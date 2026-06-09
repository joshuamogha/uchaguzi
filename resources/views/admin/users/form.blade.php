@extends('layouts.app')

@section('content')
    <div class="card surface-card">
        <div class="card-body p-4">
            <h1 class="h3 mb-4">{{ $managedUser->exists ? 'Edit User' : 'Create User' }}</h1>
            <form method="POST" action="{{ $managedUser->exists ? route('admin.users.update', $managedUser) : route('admin.users.store') }}">
                @csrf
                @if($managedUser->exists) @method('PUT') @endif

                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input class="form-control" type="text" name="name" value="{{ old('name', $managedUser->name) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input class="form-control" type="email" name="email" value="{{ old('email', $managedUser->email) }}" required>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ $managedUser->exists ? 'New Password' : 'Password' }}</label>
                        <input class="form-control" type="password" name="password" {{ $managedUser->exists ? '' : 'required' }}>
                        @if ($managedUser->exists)
                            <div class="form-text">Leave blank to keep the current password.</div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password</label>
                        <input class="form-control" type="password" name="password_confirmation" {{ $managedUser->exists ? '' : 'required' }}>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <label class="form-label">Access Role</label>
                        <select class="form-select" name="is_admin">
                            <option value="1" @selected((int) old('is_admin', $managedUser->is_admin) === 1)>Admin</option>
                            <option value="0" @selected((int) old('is_admin', $managedUser->is_admin) === 0)>Ballot Entry User</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Account Status</label>
                        <select class="form-select" name="is_active">
                            <option value="1" @selected((int) old('is_active', $managedUser->is_active) === 1)>Active</option>
                            <option value="0" @selected((int) old('is_active', $managedUser->is_active) === 0)>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">{{ $managedUser->exists ? 'Save User' : 'Create User' }}</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.users.index') }}">Back</a>
                </div>
            </form>
        </div>
    </div>
@endsection
