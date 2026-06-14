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
    $response->assertSeeText('Submit This Paper Ballot');
    $response->assertSeeText('Mark this contest as blank');
    $response->assertSeeText('Mark this contest as destroyed');
});

test('manual ballot entry updates the results report and ranking', function () {
    $user = User::factory()->create([
        'name' => 'Manual Entry Admin',
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
    $manualEntryResponse->assertSeeText('Destroyed contest entries');
    $manualEntryResponse->assertSeeText('0');
    $manualEntryResponse->assertSeeText('Recent Manual Entry Audit');
    $manualEntryResponse->assertSeeText('Manual Entry Admin');
    $manualEntryResponse->assertSeeText('Candidate Two');

    $this->assertDatabaseHas('election_manual_entry_audits', [
        'election_id' => $election->id,
        'user_id' => $user->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('admin.elections.results.index', $election));

    $response->assertOk();
    $response->assertSeeText('Source: Manual tally entry');
    $response->assertSeeText('Candidate Two');
    $response->assertSeeText('1');
    $response->assertSeeText('Winner');
});

test('manual ballot entry allows fewer ticks than the contest required count', function () {
    $user = User::factory()->create([
        'is_admin' => true,
    ]);
    $group = ChurchGroup::create(['name' => 'Western Diocese', 'is_active' => true]);
    $community = Community::create(['name' => 'Mavurunza', 'is_active' => true]);
    $election = Election::create([
        'church_group_id' => $group->id,
        'title' => 'Manual Delegates Election',
        'start_at' => now(),
        'end_at' => now()->addHour(),
        'status' => ElectionStatus::Draft,
    ]);
    $contest = ElectionContest::create([
        'election_id' => $election->id,
        'community_id' => $community->id,
        'name' => 'Mavurunza Delegates',
        'contest_type' => ContestType::Community,
        'required_selections' => 2,
        'min_selections' => 2,
        'max_selections' => 2,
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
    Candidate::create([
        'election_id' => $election->id,
        'election_contest_id' => $contest->id,
        'name' => 'Candidate Two',
        'sort_order' => 2,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->post(route('admin.elections.results.manual-entry.ballots.store', $election), [
            'selections' => [
                $contest->id => [$candidateOne->id],
            ],
        ])
        ->assertRedirect(route('admin.elections.results.manual-entry', $election));

    $response = $this->actingAs($user)
        ->get(route('admin.elections.results.index', $election));

    $response->assertOk();
    $response->assertSeeText('Candidate One');
    $response->assertSeeText('1');
});

test('destroyed contest entry increments destroyed count without affecting candidate totals', function () {
    $user = User::factory()->create([
        'name' => 'Destroyed Entry Admin',
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
        'name' => 'Candidate One',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $this
        ->actingAs($user)
        ->post(route('admin.elections.results.manual-entry.ballots.store', $election), [
            'destroyed_contests' => [
                $contest->id => '1',
            ],
        ])
        ->assertRedirect(route('admin.elections.results.manual-entry', $election));

    $manualEntryResponse = $this
        ->actingAs($user)
        ->get(route('admin.elections.results.manual-entry', $election));

    $manualEntryResponse->assertOk();
    $manualEntryResponse->assertSeeText('Paper ballots entered');
    $manualEntryResponse->assertSeeText('1');
    $manualEntryResponse->assertSeeText('Blank contest entries');
    $manualEntryResponse->assertSeeText('0');
    $manualEntryResponse->assertSeeText('Destroyed contest entries');
    $manualEntryResponse->assertSeeText('1');
    $manualEntryResponse->assertSeeText('Destroyed Entry Admin');
    $manualEntryResponse->assertSeeText('Destroyed entry');

    $this->assertDatabaseHas('election_manual_entry_audits', [
        'election_id' => $election->id,
        'user_id' => $user->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('admin.elections.results.index', $election));

    $response->assertOk();
    $response->assertSeeText('Source: Manual tally entry');
    $response->assertSeeText('Paper ballots entered: 1');
    $response->assertSeeText('Blank entries: 0');
    $response->assertSeeText('Destroyed entries: 1');
    $response->assertSeeText('Candidate One');
    $response->assertSeeText('0');
});

test('blank contest entry is recorded separately without affecting candidate totals', function () {
    $user = User::factory()->create([
        'name' => 'Blank Contest Admin',
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
        'name' => 'Candidate One',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $this
        ->actingAs($user)
        ->post(route('admin.elections.results.manual-entry.ballots.store', $election), [
            'blank_contests' => [
                $contest->id => '1',
            ],
        ])
        ->assertRedirect(route('admin.elections.results.manual-entry', $election));

    $manualEntryResponse = $this
        ->actingAs($user)
        ->get(route('admin.elections.results.manual-entry', $election));

    $manualEntryResponse->assertOk();
    $manualEntryResponse->assertSeeText('Paper ballots entered');
    $manualEntryResponse->assertSeeText('1');
    $manualEntryResponse->assertSeeText('Blank contest entries');
    $manualEntryResponse->assertSeeText('1');
    $manualEntryResponse->assertSeeText('Blank Contest Admin');
    $manualEntryResponse->assertSeeText('Blank entry');

    $this->assertDatabaseHas('election_manual_entry_audits', [
        'election_id' => $election->id,
        'user_id' => $user->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('admin.elections.results.index', $election));

    $response->assertOk();
    $response->assertSeeText('Paper ballots entered: 1');
    $response->assertSeeText('Blank entries: 1');
    $response->assertSeeText('Destroyed entries: 0');
    $response->assertSeeText('Candidate One');
    $response->assertSeeText('0');
});

test('paper ballot can mix valid selections and blank contests', function () {
    $user = User::factory()->create([
        'is_admin' => true,
    ]);
    $group = ChurchGroup::create(['name' => 'Western Diocese', 'is_active' => true]);
    $community = Community::create(['name' => 'Mavurunza', 'is_active' => true]);
    $election = Election::create([
        'church_group_id' => $group->id,
        'title' => 'Mixed Manual Election',
        'start_at' => now(),
        'end_at' => now()->addHour(),
        'status' => ElectionStatus::Draft,
    ]);
    $validContest = ElectionContest::create([
        'election_id' => $election->id,
        'community_id' => $community->id,
        'name' => 'Ward Elders',
        'contest_type' => ContestType::Community,
        'required_selections' => 1,
        'min_selections' => 1,
        'max_selections' => 1,
        'sort_order' => 1,
        'is_active' => true,
    ]);
    $blankContest = ElectionContest::create([
        'election_id' => $election->id,
        'community_id' => $community->id,
        'name' => 'Parish Delegates',
        'contest_type' => ContestType::Community,
        'required_selections' => 1,
        'min_selections' => 1,
        'max_selections' => 1,
        'sort_order' => 2,
        'is_active' => true,
    ]);
    $validCandidate = Candidate::create([
        'election_id' => $election->id,
        'election_contest_id' => $validContest->id,
        'name' => 'Valid Candidate',
        'sort_order' => 1,
        'is_active' => true,
    ]);
    Candidate::create([
        'election_id' => $election->id,
        'election_contest_id' => $blankContest->id,
        'name' => 'Blank Contest Candidate',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $this
        ->actingAs($user)
        ->post(route('admin.elections.results.manual-entry.ballots.store', $election), [
            'selections' => [
                $validContest->id => [$validCandidate->id],
            ],
            'blank_contests' => [
                $blankContest->id => '1',
            ],
        ])
        ->assertRedirect(route('admin.elections.results.manual-entry', $election));

    $response = $this
        ->actingAs($user)
        ->get(route('admin.elections.results.index', $election));

    $response->assertOk();
    $response->assertSeeText('Paper ballots entered: 1');
    $response->assertSeeText('Blank entries: 1');
    $response->assertSeeText('Destroyed entries: 0');
    $response->assertSeeText('Ward Elders');
    $response->assertSeeText('Parish Delegates');
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
    $followUp->assertSeeText('Blank contest entries');
    $followUp->assertSeeText('Destroyed contest entries');
    $followUp->assertSeeText('0');
});
