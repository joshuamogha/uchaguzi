@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card surface-card text-center">
                <div class="card-body p-4 p-lg-5">
                    <div class="display-6 text-success mb-3">Ballot Submitted</div>
                    <h1 class="h3 mb-3">Thank you for voting.</h1>
                    <p class="page-subtle">Your ballot was recorded successfully and stored anonymously.</p>
                    @if ($ballotCode)
                        <p class="mb-4"><span class="fw-semibold">Reference:</span> <code>{{ $ballotCode }}</code></p>
                    @endif
                    <a href="{{ route('home') }}" class="btn btn-primary">Return Home</a>
                </div>
            </div>
        </div>
    </div>
@endsection
