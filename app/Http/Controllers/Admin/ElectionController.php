<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ElectionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ElectionRequest;
use App\Models\ChurchGroup;
use App\Models\Election;
use App\Services\ManualResultService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ElectionController extends Controller
{
    public function __construct(
        private readonly ManualResultService $manualResultService,
    ) {
    }

    public function index(): View
    {
        $elections = Election::query()->with('churchGroup')->latest()->paginate(15);
        $elections->getCollection()->transform(function (Election $election) {
            $election->entered_ballots = $this->manualResultService->enteredBallots($election);
            $election->destroyed_contests = $this->manualResultService->destroyedContests($election);

            return $election;
        });

        return view('admin.elections.index', [
            'elections' => $elections,
            'statuses' => ElectionStatus::cases(),
            'isAdmin' => auth()->user()?->isAdmin() ?? false,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Election::class);

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
        $this->authorize('create', Election::class);
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
