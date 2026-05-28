<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContestType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ContestRequest;
use App\Models\Community;
use App\Models\Election;
use App\Models\ElectionContest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ElectionContestController extends Controller
{
    public function index(Election $election): View
    {
        $this->authorize('view', $election);

        return view('admin.elections.contests.index', [
            'election' => $election,
            'contests' => $election->contests()->with('community')->paginate(20),
        ]);
    }

    public function create(Election $election): View
    {
        $this->authorize('update', $election);

        return view('admin.elections.contests.form', [
            'election' => $election,
            'contest' => new ElectionContest([
                'contest_type' => ContestType::Position,
                'required_selections' => 1,
                'min_selections' => 1,
                'max_selections' => 1,
                'sort_order' => ($election->contests()->max('sort_order') ?? 0) + 1,
                'is_active' => true,
            ]),
            'communities' => Community::where('is_active', true)->orderBy('name')->get(),
            'contestTypes' => ContestType::cases(),
        ]);
    }

    public function store(ContestRequest $request, Election $election): RedirectResponse
    {
        $this->authorize('update', $election);
        $election->contests()->create($request->validated());

        return redirect()->route('admin.elections.contests.index', $election)->with('success', 'Contest created successfully.');
    }

    public function edit(Election $election, ElectionContest $contest): View
    {
        $this->authorize('update', $election);

        return view('admin.elections.contests.form', [
            'election' => $election,
            'contest' => $contest,
            'communities' => Community::where('is_active', true)->orderBy('name')->get(),
            'contestTypes' => ContestType::cases(),
        ]);
    }

    public function update(ContestRequest $request, Election $election, ElectionContest $contest): RedirectResponse
    {
        $this->authorize('update', $election);
        $contest->update($request->validated());

        return redirect()->route('admin.elections.contests.index', $election)->with('success', 'Contest updated successfully.');
    }

    public function destroy(Election $election, ElectionContest $contest): RedirectResponse
    {
        $this->authorize('update', $election);
        $contest->delete();

        return redirect()->route('admin.elections.contests.index', $election)->with('success', 'Contest deleted successfully.');
    }
}
