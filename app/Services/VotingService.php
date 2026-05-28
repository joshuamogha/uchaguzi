<?php

namespace App\Services;

use App\Enums\ElectionStatus;
use App\Models\Ballot;
use App\Models\Election;
use App\Models\Voter;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VotingService
{
    public function __construct(
        private readonly ElectionLogService $logService,
    ) {
    }

    public function storeVerifiedSession(Voter $voter): void
    {
        session([
            'voting_election_id' => $voter->election_id,
            'voting_voter_id' => $voter->id,
            'verified_for_voting' => true,
        ]);
    }

    public function clearVerifiedSession(): void
    {
        session()->forget([
            'voting_election_id',
            'voting_voter_id',
            'verified_for_voting',
            'voting_review',
        ]);
    }

    public function getVerifiedVoter(Election $election): ?Voter
    {
        $voterId = session('voting_voter_id');
        $electionId = session('voting_election_id');

        if (! $voterId || (int) $electionId !== $election->id) {
            return null;
        }

        return Voter::query()
            ->with('member')
            ->whereKey($voterId)
            ->where('election_id', $election->id)
            ->first();
    }

    public function validateElectionWindow(Election $election, Voter $voter): void
    {
        if (! $election->isOpenForVoting()) {
            throw ValidationException::withMessages([
                'token' => $this->electionAvailabilityMessage($election),
            ]);
        }

        if (! $voter->is_eligible) {
            throw ValidationException::withMessages([
                'token' => 'This voter is not eligible for the election.',
            ]);
        }

        if ($voter->has_voted) {
            throw ValidationException::withMessages([
                'token' => 'This voter has already submitted a ballot.',
            ]);
        }
    }

    public function validateSelections(Election $election, array $inputSelections): array
    {
        $contests = $election->contests()
            ->where('is_active', true)
            ->with(['candidates' => fn ($query) => $query->where('is_active', true)])
            ->get();

        $validated = [];
        $errors = [];

        foreach ($contests as $contest) {
            $selectedIds = Arr::wrap($inputSelections[$contest->id] ?? []);
            $selectedIds = array_values(array_unique(array_map('intval', $selectedIds)));

            if (count($selectedIds) !== (int) $contest->required_selections) {
                $errors["selections.{$contest->id}"] = "Select exactly {$contest->required_selections} candidate(s) for {$contest->name}.";
                continue;
            }

            if (count($selectedIds) < (int) $contest->min_selections || count($selectedIds) > (int) $contest->max_selections) {
                $errors["selections.{$contest->id}"] = "Selection count for {$contest->name} is outside the allowed range.";
                continue;
            }

            $allowedIds = $contest->candidates->pluck('id')->all();

            foreach ($selectedIds as $candidateId) {
                if (! in_array($candidateId, $allowedIds, true)) {
                    $errors["selections.{$contest->id}"] = "One or more selected candidates are invalid for {$contest->name}.";
                    continue 2;
                }
            }

            $validated[$contest->id] = $selectedIds;
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $validated;
    }

    public function storeReviewSelections(array $selections): void
    {
        session(['voting_review' => $selections]);
    }

    public function getReviewSelections(): array
    {
        return session('voting_review', []);
    }

    public function submitVote(Election $election, Voter $voter, array $selections, Request $request): Ballot
    {
        return DB::transaction(function () use ($election, $voter, $selections, $request) {
            $lockedVoter = Voter::query()
                ->whereKey($voter->id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->validateElectionWindow($election->fresh(), $lockedVoter);
            $validatedSelections = $this->validateSelections($election->fresh(['contests.candidates']), $selections);

            $ballot = Ballot::create([
                'election_id' => $election->id,
                'ballot_code' => (string) Str::uuid(),
                'submitted_at' => now(),
            ]);

            foreach ($validatedSelections as $contestId => $candidateIds) {
                foreach ($candidateIds as $candidateId) {
                    $ballot->selections()->create([
                        'election_contest_id' => $contestId,
                        'candidate_id' => $candidateId,
                    ]);
                }
            }

            $lockedVoter->forceFill([
                'has_voted' => true,
                'voted_at' => now(),
                'token_used_at' => now(),
            ])->save();

            $this->logService->log(
                action: 'vote_submitted',
                description: "Ballot {$ballot->ballot_code} submitted.",
                election: $election,
                voter: $lockedVoter,
                request: $request,
            );

            $this->clearVerifiedSession();

            return $ballot;
        });
    }

    private function electionAvailabilityMessage(Election $election): string
    {
        $now = now();
        $start = $election->start_at?->format('d M Y H:i');
        $end = $election->end_at?->format('d M Y H:i');

        if ($election->status !== ElectionStatus::Active) {
            return match ($election->status) {
                ElectionStatus::Draft => "This election is still in draft status. Voting has not opened yet. Scheduled start: {$start}.",
                ElectionStatus::Closed => "This election was closed. Voting ended on {$end}.",
                ElectionStatus::Cancelled => 'This election has been cancelled and is not available for voting.',
                default => 'This election is not currently available for voting.',
            };
        }

        if ($now->lt($election->start_at)) {
            return "Voting has not started yet. It will open on {$start}.";
        }

        if ($now->gt($election->end_at)) {
            return "Voting has already closed. It ended on {$end}.";
        }

        return 'This election is not currently available for voting.';
    }
}
