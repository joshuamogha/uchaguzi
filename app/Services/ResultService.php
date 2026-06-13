<?php

namespace App\Services;

use App\Models\CandidateManualTally;
use App\Models\Election;
use App\Models\ElectionContestManualSummary;
use Illuminate\Support\Collection;

class ResultService
{
    public function summary(Election $election): array
    {
        $registered = $election->voters()->count();
        $voted = $election->voters()->where('has_voted', true)->count();
        $hasManualTallies = CandidateManualTally::query()
            ->where('election_id', $election->id)
            ->exists();
        $destroyedContests = (int) (ElectionContestManualSummary::query()
            ->where('election_id', $election->id)
            ->sum('destroyed_entries') ?? 0);
        $blankContests = app(ManualResultService::class)->blankContests($election);
        $hasManualSummary = $hasManualTallies || $destroyedContests > 0 || $blankContests > 0;
        $manualBallots = $hasManualSummary
            ? app(ManualResultService::class)->enteredBallots($election)
            : 0;

        return [
            'registered_voters' => $registered,
            'votes_cast' => $voted,
            'turnout_percentage' => $registered > 0 ? round(($voted / $registered) * 100, 2) : 0,
            'result_source' => $hasManualSummary ? 'manual' : 'digital',
            'manual_ballots_entered' => $manualBallots,
            'blank_manual_entries' => $blankContests,
            'destroyed_manual_entries' => $destroyedContests,
        ];
    }

    public function contestResults(Election $election): array
    {
        $hasCandidateManualTallies = CandidateManualTally::query()
            ->where('election_id', $election->id)
            ->exists();
        $destroyedContests = ElectionContestManualSummary::query()
            ->where('election_id', $election->id)
            ->pluck('destroyed_entries', 'election_contest_id');
        $blankContests = ElectionContestManualSummary::query()
            ->where('election_id', $election->id)
            ->pluck('blank_entries', 'election_contest_id');
        $hasManualTallies = $hasCandidateManualTallies || $destroyedContests->sum() > 0 || $blankContests->sum() > 0;

        $contests = $election->contests()
            ->with([
                'community',
                'candidates' => fn ($query) => $query
                    ->when(
                        $hasManualTallies,
                        fn ($candidateQuery) => $candidateQuery
                            ->with(['manualTallies' => fn ($manualQuery) => $manualQuery->where('election_id', $election->id)])
                            ->orderBy('sort_order')
                            ->orderBy('name'),
                        fn ($candidateQuery) => $candidateQuery
                            ->withCount('selections')
                            ->orderByDesc('selections_count')
                            ->orderBy('name')
                    ),
            ])
            ->get();

        return $contests->map(function ($contest) use ($hasManualTallies, $destroyedContests, $blankContests) {
            $previousVotes = null;
            $currentRank = 0;

            $results = $contest->candidates
                ->map(fn ($candidate) => [
                    'candidate' => $candidate,
                    'votes' => $hasManualTallies
                        ? (int) ($candidate->manualTallies->first()?->votes ?? 0)
                        : (int) $candidate->selections_count,
                ])
                ->sortBy([
                    ['votes', 'desc'],
                    ['candidate.name', 'asc'],
                ])
                ->values()
                ->map(function (array $row, int $index) use (&$previousVotes, &$currentRank) {
                    if ($previousVotes !== $row['votes']) {
                        $currentRank = $index + 1;
                        $previousVotes = $row['votes'];
                    }

                    return $row + [
                        'ranking' => $currentRank,
                    ];
                });

            $positiveResults = $results->where('votes', '>', 0)->values();
            $winnerIds = [];
            $tiedCandidateIds = [];
            $runoffCandidates = collect();
            $runoffSlots = 0;
            $requiresRunoff = false;

            if ($positiveResults->isNotEmpty()) {
                if ($positiveResults->count() <= (int) $contest->required_selections) {
                    $winnerIds = $positiveResults->pluck('candidate.id')->all();
                } else {
                    $cutoffVotes = $positiveResults->values()[(int) $contest->required_selections - 1]['votes'];
                    $clearWinners = $positiveResults->where('votes', '>', $cutoffVotes)->values();
                    $tiedAtCutoff = $positiveResults->where('votes', $cutoffVotes)->values();
                    $remainingSlots = (int) $contest->required_selections - $clearWinners->count();

                    $winnerIds = $clearWinners->pluck('candidate.id')->all();

                    if ($tiedAtCutoff->count() > $remainingSlots) {
                        $requiresRunoff = true;
                        $runoffSlots = $remainingSlots;
                        $runoffCandidates = $tiedAtCutoff;
                        $tiedCandidateIds = $tiedAtCutoff->pluck('candidate.id')->all();
                        $winnerIds = array_merge($winnerIds, $tiedCandidateIds);
                    } else {
                        $winnerIds = array_merge($winnerIds, $tiedAtCutoff->pluck('candidate.id')->all());
                    }
                }
            }

            $topVotes = (int) ($positiveResults->first()['votes'] ?? 0);
            $secondVotes = (int) ($positiveResults->skip(1)->first()['votes'] ?? 0);
            $topMargin = max($topVotes - $secondVotes, 0);
            $totalVotes = (int) $results->sum('votes');
            $topCandidates = $results
                ->where('votes', $topVotes)
                ->pluck('candidate.name')
                ->values();

            return [
                'contest' => $contest,
                'blank_entries' => (int) ($blankContests[$contest->id] ?? 0),
                'destroyed_entries' => (int) ($destroyedContests[$contest->id] ?? 0),
                'total_votes' => $totalVotes,
                'top_candidates' => $topCandidates,
                'requires_runoff' => $requiresRunoff,
                'runoff_slots' => $runoffSlots,
                'runoff_candidates' => $runoffCandidates,
                'top_votes' => $topVotes,
                'second_votes' => $secondVotes,
                'top_margin' => $topMargin,
                'source' => $hasManualTallies ? 'manual' : 'digital',
                'results' => $results->map(fn (array $row) => $row + [
                    'is_winner' => in_array($row['candidate']->id, $winnerIds, true),
                    'is_tied_winner' => in_array($row['candidate']->id, $tiedCandidateIds, true),
                ]),
            ];
        })->all();
    }

