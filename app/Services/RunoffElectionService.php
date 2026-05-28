<?php

namespace App\Services;

use App\Models\Election;
use App\Models\ElectionContest;
use Illuminate\Support\Facades\DB;

class RunoffElectionService
{
    public function createFromTie(Election $sourceElection, ElectionContest $contest, array $contestResult): Election
    {
        return DB::transaction(function () use ($sourceElection, $contest, $contestResult) {
            $runoffElection = Election::create([
                'church_group_id' => $sourceElection->church_group_id,
                'title' => "{$sourceElection->title} - {$contest->name} Runoff",
                'description' => "Runoff election created from {$sourceElection->title} for tied candidates in {$contest->name}.",
                'start_at' => now()->addDay()->startOfHour(),
                'end_at' => now()->addDays(2)->startOfHour(),
                'status' => 'draft',
            ]);

            $runoffContest = $runoffElection->contests()->create([
                'community_id' => $contest->community_id,
                'name' => "{$contest->name} Runoff",
                'contest_type' => $contest->contest_type,
                'required_selections' => $contestResult['runoff_slots'],
                'min_selections' => $contestResult['runoff_slots'],
                'max_selections' => $contestResult['runoff_slots'],
                'sort_order' => 1,
                'is_active' => true,
            ]);

            foreach ($contestResult['runoff_candidates'] as $index => $candidateRow) {
                $candidate = $candidateRow['candidate'];

                $runoffContest->candidates()->create([
                    'election_id' => $runoffElection->id,
                    'member_id' => $candidate->member_id,
                    'name' => $candidate->name,
                    'photo' => $candidate->photo,
                    'bio' => $candidate->bio,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]);
            }

            foreach ($sourceElection->voters as $voter) {
                $runoffElection->voters()->create([
                    'member_id' => $voter->member_id,
                    'phone_number' => $voter->phone_number,
                    'token_hash' => null,
                    'pin_hash' => null,
                    'is_eligible' => $voter->is_eligible,
                    'has_voted' => false,
                    'verified_at' => null,
                    'voted_at' => null,
                    'token_used_at' => null,
                ]);
            }

            return $runoffElection;
        });
    }
}
