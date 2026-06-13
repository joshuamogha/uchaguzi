@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">Elections</h1>
        @if ($isAdmin)
            <a class="btn btn-primary" href="{{ route('admin.elections.create') }}">Create Election</a>
        @endif
    </div>
    <div class="card surface-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th style="width: 80px;">#</th><th>Title</th><th>Group</th><th>Window</th><th>Status</th><th>Paper Ballots</th>@if($isAdmin)<th>Public Results</th>@endif<th></th></tr></thead>
                    <tbody>
                    @forelse ($elections as $election)
                        <tr>
                            <td>{{ ($elections->firstItem() ?? 1) + $loop->index }}</td>
                            <td>{{ $election->title }}</td>
                            <td>{{ $election->churchGroup?->name ?: 'General' }}</td>
                            <td>{{ $election->start_at->format('d M Y H:i') }} - {{ $election->end_at->format('d M Y H:i') }}</td>
                            <td><span class="badge text-bg-secondary">{{ strtoupper($election->status->value) }}</span></td>
                            <td>
                                {{ $election->entered_ballots }}
                                @if ($election->destroyed_contests > 0)
                                    <div class="small text-danger">Destroyed: {{ $election->destroyed_contests }}</div>
                                @endif
                            </td>
                            @if ($isAdmin)
                                <td><span class="badge text-bg-{{ $election->public_results_enabled ? 'success' : 'secondary' }}">{{ $election->public_results_enabled ? 'Enabled' : 'Disabled' }}</span></td>
                            @endif
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.elections.results.manual-entry', $election) }}">{{ $isAdmin ? 'Manual Entry' : 'Enter Ballot' }}</a>
                                <a class="btn btn-sm btn-outline-dark" href="{{ route('admin.elections.candidates.export-sheet', $election) }}" target="_blank">Candidate Sheet</a>
                                <a class="btn btn-sm btn-outline-dark" href="{{ route('admin.elections.candidates.export-contest-pdf', $election) }}" target="_blank">Candidate List PDF</a>
                                @if ($isAdmin)
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.elections.edit', $election) }}">Edit</a>
                                    <a class="btn btn-sm btn-outline-dark" href="{{ route('admin.elections.contests.index', $election) }}">Contests</a>
                                    <a class="btn btn-sm btn-outline-success" href="{{ route('admin.elections.voters.index', $election) }}">Voters</a>
                                    @can('viewResults', $election)
                                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.elections.results.index', $election) }}">Results</a>
                                    @endcan
                                    <form method="POST" action="{{ route('admin.elections.destroy', $election) }}" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Archive this election?')" type="submit">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $isAdmin ? 8 : 7 }}" class="text-muted">No elections available.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $elections->links() }}
        </div>
    </div>
@endsection
