<?php

use App\Enums\ContestType;
use App\Enums\ElectionStatus;
use App\Models\Candidate;
use App\Models\ChurchGroup;
use App\Models\Community;
use App\Models\Election;
use App\Models\ElectionContest;
use App\Models\User;

test('authenticated users can open the printable candidate export sheet', function () {
    $user = User::factory()->create();
    $group = ChurchGroup::create([
        'name' => 'Jimbo la Magharibi - Usharika wa Temboni',
        'is_active' => true,
    ]);
    $community = Community::create([
        'name' => 'Mavurunza',
        'is_active' => true,
    ]);
    $election = Election::create([
        'church_group_id' => $group->id,
        'title' => 'Karatasi ya kura ya uchaguzi wa wazee wa kanisani',
        'start_at' => '2026-06-14 09:00:00',
        'end_at' => '2026-06-14 18:00:00',
        'status' => ElectionStatus::Draft,
    ]);
    $contest = ElectionContest::create([
        'election_id' => $election->id,
        'community_id' => $community->id,
        'name' => 'Mavurunza Elders',
        'contest_type' => ContestType::Community,
        'required_selections' => 1,
        'min_selections' => 1,
        'max_selections' => 1,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    Candidate::create([
        'election_id' => $election->id,
        'election_contest_id' => $contest->id,
        'name' => 'Emmanuel Ndanshau',
        'sort_order' => 1,
        'is_active' => true,
    ]);
    Candidate::create([
        'election_id' => $election->id,
        'election_contest_id' => $contest->id,
        'name' => 'Gervas Simon',
        'sort_order' => 2,
        'is_active' => true,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('admin.elections.candidates.export-sheet', $election));

    $response->assertOk();
    $response->assertSeeText('DAYOSISI MASHARIKI NA PWANI,');
    $response->assertSeeText('JIMBO LA MAGHARIBI');
    $response->assertSeeText('USHARIKA WA TEMBONI');
    $response->assertSeeText('KARATASI YA KURA YA UCHAGUZI WA WAZEE WA KANISANI');
    $response->assertSeeText('14.06.2026');
    $response->assertSeeText('MAVURUNZA');
    $response->assertSeeText('EMMANUEL NDANSHAU');
    $response->assertSeeText('GERVAS SIMON');
});
