@extends('layouts.app')

@push('styles')
    <style>
        .public-home {
            display: grid;
            gap: 1.5rem;
        }
        .public-home-hero {
            border: 1px solid rgba(19, 34, 56, .08);
            border-radius: 1.5rem;
            overflow: hidden;
            background:
                linear-gradient(130deg, rgba(21, 76, 121, .96), rgba(27, 57, 91, .94)),
                radial-gradient(circle at top right, rgba(243, 182, 31, .32), transparent 38%);
            color: #fff;
        }
        .public-home-hero .card-body {
            padding: 2rem;
        }
        .public-home-kicker {
            text-transform: uppercase;
            letter-spacing: .12em;
            font-size: .78rem;
            font-weight: 700;
            opacity: .82;
            margin-bottom: .65rem;
        }
        .public-home-copy {
            color: rgba(255, 255, 255, .82);
            max-width: 760px;
        }
        .public-note-card,
        .public-election-card {
            border: 1px solid rgba(19, 34, 56, .08);
            border-radius: 1.25rem;
            box-shadow: 0 .95rem 1.9rem rgba(19, 34, 56, .06);
        }
        .public-note-card .card-body,
        .public-election-card .card-body {
            padding: 1.4rem 1.5rem;
        }
        .public-note-title {
            font-size: 1.05rem;
            font-weight: 800;
            margin-bottom: .9rem;
            color: #16273d;
        }
        .public-note-card li,
        .public-note-card ol li {
            margin-bottom: .6rem;
            color: #56687a;
        }
        .public-note-card li:last-child,
        .public-note-card ol li:last-child {
            margin-bottom: 0;
        }
        .public-election-card {
            background: linear-gradient(180deg, rgba(255, 255, 255, .98), rgba(246, 249, 252, .98));
        }
        .public-election-title {
            margin: 0 0 .2rem;
            font-size: 1.2rem;
            font-weight: 800;
            color: #16273d;
        }
        .public-election-group {
            color: #5f7184;
            margin-bottom: 0;
        }
        .public-election-meta {
            display: grid;
            gap: .25rem;
            color: #5f7184;
            font-size: .92rem;
        }
        .public-election-actions {
            display: flex;
            flex-wrap: wrap;
            gap: .65rem;
        }
        @media (max-width: 767.98px) {
            .public-home-hero .card-body {
                padding: 1.45rem;
            }
            .public-note-card .card-body,
            .public-election-card .card-body {
                padding: 1.15rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="public-home">
    <div class="card public-home-hero">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <p class="public-home-kicker">Official Election Information</p>
                    <h1 class="display-6 fw-bold">Welcome to the KKKT Temboni Church Election Page.</h1>
                    <p class="public-home-copy mb-0">Please review the election details below, confirm the correct election, and follow the voting instructions carefully before casting your ballot.</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <a href="{{ route('vote.verify.form') }}" class="btn btn-warning btn-lg">Start Voting</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card public-note-card h-100">
                <div class="card-body">
                    <h2 class="public-note-title">How to Vote</h2>
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
            <div class="card public-note-card h-100">
                <div class="card-body">
                    <h2 class="public-note-title">Important Notes</h2>
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
                <div class="card public-election-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h2 class="public-election-title">{{ $election->title }}</h2>
                                <p class="public-election-group">{{ $election->churchGroup?->name ?? 'General Church Election' }}</p>
                            </div>
                            <span class="badge text-bg-{{ $election->status->value === 'active' ? 'success' : ($election->status->value === 'closed' ? 'secondary' : 'warning') }}">
                                {{ strtoupper($election->status->value) }}
                            </span>
                        </div>
                        <p class="page-subtle">{{ $election->description ?: 'Please review the election dates and candidate list before voting.' }}</p>
                        <div class="public-election-meta mb-3">
                            <div>Starts: {{ $election->start_at->format('d M Y H:i') }}</div>
                            <div>Ends: {{ $election->end_at->format('d M Y H:i') }}</div>
                        </div>
                        <div class="public-election-actions">
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
    </div>
@endsection
