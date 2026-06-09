<?php

use App\Enums\ElectionStatus;
use App\Models\ChurchGroup;
use App\Models\Election;
use App\Models\User;

function createElectionForResultsAccess(): Election
{
    $group = ChurchGroup::create([
        'name' => 'Access Control Group',
        'is_active' => true,
    ]);

    return Election::create([
        'church_group_id' => $group->id,
        'title' => 'Results Access Election',
        'start_at' => now(),
        'end_at' => now()->addHour(),
        'status' => ElectionStatus::Draft,
        'public_results_enabled' => true,
    ]);
}

test('admin users can view and export results', function () {
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);
    $election = createElectionForResultsAccess();

    $this->actingAs($admin)
        ->get(route('admin.elections.results.index', $election))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('admin.elections.results.export', $election))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('public.elections.results', $election))
        ->assertOk();
});

test('non admin users cannot view or export results', function () {
    $user = User::factory()->create([
        'is_admin' => false,
    ]);
    $election = createElectionForResultsAccess();

    $this->actingAs($user)
        ->get(route('admin.elections.results.index', $election))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('admin.elections.results.export', $election))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('public.elections.results', $election))
        ->assertForbidden();
});
