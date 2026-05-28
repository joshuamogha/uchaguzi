<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ElectionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ElectionRequest;
use App\Models\ChurchGroup;
use App\Models\Election;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ElectionController extends Controller
{
    public function index(): View
    {
        return view('admin.elections.index', [
            'elections' => Election::query()->with('churchGroup')->latest()->paginate(15),
            'statuses' => ElectionStatus::cases(),
        ]);
    }

    public function create(): View
    {
        return view('admin.elections.form', [
            'election' => new Election([
                'status' => ElectionStatus::Draft,
                'public_results_enabled' => false,
                'start_at' => now()->startOfHour(),
                'end_at' => now()->addDay()->startOfHour(),
            ]),
            'groups' => ChurchGroup::where('is_active', true)->orderBy('name')->get(),
            'statuses' => ElectionStatus::cases(),
        ]);
    }

    public function store(ElectionRequest $request): RedirectResponse
    {
        $election = Election::create($request->validated());

        return redirect()->route('admin.elections.contests.index', $election)->with('success', 'Election created successfully.');
    }

    public function edit(Election $election): View
    {
        $this->authorize('update', $election);

        return view('admin.elections.form', [
            'election' => $election,
            'groups' => ChurchGroup::where('is_active', true)->orderBy('name')->get(),
            'statuses' => ElectionStatus::cases(),
        ]);
    }

    public function update(ElectionRequest $request, Election $election): RedirectResponse
    {
        $this->authorize('update', $election);
        $election->update($request->validated());

        return redirect()->route('admin.elections.index')->with('success', 'Election updated successfully.');
    }

    public function destroy(Election $election): RedirectResponse
    {
        $this->authorize('delete', $election);
        $election->delete();

        return redirect()->route('admin.elections.index')->with('success', 'Election archived successfully.');
    }
}
