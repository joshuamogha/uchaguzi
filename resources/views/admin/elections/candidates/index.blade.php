@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4">
        <div>
            <h1 class="h2 mb-1">Candidates</h1>
            <p class="page-subtle mb-0">{{ $election->title }}</p>
        </div>
        <div class="d-flex flex-column flex-sm-row gap-2 mt-3 mt-lg-0">
            <a class="btn btn-outline-dark" href="{{ route('admin.elections.candidates.export-sheet', $election) }}" target="_blank">Export Candidate Sheet</a>
            <a class="btn btn-outline-secondary" href="{{ route('admin.elections.contests.index', $election) }}">Back to Contests</a>
        </div>
    </div>

    @foreach ($contests as $contest)
        <div class="card surface-card mb-4">
            <div class="card-header px-4 pt-4 d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h4 mb-1">{{ $contest->name }}</h2>
                    <p class="text-muted mb-0">Required selections: {{ $contest->required_selections }}</p>
                </div>
                <a class="btn btn-primary" href="{{ route('admin.elections.candidates.create', [$election, $contest]) }}">Add Candidate</a>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    @forelse ($contest->candidates as $candidate)
                        <div class="col-md-6 col-xl-4">
                            <div class="card selection-card h-100">
                                <img src="{{ $candidate->photo_url }}" alt="{{ $candidate->name }}" class="candidate-photo">
                                <div class="card-body">
                                    <h3 class="h5">{{ $candidate->name }}</h3>
                                    <p class="small text-muted">{{ $candidate->bio ?: 'Candidate bio not provided.' }}</p>
                                    <div class="small mb-3">Member: {{ $candidate->member?->name ?: 'Independent candidate' }}</div>
                                    <div class="d-flex gap-2">
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.elections.candidates.edit', [$election, $contest, $candidate]) }}">Edit</a>
                                        <form method="POST" action="{{ route('admin.elections.candidates.destroy', [$election, $contest, $candidate]) }}">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this candidate?')" type="submit">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12"><div class="alert alert-light mb-0">No candidates added for this contest yet.</div></div>
                    @endforelse
                </div>
            </div>
        </div>
    @endforeach
@endsection
