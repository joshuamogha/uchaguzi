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

                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input class="form-control" type="text" name="phone_number" value="{{ old('phone_number', $managedUser->phone_number) }}" required>
                    <div class="form-text">{{ $managedUser->exists ? 'Used for SMS notifications.' : 'The password will be sent to this phone number by SMS.' }}</div>
                </div>

                @if ($managedUser->exists)
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">New Password</label>
                            <input class="form-control" type="password" name="password">
                            <div class="form-text">Leave blank to keep the current password.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password</label>
                            <input class="form-control" type="password" name="password_confirmation">
                        </div>
                    </div>
                @else
                    <div class="alert alert-info mt-3 mb-0">
                        The system will generate a password automatically and send it to this phone number by SMS.
                    </div>
                @endif

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
