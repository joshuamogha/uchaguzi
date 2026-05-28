@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">Members</h1>
        <a class="btn btn-primary" href="{{ route('admin.members.create') }}">Add Member</a>
    </div>
    <div class="card surface-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th style="width: 80px;">#</th><th>Name</th><th>Member No</th><th>Community</th><th>Contact</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    @forelse ($members as $member)
                        <tr>
                            <td>{{ ($members->firstItem() ?? 1) + $loop->index }}</td>
                            <td>{{ $member->name }}</td>
                            <td>{{ $member->member_no ?: 'N/A' }}</td>
                            <td>{{ $member->community?->name ?: 'Unassigned' }}</td>
                            <td>
                                <div>{{ $member->phone_number ?: 'No phone' }}</div>
                                <div class="small text-muted">{{ $member->email ?: 'No email' }}</div>
                            </td>
                            <td><span class="badge text-bg-{{ $member->is_active ? 'success' : 'secondary' }}">{{ $member->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.members.edit', $member) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.members.destroy', $member) }}" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this member?')" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-muted">No members available.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $members->links() }}
        </div>
    </div>
@endsection
