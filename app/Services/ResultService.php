<?php

namespace App\Services;

use App\Models\Election;
use Illuminate\Support\Collection;

class ResultService
{
    public function summary(Election $election): array
    {
        $registered = $election->voters()->count();
        $voted = $election->voters()->where('has_voted', true)->count();

        return [
            'registered_voters' => $registered,
            'votes_cast' => $voted,
            'turnout_percentage' => $registered > 0 ? round(($voted / $registered) * 100, 2) : 0,
        ];
    }

    public function contestResults(Election $election): array
    {
        $contests = $election->contests()
            ->with(['candidates' => fn ($query) => $query
                ->withCount('selections')
                ->orderByDesc('selections_count')
                ->orderBy('name')])
            ->get();

        return $contests->map(function ($contest) {
            $previousVotes = null;
            $currentRank = 0;

            $results = $contest->candidates->values()->map(function ($candidate, $index) use (&$previousVotes, &$currentRank) {
                if ($previousVotes !== $candidate->selections_count) {
                    $currentRank = $index + 1;
                    $previousVotes = $candidate->selections_count;
                }

                return [
                    'candidate' => $candidate,
                    'votes' => $candidate->selections_count,
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

            return [
                'contest' => $contest,
                'requires_runoff' => $requiresRunoff,
                'runoff_slots' => $runoffSlots,
                'runoff_candidates' => $runoffCandidates,
                'top_votes' => $topVotes,
                'second_votes' => $secondVotes,
                'top_margin' => $topMargin,
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
                ];
            }
        }

        return $rows;
    }
}
