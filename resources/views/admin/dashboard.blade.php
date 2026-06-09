@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">{{ $isAdmin ? 'Admin Dashboard' : 'Ballot Entry Dashboard' }}</h1>
            <p class="page-subtle mb-0">
                {{ $isAdmin ? 'Operational overview for elections, voters, and turnout.' : 'View elections and open the manual ballot-entry workspace.' }}
            </p>
        </div>
    </div>

    @if ($isAdmin)
        <div class="row g-4 stats-grid mb-4">
            @foreach ($stats as $label => $value)
                <div class="col-md-6 col-xl-4">
                    <div class="card surface-card h-100">
                        <div class="card-body">
                            <div class="text-muted text-uppercase small">{{ str_replace('_', ' ', $label) }}</div>
                            <div class="display-6 fw-semibold">{{ $value }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-5">
                <div class="card surface-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h2 class="h4 mb-1">Turnout Chart</h2>
                                <p class="page-subtle mb-0">Registered voters against ballots already cast.</p>
                            </div>
                            <span class="badge text-bg-success">{{ $dashboardCharts['turnout']['turnout_percentage'] }}%</span>
                        </div>
                        <div style="height: 320px;">
                            <canvas id="dashboardTurnoutChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-7">
                <div class="card surface-card h-100">
                    <div class="card-body">
                        <h2 class="h4 mb-1">Election Status Chart</h2>
                        <p class="page-subtle mb-3">Count of elections grouped by lifecycle status.</p>
                        <div style="height: 320px;">
                            <canvas id="dashboardStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="card surface-card">
        <div class="card-body">
            <h2 class="h4 mb-3">{{ $isAdmin ? 'Recent Elections' : 'Available Elections' }}</h2>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th style="width: 80px;">#</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Window</th>
                        <th>Ballots Entered</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($recentElections as $election)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $election->title }}</td>
                            <td><span class="badge text-bg-secondary">{{ strtoupper($election->status->value) }}</span></td>
                            <td>{{ $election->start_at->format('d M Y H:i') }} - {{ $election->end_at->format('d M Y H:i') }}</td>
                            <td>{{ $election->entered_ballots }}</td>
                            <td>
                                @if ($isAdmin)
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.elections.contests.index', $election) }}">Manage</a>
                                @else
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.elections.results.manual-entry', $election) }}">Enter Ballot</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">No elections found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@if ($isAdmin)
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
        <script>
            const turnoutData = @json($dashboardCharts['turnout']);
            const statusData = @json($dashboardCharts['statuses']);

            new Chart(document.getElementById('dashboardTurnoutChart'), {
                type: 'doughnut',
                data: {
                    labels: turnoutData.labels,
                    datasets: [{
                        data: turnoutData.values,
                        backgroundColor: ['#154c79', '#d7e3ef'],
                        borderWidth: 0
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    cutout: '68%'
                }
            });

            new Chart(document.getElementById('dashboardStatusChart'), {
                type: 'bar',
                data: {
                    labels: statusData.labels,
                    datasets: [{
                        label: 'Elections',
                        data: statusData.values,
                        backgroundColor: ['#adb5bd', '#1f7a4c', '#154c79', '#b02a37'],
                        borderRadius: 8
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        </script>
    @endpush
@endif
