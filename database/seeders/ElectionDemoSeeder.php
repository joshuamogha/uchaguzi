<?php

namespace Database\Seeders;

use App\Enums\ContestType;
use App\Enums\ElectionStatus;
use App\Models\Candidate;
use App\Models\ChurchGroup;
use App\Models\Community;
use App\Models\Election;
use App\Models\Member;
use App\Models\Voter;
use App\Services\VoterTokenService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ElectionDemoSeeder extends Seeder
{
    public function __construct(
        private readonly VoterTokenService $tokenService,
    ) {
    }

    public function run(): void
    {
        $this->seedPlaceholderImages();

        $communityNames = [
            'Uwanjani', 'Mbezi', 'Kimara', 'Kijitonyama', 'Sinza', 'Mbagala',
            'Tabata', 'Tegeta', 'Makongo', 'Kunduchi', 'Kawe', 'Goba',
            'Bunju', 'Ukonga', 'Kurasini', 'Mikocheni', 'Oysterbay', 'Kibamba',
        ];

        $communities = collect($communityNames)->map(function (string $name) {
            return Community::firstOrCreate(['name' => $name], ['is_active' => true]);
        });

        $groups = collect(['Elders', 'Choir', 'Youth', 'Women', 'Men'])->mapWithKeys(function (string $name) {
            return [$name => ChurchGroup::firstOrCreate(['name' => $name], ['is_active' => true])];
        });

        $membersByCommunity = $communities->mapWithKeys(function (Community $community, int $index) {
            $members = collect(range(1, 4))->map(function (int $memberIndex) use ($community, $index) {
                return Member::firstOrCreate(
                    ['member_no' => sprintf('M%03d%02d', $index + 1, $memberIndex)],
                    [
                        'community_id' => $community->id,
                        'name' => "{$community->name} Member {$memberIndex}",
                        'phone_number' => '255700'.str_pad((string) (($index * 10) + $memberIndex), 6, '0', STR_PAD_LEFT),
                        'email' => strtolower($community->name).$memberIndex.'@church.test',
                        'is_active' => true,
                    ],
                );
            });

            return [$community->id => $members];
        });

        $choirMembers = collect(range(1, 12))->map(function (int $index) {
            return Member::firstOrCreate(
                ['member_no' => sprintf('CH%03d', $index)],
                [
                    'name' => "Choir Member {$index}",
                    'phone_number' => '255711'.str_pad((string) $index, 6, '0', STR_PAD_LEFT),
                    'email' => "choir{$index}@church.test",
                    'is_active' => true,
                ],
            );
        });

        $eldersElection = Election::updateOrCreate(
            ['title' => 'Elders Election 2026'],
            [
                'church_group_id' => $groups['Elders']->id,
                'description' => 'Community-based elders election with flexible winner counts per community.',
                'start_at' => now()->subHour(),
                'end_at' => now()->addDays(2),
                'status' => ElectionStatus::Active,
            ],
        );

        foreach ($communities as $index => $community) {
            $requiredSelections = ($index % 3 === 0) ? 2 : 1;
            $contest = $eldersElection->contests()->updateOrCreate(
                ['name' => "{$community->name} Elders"],
                [
                    'community_id' => $community->id,
                    'contest_type' => ContestType::Community,
                    'required_selections' => $requiredSelections,
                    'min_selections' => $requiredSelections,
                    'max_selections' => $requiredSelections,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );

            foreach ($membersByCommunity[$community->id] as $candidateIndex => $member) {
                $contest->candidates()->updateOrCreate(
                    ['member_id' => $member->id],
                    [
                        'election_id' => $eldersElection->id,
                        'name' => $member->name,
                        'photo' => 'candidates/placeholder-'.(($candidateIndex % 4) + 1).'.svg',
                        'bio' => "Committed to serve the {$community->name} fellowship with integrity and accountability.",
                        'sort_order' => $candidateIndex + 1,
                        'is_active' => true,
                    ],
                );
            }
        }

        $choirElection = Election::updateOrCreate(
            ['title' => 'Choir Leadership Election 2026'],
            [
                'church_group_id' => $groups['Choir']->id,
                'description' => 'Leadership vote for the choir ministry executive team.',
                'start_at' => now()->subDays(7),
                'end_at' => now()->subDays(6),
                'status' => ElectionStatus::Closed,
            ],
        );

        $choirContests = ['Chairperson', 'Vice Chairperson', 'Secretary', 'Treasurer'];

        foreach ($choirContests as $index => $contestName) {
            $contest = $choirElection->contests()->updateOrCreate(
                ['name' => $contestName],
                [
                    'contest_type' => ContestType::Position,
                    'required_selections' => 1,
                    'min_selections' => 1,
                    'max_selections' => 1,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );

            $choirMembers->slice($index * 3, 3)->values()->each(function (Member $member, int $candidateIndex) use ($contest, $choirElection) {
                $contest->candidates()->updateOrCreate(
                    ['member_id' => $member->id],
                    [
                        'election_id' => $choirElection->id,
                        'name' => $member->name,
                        'photo' => 'candidates/placeholder-'.(($candidateIndex % 4) + 1).'.svg',
                        'bio' => 'Choir ministry leader with a focus on discipleship, discipline, and teamwork.',
                        'sort_order' => $candidateIndex + 1,
                        'is_active' => true,
                    ],
                );
            });
        }

        $this->seedVotersForElection($eldersElection, Member::where('is_active', true)->get());
        $this->seedVotersForElection($choirElection, $choirMembers);
    }

    private function seedVotersForElection(Election $election, $members): void
    {
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

            if (! $voter->token_hash) {
                $this->tokenService->issueCredentials($voter, true);
            }
        }

        if ($election->status === ElectionStatus::Closed) {
            $firstContest = $election->contests()->with('candidates')->first();
            if (! $firstContest) {
                return;
            }

            Voter::where('election_id', $election->id)->take(5)->get()->each(function (Voter $voter, int $index) use ($election) {
                if ($voter->has_voted) {
                    return;
                }

                $ballot = $election->ballots()->create([
                    'ballot_code' => (string) \Illuminate\Support\Str::uuid(),
                    'submitted_at' => now()->subDays(6)->addMinutes($index),
                ]);

                foreach ($election->contests()->with('candidates')->get() as $contest) {
                    $candidate = $contest->candidates->values()[$index % max($contest->candidates->count(), 1)] ?? null;
                    if (! $candidate) {
                        continue;
                    }

                    $ballot->selections()->create([
                        'election_contest_id' => $contest->id,
                        'candidate_id' => $candidate->id,
                    ]);
                }

                $voter->update([
                    'has_voted' => true,
                    'voted_at' => now()->subDays(6)->addMinutes($index),
                    'token_used_at' => now()->subDays(6)->addMinutes($index),
                ]);
            });
        }
    }

    private function seedPlaceholderImages(): void
    {
        foreach (range(1, 4) as $index) {
            Storage::disk('public')->put(
                "candidates/placeholder-{$index}.svg",
                <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 600">
  <defs>
    <linearGradient id="grad{$index}" x1="0" x2="1" y1="0" y2="1">
      <stop offset="0%" stop-color="#154c79"/>
      <stop offset="100%" stop-color="#{$this->accentFor($index)}"/>
    </linearGradient>
  </defs>
  <rect width="600" height="600" fill="url(#grad{$index})"/>
  <circle cx="300" cy="220" r="110" fill="#ffffff" opacity="0.9"/>
  <path d="M150 500c18-88 106-150 150-150 44 0 132 62 150 150" fill="#ffffff" opacity="0.9"/>
</svg>
SVG
            );
        }
    }

    private function accentFor(int $index): string
    {
        return match ($index) {
            1 => 'f3b61f',
            2 => '7dcfb6',
            3 => 'f25f5c',
            default => '70a9a1',
        };
    }
}