    public function communityTurnout(Election $election): array
    {
        $voters = $election->voters()
            ->with('member.community')
            ->get()
            ->filter(fn ($voter) => $voter->member?->community);

        $grouped = $voters->groupBy(fn ($voter) => $voter->member->community->name)
            ->map(function (Collection $communityVoters, string $communityName) {
                $registered = $communityVoters->count();
                $voted = $communityVoters->where('has_voted', true)->count();

                return [
                    'community' => $communityName,
                    'registered' => $registered,
                    'voted' => $voted,
                    'turnout_percentage' => $registered > 0 ? round(($voted / $registered) * 100, 2) : 0,
                ];
            })
            ->sortByDesc('turnout_percentage')
            ->values();

        return [
            'labels' => $grouped->pluck('community')->all(),
            'registered' => $grouped->pluck('registered')->all(),
            'voted' => $grouped->pluck('voted')->all(),
            'turnout_percentages' => $grouped->pluck('turnout_percentage')->all(),
        ];
    }

    public function marginInsights(array $contestResults): array
    {
        return [
            'labels' => collect($contestResults)->pluck('contest.name')->all(),
            'margins' => collect($contestResults)->pluck('top_margin')->all(),
            'runoff_flags' => collect($contestResults)->map(fn (array $contestResult) => $contestResult['requires_runoff'])->all(),
            'top_votes' => collect($contestResults)->pluck('top_votes')->all(),
            'second_votes' => collect($contestResults)->pluck('second_votes')->all(),
        ];
    }

    public function exportRows(Election $election): array
    {
        $rows = [];

        foreach ($this->contestResults($election) as $contestResult) {
            foreach ($contestResult['results'] as $row) {
                $rows[] = [
                    'contest' => $contestResult['contest']->name,
                    'candidate' => $row['candidate']->name,
                    'votes' => $row['votes'],
                    'ranking' => $row['ranking'],
                    'winner' => $row['is_winner'] ? 'Yes' : 'No',
                    'total_votes' => $contestResult['total_votes'],
                    'spoiled_votes' => $contestResult['destroyed_entries'],
                ];
            }
        }

        return $rows;
    }
}
