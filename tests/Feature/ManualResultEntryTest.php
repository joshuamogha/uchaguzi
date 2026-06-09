<?php

use App\Enums\ContestType;
use App\Enums\ElectionStatus;
use App\Models\Candidate;
use App\Models\ChurchGroup;
use App\Models\Community;
use App\Models\Election;
use App\Models\ElectionContest;
use App\Models\User;

test('manual result entry form is available to authenticated users', function () {
    $user = User::factory()->create([
        'is_admin' => true,
    ]);
    $group = ChurchGroup::create(['name' => 'Western Diocese', 'is_active' => true]);
    $community = Community::create(['name' => 'Mavurunza', 'is_active' => true]);
    $election = Election::create([
        'church_group_id' => $group->id,
        'title' => 'Manual Elders Election',
        'start_at' => now(),
        'end_at' => now()->addHour(),
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

    $response = $this
        ->actingAs($user)
        ->get(route('admin.elections.results.manual-entry', $election));

    $response->assertOk();
    $response->assertSeeText('Manual Result Entry');
    $response->assertSeeText('Mavurunza');
    $response->assertSeeText('Emmanuel Ndanshau');
    $response->assertSeeText('Record This Paper Ballot');
});

test('manual ballot entry updates the results report and ranking', function () {
    $user = User::factory()->create([
        'is_admin' => true,
    ]);
    $group = ChurchGroup::create(['name' => 'Western Diocese', 'is_active' => true]);
    $community = Community::create(['name' => 'Mavurunza', 'is_active' => true]);
    $election = Election::create([
        'church_group_id' => $group->id,
        'title' => 'Manual Elders Election',
        'start_at' => now(),
        'end_at' => now()->addHour(),
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
    $candidateOne = Candidate::create([
        'election_id' => $election->id,
        'election_contest_id' => $contest->id,
        'name' => 'Candidate One',
        'sort_order' => 1,
        'is_active' => true,
    ]);
    $candidateTwo = Candidate::create([
        'election_id' => $election->id,
        'election_contest_id' => $contest->id,
        'name' => 'Candidate Two',
        'sort_order' => 2,
        'is_active' => true,
    ]);

    $this
        ->actingAs($user)
        ->post(route('admin.elections.results.manual-entry.ballots.store', $election), [
            'selections' => [
                $contest->id => [
                    $candidateTwo->id,
                ],
            ],
        ])
        ->assertRedirect(route('admin.elections.results.manual-entry', $election));

    $manualEntryResponse = $this
        ->actingAs($user)
        ->get(route('admin.elections.results.manual-entry', $election));

    $manualEntryResponse->assertOk();
    $manualEntryResponse->assertSeeText('Paper ballots entered');
    $manualEntryResponse->assertSeeText('1');
    $manualEntryResponse->assertSeeText('Total: 1');

    $response = $this
        ->actingAs($user)
        ->get(route('admin.elections.results.index', $election));

    $response->assertOk();
    $response->assertSeeText('Source: Manual tally entry');
    $response->assertSeeText('Candidate Two');
    $response->assertSeeText('1');
    $response->assertSeeText('Winner');
});

test('closed elections expose manual ballot entry as read only and reject new ballots', function () {
    $user = User::factory()->create([
        'is_admin' => true,
    ]);
    $group = ChurchGroup::create(['name' => 'Western Diocese', 'is_active' => true]);
    $community = Community::create(['name' => 'Mavurunza', 'is_active' => true]);
    $election = Election::create([
        'church_group_id' => $group->id,
        'title' => 'Closed Manual Election',
        'start_at' => now()->subDay(),
        'end_at' => now()->subHour(),
        'status' => ElectionStatus::Closed,
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
    $candidate = Candidate::create([
        'election_id' => $election->id,
        'election_contest_id' => $contest->id,
        'name' => 'Candidate One',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get(route('admin.elections.results.manual-entry', $election))
        ->assertOk()
        ->assertSeeText('read only')
        ->assertSee('disabled', false);

    $this->actingAs($user)
        ->post(route('admin.elections.results.manual-entry.ballots.store', $election), [
            'selections' => [
                $contest->id => [$candidate->id],
            ],
        ])
        ->assertRedirect(route('admin.elections.results.manual-entry', $election))
        ->assertSessionHasErrors('manual_ballot');

    $followUp = $this->actingAs($user)
        ->get(route('admin.elections.results.manual-entry', $election));

    $followUp->assertSeeText('Paper ballots entered');
    $followUp->assertSeeText('0');
});
