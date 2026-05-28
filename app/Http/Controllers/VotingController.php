<?php

namespace App\Http\Controllers;

use App\Http\Requests\Voting\ConfirmPinRequest;
use App\Http\Requests\Voting\SubmitBallotRequest;
use App\Http\Requests\Voting\VerifyTokenRequest;
use App\Models\Election;
use App\Services\ElectionLogService;
use App\Services\VoterTokenService;
use App\Services\VotingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VotingController extends Controller
{
    public function __construct(
        private readonly VoterTokenService $tokenService,
        private readonly VotingService $votingService,
        private readonly ElectionLogService $logService,
    ) {
    }

    public function showVerifyForm(Request $request): View|RedirectResponse
    {
        if ($request->filled('token')) {
            $request->validate([
                'token' => ['required', 'string', 'min:12'],
            ]);

            return $this->showConfirmationForToken($request->string('token')->toString(), $request);
        }

        return view('vote.verify', ['voter' => null, 'token' => '']);
    }

    public function verifyToken(VerifyTokenRequest $request): View
    {
        return $this->showConfirmationForToken($request->validated('token'), $request);
    }

    public function confirmPin(ConfirmPinRequest $request): RedirectResponse
    {
        $token = $request->validated('token');
        $voter = $this->tokenService->findByPlainToken($token);

        if (! $voter) {
            throw ValidationException::withMessages(['token' => 'The voting token is invalid.']);
        }

        $this->guardVotingAvailability($voter, $request);

        if (! $this->tokenService->verifyPin($voter, $request->validated('pin'))) {
            $this->logService->log('pin_failed', 'Invalid PIN supplied.', $voter->election, $voter, request: $request);
            throw ValidationException::withMessages(['pin' => 'The PIN provided is invalid.']);
        }

        $voter->forceFill(['verified_at' => now()])->save();
        $this->votingService->storeVerifiedSession($voter);

        return redirect()->route('vote.ballot', $voter->election);
    }

    public function ballot(Election $election, Request $request): View
    {
        $voter = $this->requireVerifiedVoter($election);
        $election->load(['contests' => fn ($query) => $query->where('is_active', true), 'contests.candidates' => fn ($query) => $query->where('is_active', true)]);

        $this->logService->log('ballot_opened', 'Verified voter opened the ballot.', $election, $voter, request: $request);

        return view('vote.ballot', [
            'election' => $election,
            'voter' => $voter,
            'savedSelections' => $this->votingService->getReviewSelections(),
        ]);
    }

    public function review(SubmitBallotRequest $request, Election $election): View
    {
        $this->requireVerifiedVoter($election);
        $validatedSelections = $this->votingService->validateSelections($election, $request->validated('selections'));
        $this->votingService->storeReviewSelections($validatedSelections);

        $election->load(['contests.candidates']);

        return view('vote.review', [
            'election' => $election,
            'reviewSelections' => $validatedSelections,
        ]);
    }

    public function submit(Request $request, Election $election): RedirectResponse
    {
        $voter = $this->requireVerifiedVoter($election);
        $selections = $this->votingService->getReviewSelections();

        abort_if($selections === [], 422, 'No reviewed ballot selections found.');

        $ballot = $this->votingService->submitVote($election, $voter, $selections, $request);
        session()->flash('submitted_ballot_code', $ballot->ballot_code);

        return redirect()->route('vote.success');
    }

    public function success(): View
    {
        return view('vote.success', [
            'ballotCode' => session('submitted_ballot_code'),
        ]);
    }

    private function showConfirmationForToken(string $token, Request $request): View
    {
        $voter = $this->tokenService->findByPlainToken($token);

        if (! $voter) {
            $this->logService->log('invalid_token', 'Invalid token submitted for verification.', request: $request);
            throw ValidationException::withMessages(['token' => 'The voting token is invalid.']);
        }

        $this->guardVotingAvailability($voter, $request);
        $this->logService->log('token_scanned', 'Voting token accepted for verification.', $voter->election, $voter, request: $request);

        return view('vote.verify', [
            'voter' => $voter,
            'token' => $token,
        ]);
    }

    private function requireVerifiedVoter(Election $election)
    {
        $voter = $this->votingService->getVerifiedVoter($election);
        abort_unless($voter, 403);

        return $voter;
    }

    private function guardVotingAvailability($voter, Request $request): void
    {
        try {
            $this->votingService->validateElectionWindow($voter->election, $voter);
        } catch (ValidationException $exception) {
            $action = match (true) {
                $voter->has_voted => 'already_voted_attempt',
                ! $voter->is_eligible => 'ineligible_voter_attempt',
                default => 'election_unavailable_attempt',
            };

            $this->logService->log($action, 'Blocked during voting eligibility checks.', $voter->election, $voter, request: $request);
            throw $exception;
        }
    }
}
