<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ElectionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ManualBallotEntryRequest;
use App\Http\Requests\Admin\ManualResultEntryRequest;
use App\Models\Election;
use App\Models\ElectionContest;
use App\Services\ManualResultService;
use App\Services\ResultService;
use App\Services\RunoffElectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ElectionResultController extends Controller
{
    public function __construct(
        private readonly ResultService $resultService,
        private readonly ManualResultService $manualResultService,
        private readonly RunoffElectionService $runoffElectionService,
    ) {
    }

    public function index(Election $election): View
    {
        $this->authorize('viewResults', $election);
        $contestResults = $this->resultService->contestResults($election);

        return view('admin.results.index', [
            'election' => $election,
            'summary' => $this->resultService->summary($election),
            'contestResults' => $contestResults,
            'communityTurnout' => $this->resultService->communityTurnout($election),
            'marginInsights' => $this->resultService->marginInsights($contestResults),
            'hasManualTallies' => $this->manualResultService->hasManualTallies($election),
        ]);
    }

    public function editManualEntry(Election $election): View
    {
        $this->authorize('enterManualBallots', $election);

        return view('admin.results.manual-entry', [
            'election' => $election,
            'contests' => $election->contests()
                ->with([
                    'community',
                    'manualSummary',
                    'candidates' => fn ($query) => $query
                        ->with([
                            'member',
                            'manualTallies' => fn ($manualQuery) => $manualQuery->where('election_id', $election->id),
                        ])
                        ->where('is_active', true),
                ])
                ->where('is_active', true)
                ->get(),
            'enteredBallots' => $this->manualResultService->enteredBallots($election),
            'destroyedContests' => $this->manualResultService->destroyedContests($election),
            'recentManualEntries' => $this->manualResultService->recentManualEntries($election),
            'isReadOnly' => $election->status === ElectionStatus::Closed,
        ]);
    }

    public function storeManualBallot(ManualBallotEntryRequest $request, Election $election): RedirectResponse
    {
        $this->authorize('enterManualBallots', $election);

        if ($election->status === ElectionStatus::Closed) {
            return redirect()
                ->route('admin.elections.results.manual-entry', $election)
                ->withErrors([
                    'manual_ballot' => 'This election is closed. Manual ballot entry is now read only.',
                ]);
        }

        $this->manualResultService->recordBallot(
            $election,
            $request->validated('selections', []),
            array_keys(array_filter($request->input('destroyed_contests', []))),
            $request->user(),
            $request,
        );

        return redirect()
            ->route('admin.elections.results.manual-entry', $election)
            ->with('success', 'Paper ballot entry recorded successfully.');
    }

    public function updateManualEntry(ManualResultEntryRequest $request, Election $election): RedirectResponse
    {
        $this->authorize('manageResults', $election);

        $this->manualResultService->save($election, $request->validated('votes'));

        return redirect()
            ->route('admin.elections.results.index', $election)
            ->with('success', 'Manual election results saved successfully.');
    }

    public function export(Election $election): Response
    {
        $this->authorize('exportResults', $election);

        $rows = $this->resultService->exportRows($election);
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['Contest', 'Candidate', 'Votes', 'Ranking', 'Winner', 'Total Contest Votes', 'Spoiled Votes']);

        foreach ($rows as $row) {
            fputcsv($handle, array_values($row));
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="election-results-'.$election->id.'.csv"',
        ]);
    }

    public function exportPdf(Election $election): View
    {
        $this->authorize('exportResults', $election);

        $contestResults = $this->resultService->contestResults($election);

        return view('admin.results.export-pdf', [
            'election' => $election->load('churchGroup'),
            'summary' => $this->resultService->summary($election),
            'contestResults' => $contestResults,
        ]);
    }

    public function createRunoff(Election $election, ElectionContest $contest): RedirectResponse
    {
        $this->authorize('manageResults', $election);

        $contestResult = collect($this->resultService->contestResults($election))
            ->firstWhere('contest.id', $contest->id);

        abort_unless($contestResult && $contestResult['requires_runoff'], 422, 'This contest does not require a runoff election.');

        $runoffElection = $this->runoffElectionService->createFromTie($election->load('voters'), $contest, $contestResult);

        return redirect()
            ->route('admin.elections.contests.index', $runoffElection)
            ->with('success', 'Runoff election created successfully. Generate fresh voter tokens before opening it.');
    }
}
