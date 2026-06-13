<?php

namespace App\Services;

use App\Models\CandidateManualTally;
use App\Models\Election;
use App\Models\ElectionManualEntryAudit;
use App\Models\ElectionContestManualSummary;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManualResultService
{
    public function save(Election $election, array $votesByContest): void
    {
        $contests = $election->contests()->with('candidates')->where('is_active', true)->get();

        DB::transaction(function () use ($election, $votesByContest, $contests) {
            foreach ($contests as $contest) {
                $contestVotes = $votesByContest[$contest->id] ?? $votesByContest[(string) $contest->id] ?? [];

                foreach ($contest->candidates as $candidate) {
                    CandidateManualTally::updateOrCreate(
                        [
                            'election_id' => $election->id,
                            'election_contest_id' => $contest->id,
                            'candidate_id' => $candidate->id,
                        ],
                        [
                            'votes' => (int) ($contestVotes[$candidate->id] ?? $contestVotes[(string) $candidate->id] ?? 0),
                        ],
                    );
                }
            }
        });
    }

    public function recordBallot(
        Election $election,
        array $selections,
        array $destroyedContestIds = [],
        ?User $user = null,
        ?Request $request = null,
    ): void
    {
        $contests = $election->contests()
            ->with([
                'community',
                'candidates' => fn ($query) => $query->where('is_active', true),
            ])
            ->where('is_active', true)
            ->get();
        $destroyedContestIds = array_values(array_unique(array_map('intval', $destroyedContestIds)));
        $auditEntries = [];

        DB::transaction(function () use ($election, $selections, $contests, $destroyedContestIds, $user, $request, &$auditEntries) {
            foreach ($contests as $contest) {
                if (in_array($contest->id, $destroyedContestIds, true)) {
                    $summary = ElectionContestManualSummary::firstOrCreate(
                        [
                            'election_id' => $election->id,
                            'election_contest_id' => $contest->id,
                        ],
                        [
                            'destroyed_entries' => 0,
                        ],
                    );

                    $summary->increment('destroyed_entries');

                    $auditEntries[] = [
                        'contest_id' => $contest->id,
                        'contest_name' => $contest->name,
                        'community_name' => $contest->community?->name,
                        'status' => 'destroyed',
                        'candidate_ids' => [],
                        'candidate_names' => [],
                    ];

                    continue;
                }

                $selectedIds = array_values(array_unique(array_map('intval', (array) ($selections[$contest->id] ?? $selections[(string) $contest->id] ?? []))));
                $selectedCandidates = $contest->candidates
                    ->whereIn('id', $selectedIds)
                    ->sortBy('name')
                    ->values();

                foreach ($selectedIds as $candidateId) {
                    $tally = CandidateManualTally::firstOrCreate(
                        [
                            'election_id' => $election->id,
                            'election_contest_id' => $contest->id,
                            'candidate_id' => $candidateId,
                        ],
                        [
                            'votes' => 0,
                        ],
                    );

                    $tally->increment('votes');
                }

                $auditEntries[] = [
                    'contest_id' => $contest->id,
                    'contest_name' => $contest->name,
                    'community_name' => $contest->community?->name,
                    'status' => 'valid',
                    'candidate_ids' => $selectedCandidates->pluck('id')->all(),
                    'candidate_names' => $selectedCandidates->pluck('name')->all(),
                ];
            }

            ElectionManualEntryAudit::create([
                'election_id' => $election->id,
                'user_id' => $user?->id,
                'payload' => $auditEntries,
                'entered_at' => now(),
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
            ]);
        });
    }

    public function clear(Election $election): void
    {
        CandidateManualTally::query()
            ->where('election_id', $election->id)
            ->delete();

        ElectionContestManualSummary::query()
            ->where('election_id', $election->id)
            ->delete();

        ElectionManualEntryAudit::query()
            ->where('election_id', $election->id)
            ->delete();
    }

    public function hasManualTallies(Election $election): bool
    {
        return CandidateManualTally::query()
            ->where('election_id', $election->id)
            ->exists()
            || $this->destroyedContests($election) > 0;
    }

    public function enteredBallots(Election $election): int
    {
        return ElectionManualEntryAudit::query()
            ->where('election_id', $election->id)
            ->count();
    }

    public function destroyedContests(Election $election): int
    {
        return (int) (ElectionContestManualSummary::query()
            ->where('election_id', $election->id)
            ->sum('destroyed_entries') ?? 0);
    }

    public function recentManualEntries(Election $election, int $limit = 10)
    {
        return ElectionManualEntryAudit::query()
            ->with('user')
            ->where('election_id', $election->id)
            ->orderByDesc('entered_at')
            ->limit($limit)
            ->get();
    }
}
