<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\ElectionContest;
use App\Services\ResultService;
use App\Services\RunoffElectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ElectionResultController extends Controller
{
    public function __construct(
        private readonly ResultService $resultService,
        private readonly RunoffElectionService $runoffElectionService,
    ) {
    }

    public function index(Election $election): View
    {
        $this->authorize('view', $election);
        $contestResults = $this->resultService->contestResults($election);

        return view('admin.results.index', [
            'election' => $election,
            'summary' => $this->resultService->summary($election),
            'contestResults' => $contestResults,
            'communityTurnout' => $this->resultService->communityTurnout($election),
            'marginInsights' => $this->resultService->marginInsights($contestResults),
        ]);
    }

    public function export(Election $election): Response
    {
        $this->authorize('exportResults', $election);

        $rows = $this->resultService->exportRows($election);
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['Contest', 'Candidate', 'Votes', 'Ranking', 'Winner']);

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

    public function createRunoff(Election $election, ElectionContest $contest): RedirectResponse
    {
        $this->authorize('update', $election);

        $contestResult = collect($this->resultService->contestResults($election))
            ->firstWhere('contest.id', $contest->id);

        abort_unless($contestResult && $contestResult['requires_runoff'], 422, 'This contest does not require a runoff election.');

        $runoffElection = $this->runoffElectionService->createFromTie($election->load('voters'), $contest, $contestResult);

        return redirect()
            ->route('admin.elections.contests.index', $runoffElection)
            ->with('success', 'Runoff election created successfully. Generate fresh voter tokens before opening it.');
    }
}
