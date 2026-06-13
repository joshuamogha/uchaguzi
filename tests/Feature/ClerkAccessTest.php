<?php

use App\Enums\ContestType;
use App\Enums\ElectionStatus;
use App\Models\Candidate;
use App\Models\ChurchGroup;
use App\Models\Community;
use App\Models\Election;
use App\Models\ElectionContest;
use App\Models\User;

test('non admin users can view dashboard elections and manual ballot entry', function () {
    $user = User::factory()->create([
        'is_admin' => false,
    ]);
    $group = ChurchGroup::create([
        'name' => 'Clerk Access Group',
        'is_active' => true,
    ]);
    $community = Community::create([
        'name' => 'Mavurunza',
        'is_active' => true,
    ]);
    $election = Election::create([
        'church_group_id' => $group->id,
        'title' => 'Clerk Access Election',
        'start_at' => now(),
        'end_at' => now()->addHour(),
        'status' => ElectionStatus::Draft,
        'public_results_enabled' => false,
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

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeText('Ballot Entry Dashboard')
        ->assertSeeText('Enter Ballot');

    $this->actingAs($user)
        ->get(route('admin.elections.index'))
        ->assertOk()
        ->assertSeeText('Clerk Access Election')
        ->assertSeeText('Enter Ballot');

    $this->actingAs($user)
        ->get(route('admin.elections.results.manual-entry', $election))
        ->assertOk()
        ->assertSeeText('Paper ballots entered')
        ->assertSeeText('Blank contest entries')
        ->assertSeeText('Destroyed contest entries');

    $this->actingAs($user)
        ->get(route('admin.elections.candidates.export-sheet', $election))
        ->assertOk();
});

test('non admin users cannot access admin only election management routes', function () {
    $user = User::factory()->create([
        'is_admin' => false,
    ]);
    $group = ChurchGroup::create([
        'name' => 'Clerk Access Group',
        'is_active' => true,
    ]);
    $election = Election::create([
        'church_group_id' => $group->id,
        'title' => 'Clerk Access Election',
        'start_at' => now(),
        'end_at' => now()->addHour(),
        'status' => ElectionStatus::Draft,
        'public_results_enabled' => false,
    ]);

    $this->actingAs($user)
        ->get(route('admin.elections.create'))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('admin.elections.contests.index', $election))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('admin.elections.voters.index', $election))
        ->assertForbidden();
});
