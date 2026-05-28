@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Voter QR Cards</h1>
            <p class="page-subtle mb-0">{{ $election->title }}</p>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('admin.elections.voters.index', $election) }}">Back to Voters</a>
    </div>

    @if (empty($cards))
        <div class="alert alert-warning">No plain tokens are available in session. Generate voter credentials again to print QR cards.</div>
    @else
        <div class="row g-4">
            @foreach ($cards as $card)
                <div class="col-md-6 col-xl-4">
                    <div class="card surface-card h-100">
                        <div class="card-body">
                            <h2 class="h5">{{ $card['member_name'] }}</h2>
                            <div class="qr-box border rounded-3 d-flex align-items-center justify-content-center mb-3" data-qr="{{ $card['link'] }}"></div>
                            <div class="small mb-1"><strong>Token:</strong> <code>{{ $card['token'] }}</code></div>
                            <div class="small mb-1"><strong>PIN:</strong> {{ $card['pin'] ?: 'None' }}</div>
                            <div class="small text-break"><strong>Link:</strong> {{ $card['link'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        document.querySelectorAll('[data-qr]').forEach((element) => {
            new QRCode(element, {
                text: element.dataset.qr,
                width: 120,
                height: 120
            });
        });
    </script>
@endpush
