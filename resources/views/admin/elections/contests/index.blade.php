@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4">
        <div>
            <h1 class="h2 mb-1">Election Contests</h1>
            <p class="page-subtle mb-0">{{ $election->title }}</p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-lg-0">
            <a class="btn btn-outline-secondary" href="{{ route('admin.elections.index') }}">Back</a>
            <a class="btn btn-primary" href="{{ route('admin.elections.contests.create', $election) }}">Add Contest</a>
        </div>
    </div>
    <div class="card surface-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th style="width: 80px;">#</th><th>Name</th><th>Type</th><th>Community</th><th>Selections</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    @forelse ($contests as $contest)
                        <tr>
                            <td>{{ ($contests->firstItem() ?? 1) + $loop->index }}</td>
                            <td>{{ $contest->name }}</td>
                            <td>{{ ucfirst($contest->contest_type->value) }}</td>
                            <td>{{ $contest->community?->name ?: 'N/A' }}</td>
                            <td>{{ $contest->required_selections }} required ({{ $contest->min_selections }} - {{ $contest->max_selections }})</td>
                            <td><span class="badge text-bg-{{ $contest->is_active ? 'success' : 'secondary' }}">{{ $contest->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.elections.contests.edit', [$election, $contest]) }}">Edit</a>
                                <a class="btn btn-sm btn-outline-dark" href="{{ route('admin.elections.candidates.create', [$election, $contest]) }}">Add Candidate</a>
                                <form method="POST" action="{{ route('admin.elections.contests.destroy', [$election, $contest]) }}" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this contest?')" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-muted">No contests defined for this election.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $contests->links() }}
        </div>
    </div>
@endsection
