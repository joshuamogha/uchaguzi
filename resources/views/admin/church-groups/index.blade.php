@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">Church Groups</h1>
        <a class="btn btn-primary" href="{{ route('admin.church-groups.create') }}">Add Group</a>
    </div>
    <div class="card surface-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th style="width: 80px;">#</th><th>Name</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    @forelse ($groups as $group)
                        <tr>
                            <td>{{ ($groups->firstItem() ?? 1) + $loop->index }}</td>
                            <td>{{ $group->name }}</td>
                            <td><span class="badge text-bg-{{ $group->is_active ? 'success' : 'secondary' }}">{{ $group->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.church-groups.edit', $group) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.church-groups.destroy', $group) }}" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this group?')" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted">No groups available.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $groups->links() }}
        </div>
    </div>
@endsection
