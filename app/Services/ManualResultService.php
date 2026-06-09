<?php

namespace App\Services;

use App\Models\CandidateManualTally;
use App\Models\Election;
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

    public function recordBallot(Election $election, array $selections): void
    {
        $contests = $election->contests()->with(['candidates' => fn ($query) => $query->where('is_active', true)])->where('is_active', true)->get();

        DB::transaction(function () use ($election, $selections, $contests) {
            foreach ($contests as $contest) {
                $selectedIds = array_values(array_unique(array_map('intval', (array) ($selections[$contest->id] ?? $selections[(string) $contest->id] ?? []))));

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
            }
        });
    }

    public function clear(Election $election): void
    {
        CandidateManualTally::query()
            ->where('election_id', $election->id)
            ->delete();
    }

    public function hasManualTallies(Election $election): bool
    {
        return CandidateManualTally::query()
            ->where('election_id', $election->id)
            ->exists();
    }

    public function enteredBallots(Election $election): int
    {
        $contest = $election->contests()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->first();

        if (! $contest) {
            return 0;
        }

        $totalVotes = CandidateManualTally::query()
            ->where('election_id', $election->id)
            ->where('election_contest_id', $contest->id)
            ->sum('votes');

        if ((int) $contest->required_selections < 1) {
            return 0;
        }

        return (int) floor($totalVotes / (int) $contest->required_selections);
    }
}
