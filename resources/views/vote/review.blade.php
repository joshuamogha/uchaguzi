@extends('layouts.app')

@section('content')
    <div class="card surface-card">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4">
                <div>
                    <p class="text-uppercase small fw-semibold text-primary mb-1">Review Ballot</p>
                    <h1 class="h3 mb-1">{{ $election->title }}</h1>
                    <p class="page-subtle mb-0">Confirm each contest below. Submission is final and cannot be edited later.</p>
                </div>
                <a href="{{ route('vote.ballot', $election) }}" class="btn btn-outline-secondary mt-3 mt-lg-0">Back to Ballot</a>
            </div>

            @foreach ($election->contests as $contest)
                <div class="border rounded-4 p-3 p-lg-4 mb-3">
                    <h2 class="h5">{{ $contest->name }}</h2>
                    <ul class="mb-0">
                        @foreach ($reviewSelections[$contest->id] ?? [] as $candidateId)
                            @php $candidate = $contest->candidates->firstWhere('id', $candidateId); @endphp
                            @if ($candidate)
                                <li>{{ $candidate->name }}</li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endforeach

            <button class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#confirmModal">Confirm and Submit Vote</button>
        </div>
    </div>

    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title fs-5">Final Confirmation</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Once submitted, this ballot cannot be changed. Proceed only if you have reviewed all selections.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('vote.submit', $election) }}">
                        @csrf
                        <button class="btn btn-success" type="submit">Submit Ballot</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
