@extends('layouts.app')

@section('content')
    <div class="card hero-card mb-4">
        <div class="card-body p-4 p-lg-5">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <p class="text-uppercase small fw-semibold mb-2">Official Election Information</p>
                    <h1 class="display-6 fw-bold">Welcome to the KKKT Temboni Church Election Page.</h1>
                    <p class="mb-0 opacity-75">Please review the election details below, confirm the correct election, and follow the voting instructions carefully before casting your ballot.</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <a href="{{ route('vote.verify.form') }}" class="btn btn-warning btn-lg">Start Voting</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card surface-card h-100">
                <div class="card-body p-4">
                    <h2 class="h4 mb-3">How to Vote</h2>
                    <ol class="mb-0 ps-3">
                        <li class="mb-2">Receive your voting token from the election desk or scan the QR code provided to you.</li>
                        <li class="mb-2">Open the voting page and verify your token. If you were given a PIN, enter it when requested.</li>
                        <li class="mb-2">Read each contest carefully and select the required number of candidates.</li>
                        <li class="mb-2">Review your selections before submission.</li>
                        <li>Submit your ballot once you are satisfied. Submitted votes cannot be changed.</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card surface-card h-100">
                <div class="card-body p-4">
                    <h2 class="h4 mb-3">Important Notes</h2>
                    <ul class="mb-0 ps-3">
                        <li class="mb-2">Voting is for eligible members only.</li>
                        <li class="mb-2">Each voter may vote once for the election assigned to them.</li>
                        <li class="mb-2">Your ballot remains secret after submission.</li>
                        <li class="mb-2">Some contests require choosing one candidate, while others may require more than one.</li>
                        <li>If you have difficulty verifying your token, please contact the election desk immediately.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        @forelse ($elections as $election)
            <div class="col-lg-6">
                <div class="card surface-card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h2 class="h4 mb-1">{{ $election->title }}</h2>
                                <p class="text-muted mb-0">{{ $election->churchGroup?->name ?? 'General Church Election' }}</p>
                            </div>
                            <span class="badge text-bg-{{ $election->status->value === 'active' ? 'success' : ($election->status->value === 'closed' ? 'secondary' : 'warning') }}">
                                {{ strtoupper($election->status->value) }}
                            </span>
                        </div>
                        <p class="page-subtle">{{ $election->description ?: 'Please review the election dates and candidate list before voting.' }}</p>
                        <div class="small text-muted mb-3">
                            <div>Starts: {{ $election->start_at->format('d M Y H:i') }}</div>
                            <div>Ends: {{ $election->end_at->format('d M Y H:i') }}</div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('public.elections.candidates', $election) }}" class="btn btn-outline-primary">View Candidates</a>
                            @if ($election->status->value === 'active')
                                <a href="{{ route('vote.verify.form') }}" class="btn btn-primary">Proceed to Vote</a>
                            @endif
                            @if (auth()->check() && auth()->user()->can('viewResults', $election))
                                <a href="{{ route('public.elections.results', $election) }}" class="btn btn-outline-dark">View Results</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info mb-0">No elections have been created yet.</div>
            </div>
        @endforelse
    </div>
@endsection
