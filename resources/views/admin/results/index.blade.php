@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4">
        <div>
            <h1 class="h2 mb-1">Election Results</h1>
            <p class="page-subtle mb-0">
                {{ $election->title }}
                @if ($summary['result_source'] === 'manual')
                    | Source: Manual tally entry
                @else
                    | Registered: {{ $summary['registered_voters'] }} | Cast: {{ $summary['votes_cast'] }} | Turnout: {{ $summary['turnout_percentage'] }}%
                @endif
            </p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-lg-0">
            <a class="btn btn-outline-secondary" href="{{ route('admin.elections.index') }}">Back</a>
            @can('manageResults', $election)
                <a class="btn btn-outline-dark" href="{{ route('admin.elections.results.manual-entry', $election) }}">{{ $hasManualTallies ? 'Edit Manual Results' : 'Enter Manual Results' }}</a>
            @endcan
            @can('exportResults', $election)
                <a class="btn btn-primary" href="{{ route('admin.elections.results.export', $election) }}">Export CSV</a>
            @endcan
        </div>
    </div>

    @if ($summary['result_source'] === 'manual')
        <div class="alert alert-warning mb-4">
            This report is currently using manually entered vote totals from the paper candidate sheets.
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <div class="card surface-card h-100">
                <div class="card-body">
                    <h2 class="h4 mb-1">Community Turnout Chart</h2>
                    <p class="page-subtle mb-3">Participation by community for this election.</p>
                    <div style="height: 340px;">
                        <canvas id="communityTurnoutChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card surface-card h-100">
                <div class="card-body">
                    <h2 class="h4 mb-1">Margin / Runoff Insight</h2>
                    <p class="page-subtle mb-3">Winning margin per contest. Orange bars indicate a runoff tie at the cutoff.</p>
                    <div style="height: 340px;">
                        <canvas id="marginInsightChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @foreach ($contestResults as $contestResult)
        <div class="card surface-card mb-4">
            <div class="card-header px-4 pt-4">
                <h2 class="h4 mb-1">{{ $contestResult['contest']->name }}</h2>
                <p class="text-muted mb-0">Winner slots: {{ $contestResult['contest']->required_selections }}</p>
                @if ($contestResult['requires_runoff'])
                    <div class="alert alert-warning mt-3 mb-0">
                        Tie detected at the winner cutoff.
                        {{ $contestResult['runoff_slots'] }} slot(s) remain unresolved between
                        {{ $contestResult['runoff_candidates']->pluck('candidate.name')->join(', ') }}.
                        <form method="POST" action="{{ route('admin.elections.results.runoff', [$election, $contestResult['contest']]) }}" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-dark ms-2" type="submit">Create Runoff Election</button>
                        </form>
                    </div>
                @endif
            </div>
            <div class="card-body">
                <div class="mb-4" style="height: 320px;">
                    <canvas id="contestChart{{ $contestResult['contest']->id }}"></canvas>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th style="width: 80px;">#</th><th>Rank</th><th>Candidate</th><th>Votes</th><th>Status</th></tr></thead>
                        <tbody>
                        @foreach ($contestResult['results'] as $row)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $row['ranking'] }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ $row['candidate']->photo_url }}" alt="{{ $row['candidate']->name }}" class="candidate-avatar-sm">
                                        <div>
                                            <div class="fw-semibold">{{ $row['candidate']->name }}</div>
                                            <div class="small text-muted">{{ $row['candidate']->bio }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $row['votes'] }}</td>
                                <td>
                                    @if ($row['is_tied_winner'])
                                        <span class="badge text-bg-warning">Tied Winner</span>
                                    @elseif ($row['is_winner'])
                                        <span class="badge text-bg-success">Winner</span>
                                    @else
                                        <span class="badge text-bg-light">Participant</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        const contestResults = @json($contestResults);
        const communityTurnout = @json($communityTurnout);
        const marginInsights = @json($marginInsights);

        if (communityTurnout.labels.length) {
            new Chart(document.getElementById('communityTurnoutChart'), {
                type: 'bar',
                data: {
                    labels: communityTurnout.labels,
                    datasets: [
                        {
                            label: 'Registered',
                            data: communityTurnout.registered,
                            backgroundColor: '#d7e3ef',
                            borderRadius: 8
                        },
                        {
                            label: 'Voted',
                            data: communityTurnout.voted,
                            backgroundColor: '#154c79',
                            borderRadius: 8
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    }
                }
            });
        }

        new Chart(document.getElementById('marginInsightChart'), {
            type: 'bar',
            data: {
                labels: marginInsights.labels,
                datasets: [{
                    label: 'Winning Margin',
                    data: marginInsights.margins,
                    backgroundColor: marginInsights.runoff_flags.map((flag) => flag ? '#f3b61f' : '#1f7a4c'),
                    borderRadius: 8
                }]
            },
            options: {
                maintainAspectRatio: false,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            afterLabel: (context) => {
                                const topVotes = marginInsights.top_votes[context.dataIndex];
                                const secondVotes = marginInsights.second_votes[context.dataIndex];
                                return `Top: ${topVotes}, Second: ${secondVotes}`;
                            }
                        }
                    }
                }
            }
        });

        contestResults.forEach((contestResult) => {
            const canvas = document.getElementById(`contestChart${contestResult.contest.id}`);
            if (!canvas) {
                return;
            }

            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: contestResult.results.map((row) => row.candidate.name),
                    datasets: [{
                        label: 'Votes',
                        data: contestResult.results.map((row) => row.votes),
                        backgroundColor: contestResult.results.map((row) => row.is_tied_winner ? '#f3b61f' : (row.is_winner ? '#1f7a4c' : '#154c79')),
                        borderRadius: 8
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        });
    </script>
@endpush
