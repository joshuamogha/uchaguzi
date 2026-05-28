@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1 class="h2 mb-0">Communities</h1></div>
        <a class="btn btn-primary" href="{{ route('admin.communities.create') }}">Add Community</a>
    </div>

    <div class="card surface-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th style="width: 80px;">#</th><th>Name</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    @forelse ($communities as $community)
                        <tr>
                            <td>{{ ($communities->firstItem() ?? 1) + $loop->index }}</td>
                            <td>{{ $community->name }}</td>
                            <td><span class="badge text-bg-{{ $community->is_active ? 'success' : 'secondary' }}">{{ $community->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.communities.edit', $community) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.communities.destroy', $community) }}" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this community?')" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted">No communities available.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $communities->links() }}
        </div>
    </div>
@endsection
