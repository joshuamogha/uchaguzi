@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-0">Users</h1>
            <p class="page-subtle mb-0">Create administrators or ballot-entry users and control access status.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('admin.users.create') }}">Add User</a>
    </div>

    <div class="card surface-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th style="width: 80px;">#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th class="text-end"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ ($users->firstItem() ?? 1) + $loop->index }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge text-bg-{{ $user->is_admin ? 'dark' : 'secondary' }}">
                                    {{ $user->is_admin ? 'Admin' : 'Ballot Entry' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge text-bg-{{ $user->is_active ? 'success' : 'secondary' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.users.edit', $user) }}">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">No users available.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $users->links() }}
        </div>
    </div>
@endsection
