@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">My Profile</h1>
            <p class="page-subtle mb-0">Manage your account details and change your password.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card surface-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                             style="width:72px;height:72px;background:#154c79;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <h2 class="h4 mb-1">{{ $user->name }}</h2>
                            <div class="text-muted">{{ $user->email }}</div>
                        </div>
                    </div>

                    <dl class="row mb-0">
                        <dt class="col-sm-5">Account Created</dt>
                        <dd class="col-sm-7">{{ $user->created_at?->format('d M Y H:i') }}</dd>
                        <dt class="col-sm-5">Last Updated</dt>
                        <dd class="col-sm-7">{{ $user->updated_at?->format('d M Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card surface-card mb-4">
                <div class="card-body p-4">
                    <h2 class="h4 mb-3">Update Profile</h2>
                    <form method="POST" action="{{ route('admin.profile.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input class="form-control" type="text" name="name" value="{{ old('name', $user->name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input class="form-control" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                            </div>
                        </div>
                        <button class="btn btn-primary mt-4" type="submit">Save Profile</button>
                    </form>
                </div>
            </div>

            <div class="card surface-card">
                <div class="card-body p-4">
                    <h2 class="h4 mb-3">Change Password</h2>
                    <form method="POST" action="{{ route('admin.profile.password.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Current Password</label>
                                <input class="form-control" type="password" name="current_password" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">New Password</label>
                                <input class="form-control" type="password" name="password" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm New Password</label>
                                <input class="form-control" type="password" name="password_confirmation" required>
                            </div>
                        </div>
                        <button class="btn btn-dark mt-4" type="submit">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
