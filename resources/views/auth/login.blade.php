@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card surface-card">
                <div class="card-body p-4 p-lg-5">
                    <h1 class="h3 mb-3">Admin Login</h1>
                    <p class="page-subtle">Use a record from the default `users` table to access the administration area.</p>

                    <form method="POST" action="{{ route('login.store') }}" class="mt-4">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <button class="btn btn-primary w-100" type="submit">Sign In</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
