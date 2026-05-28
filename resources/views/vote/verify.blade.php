@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card surface-card">
                <div class="card-body p-4 p-lg-5">
                    <h1 class="h3 mb-3">Voter Verification</h1>
                    <p class="page-subtle">Enter or scan your voting token. If your voter profile has a PIN, you will be asked to confirm it before the ballot opens.</p>

                    @if (! $voter)
                        <form method="POST" action="{{ route('vote.verify') }}" class="mt-4">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Voting Token</label>
                                <input type="text" name="token" value="{{ old('token', $token) }}" class="form-control form-control-lg" required>
                            </div>
                            <button class="btn btn-primary" type="submit">Verify Token</button>
                        </form>
                    @else
                        <div class="alert alert-success">
                            Token accepted for <strong>{{ \Illuminate\Support\Str::mask($voter->member->name, '*', 2) }}</strong> in <strong>{{ $voter->election->title }}</strong>.
                        </div>

                        <form method="POST" action="{{ route('vote.confirm-pin') }}" class="mt-4">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            <dl class="row mb-4">
                                <dt class="col-sm-4">Election</dt>
                                <dd class="col-sm-8">{{ $voter->election->title }}</dd>
                                <dt class="col-sm-4">Voting window</dt>
                                <dd class="col-sm-8">{{ $voter->election->start_at->format('d M Y H:i') }} - {{ $voter->election->end_at->format('d M Y H:i') }}</dd>
                            </dl>

                            @if ($voter->requiresPin())
                                <div class="mb-3">
                                    <label class="form-label">6-digit PIN</label>
                                    <input type="password" name="pin" class="form-control form-control-lg" maxlength="6" required>
                                </div>
                            @else
                                <div class="alert alert-info">No PIN is configured for this voter. Continue to open the ballot.</div>
                            @endif

                            <button class="btn btn-primary" type="submit">Continue to Ballot</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
