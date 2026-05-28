@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4">
        <div>
            <p class="text-uppercase small fw-semibold text-primary mb-1">Election Results</p>
            <h1 class="h2 mb-1">{{ $election->title }}</h1>
            <p class="page-subtle mb-0">Registered voters: {{ $summary['registered_voters'] }} | Votes cast: {{ $summary['votes_cast'] }} | Turnout: {{ $summary['turnout_percentage'] }}%</p>
        </div>
    </div>

    @foreach ($contestResults as $contestResult)
        <div class="card surface-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center px-4 pt-4">
                <div>
                    <h2 class="h4 mb-1">{{ $contestResult['contest']->name }}</h2>
                    <p class="text-muted mb-0">Winner slots: {{ $contestResult['contest']->required_selections }}</p>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Candidate</th>
                            <th>Votes</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($contestResult['results'] as $row)
                            <tr>
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
