<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\Member;
use App\Models\Voter;
use App\Services\VoterTokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VoterController extends Controller
{
    public function __construct(
        private readonly VoterTokenService $tokenService,
    ) {
    }

    public function index(Election $election): View
    {
        $this->authorize('view', $election);

        return view('admin.elections.voters.index', [
            'election' => $election,
            'voters' => $election->voters()->with('member.community')->paginate(25),
            'communities' => \App\Models\Community::where('is_active', true)->orderBy('name')->get(),
            'generatedCredentials' => collect(session("generated_voter_credentials.{$election->id}", []))->keyBy('voter_id'),
        ]);
    }

    public function generate(Request $request, Election $election): RedirectResponse
    {
        $this->authorize('update', $election);

        $data = $request->validate([
            'community_id' => ['nullable', 'exists:communities,id'],
            'generate_pin' => ['nullable', 'boolean'],
        ]);

        $members = Member::query()
            ->where('is_active', true)
            ->when($data['community_id'] ?? null, fn ($query, $communityId) => $query->where('community_id', $communityId))
            ->orderBy('name')
            ->get();

        $generated = [];

        foreach ($members as $member) {
            $voter = Voter::firstOrCreate(
                [
                    'election_id' => $election->id,
                    'member_id' => $member->id,
                ],
                [
                    'phone_number' => $member->phone_number,
                    'is_eligible' => true,
                ],
            );

            $voter->update(['phone_number' => $member->phone_number]);
            $credentials = $this->tokenService->issueCredentials($voter, $request->boolean('generate_pin', true));

            $generated[] = [
                'voter_id' => $voter->id,
                'member_name' => $member->name,
                'token' => $credentials['token'],
                'pin' => $credentials['pin'],
                'link' => route('vote.verify.form', ['token' => $credentials['token']]),
            ];
        }

        session(["generated_voter_credentials.{$election->id}" => $generated]);

        return redirect()->route('admin.elections.voters.cards', $election)->with('success', count($generated).' voter credentials generated.');
    }

    public function cards(Election $election): View
    {
        $this->authorize('view', $election);

        return view('admin.elections.voters.cards', [
            'election' => $election,
            'cards' => session("generated_voter_credentials.{$election->id}", []),
        ]);
    }

    public function toggleEligibility(Election $election, Voter $voter): RedirectResponse
    {
        $this->authorize('update', $election);
        $voter->update(['is_eligible' => ! $voter->is_eligible]);

        return redirect()->route('admin.elections.voters.index', $election)->with('success', 'Voter eligibility updated.');
    }
}
