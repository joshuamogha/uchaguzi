<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\Member;
use App\Models\Voter;
use App\Services\ManualResultService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ManualResultService $manualResultService,
    ) {
    }

    public function __invoke(): View
    {
        $isAdmin = auth()->user()?->isAdmin() ?? false;
        $totalVoters = Voter::count();
        $votesCast = Voter::where('has_voted', true)->count();
        $statusCounts = Election::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $turnoutPercentage = $totalVoters > 0 ? round(($votesCast / $totalVoters) * 100, 2) : 0;
        $recentElections = Election::latest()->take(5)->get()->map(function (Election $election) {
            $election->entered_ballots = $this->manualResultService->enteredBallots($election);
            $election->destroyed_contests = $this->manualResultService->destroyedContests($election);

            return $election;
        });

        return view('admin.dashboard', [
            'isAdmin' => $isAdmin,
            'stats' => [
                'total_elections' => Election::count(),
                'active_elections' => Election::where('status', 'active')->count(),
                'total_members' => Member::count(),
                'total_voters' => $totalVoters,
                'votes_cast' => $votesCast,
                'turnout_percentage' => $turnoutPercentage,
            ],
            'recentElections' => $recentElections,
            'dashboardCharts' => [
                'turnout' => [
                    'labels' => ['Votes Cast', 'Pending Voters'],
                    'values' => [$votesCast, max($totalVoters - $votesCast, 0)],
                    'turnout_percentage' => $turnoutPercentage,
                ],
                'statuses' => [
                    'labels' => ['Draft', 'Active', 'Closed', 'Cancelled'],
                    'values' => [
                        (int) ($statusCounts['draft'] ?? 0),
                        (int) ($statusCounts['active'] ?? 0),
                        (int) ($statusCounts['closed'] ?? 0),
                        (int) ($statusCounts['cancelled'] ?? 0),
                    ],
                ],
            ],
        ]);
    }
}
