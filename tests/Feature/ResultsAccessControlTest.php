<?php

use App\Enums\ElectionStatus;
use App\Enums\ContestType;
use App\Models\Candidate;
use App\Models\ChurchGroup;
use App\Models\Community;
use App\Models\Election;
use App\Models\ElectionContest;
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
        ->get(route('admin.elections.results.export-pdf', $election))
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
        ->get(route('admin.elections.results.export-pdf', $election))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('public.elections.results', $election))
        ->assertForbidden();
});

test('results pdf export shows contests in order with totals spoiled votes and top candidates', function () {
    $admin = User::factory()->create([
        'is_admin' => true,
    ]);
    $election = createElectionForResultsAccess();
    $community = Community::create([
        'name' => 'Mavurunza',
        'is_active' => true,
    ]);

    $firstContest = ElectionContest::create([
        'election_id' => $election->id,
        'community_id' => $community->id,
        'name' => 'First Contest',
        'contest_type' => ContestType::Community,
        'required_selections' => 1,
        'min_selections' => 1,
        'max_selections' => 1,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $secondContest = ElectionContest::create([
        'election_id' => $election->id,
        'community_id' => $community->id,
        'name' => 'Second Contest',
        'contest_type' => ContestType::Community,
        'required_selections' => 1,
        'min_selections' => 1,
        'max_selections' => 1,
        'sort_order' => 2,
        'is_active' => true,
    ]);

    $firstCandidate = Candidate::create([
        'election_id' => $election->id,
        'election_contest_id' => $firstContest->id,
        'name' => 'Alpha Candidate',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    Candidate::create([
        'election_id' => $election->id,
        'election_contest_id' => $firstContest->id,
        'name' => 'Beta Candidate',
        'sort_order' => 2,
        'is_active' => true,
    ]);

    $secondCandidate = Candidate::create([
        'election_id' => $election->id,
        'election_contest_id' => $secondContest->id,
        'name' => 'Gamma Candidate',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.elections.results.manual-entry.ballots.store', $election), [
            'selections' => [
                $firstContest->id => [$firstCandidate->id],
            ],
            'destroyed_contests' => [
                $secondContest->id => '1',
            ],
        ])
        ->assertRedirect();

    $this->actingAs($admin)
        ->post(route('admin.elections.results.manual-entry.ballots.store', $election), [
            'selections' => [
                $firstContest->id => [$firstCandidate->id],
                $secondContest->id => [$secondCandidate->id],
            ],
        ])
        ->assertRedirect();

    $response = $this->actingAs($admin)
        ->get(route('admin.elections.results.export-pdf', $election));

    $response->assertOk();
    $response->assertSeeText('DAYOSISI YA MASHARIKI NA PWANI');
    $response->assertSeeText('JIMBO LA MAGHARIBI');
    $response->assertSeeText('USHARIKA WA TEMBONI');
    $response->assertSeeText('ORODHA YA WALIOCHAGULIWA KUWA WAZEE WA KANISA TAREHE '.now()->format('d/m/Y'));
    $response->assertSeeText('Jina');
    $response->assertSeeText('ALPHA CANDIDATE');
    $response->assertSeeText('GAMMA CANDIDATE');
    $response->assertDontSeeText('FIRST CONTEST');
    $response->assertDontSeeText('SECOND CONTEST');
    $response->assertDontSeeText('Jumla ya kura: 2');
});
