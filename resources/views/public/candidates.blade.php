@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4">
        <div>
            <p class="text-uppercase small fw-semibold text-primary mb-1">Election Candidates</p>
            <h1 class="h2 mb-1">{{ $election->title }}</h1>
            <p class="page-subtle mb-0">{{ $election->description }}</p>
        </div>
        <a href="{{ route('home') }}" class="btn btn-outline-secondary mt-3 mt-lg-0">Back to Elections</a>
    </div>

    @foreach ($election->contests as $contest)
        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="h4 mb-1">{{ $contest->name }}</h2>
                    <p class="text-muted mb-0">Choose {{ $contest->required_selections }} candidate(s)</p>
                </div>
                <span class="badge contest-badge">Max {{ $contest->max_selections }}</span>
            </div>
            <div class="row g-4">
                @foreach ($contest->candidates as $candidate)
                    <div class="col-md-6 col-xl-3">
                        <div class="card selection-card surface-card">
                            <img src="{{ $candidate->photo_url }}" alt="{{ $candidate->name }}" class="candidate-photo">
                            <div class="card-body">
                                <h3 class="h5">{{ $candidate->name }}</h3>
                                <p class="text-muted small mb-0">{{ $candidate->bio ?: 'Candidate profile not provided.' }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endforeach
@endsection
