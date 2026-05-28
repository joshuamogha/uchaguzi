@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4">
        <div>
            <h1 class="h2 mb-1">Voters</h1>
            <p class="page-subtle mb-0">{{ $election->title }}</p>
        </div>
        <a class="btn btn-outline-secondary mt-3 mt-lg-0" href="{{ route('admin.elections.index') }}">Back to Elections</a>
    </div>

    <div class="card surface-card mb-4">
        <div class="card-body">
            <h2 class="h4 mb-3">Generate Voters</h2>
            <form method="POST" action="{{ route('admin.elections.voters.generate', $election) }}" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-5">
                    <label class="form-label">Community Filter</label>
                    <select class="form-select" name="community_id">
                        <option value="">All active members</option>
                        @foreach($communities as $community)
                            <option value="{{ $community->id }}">{{ $community->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">PIN Mode</label>
                    <select class="form-select" name="generate_pin">
                        <option value="1">Generate PINs</option>
                        <option value="0">Token only</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100" type="submit">Generate Tokens</button>
                </div>
            </form>
            @if ($generatedCredentials->isNotEmpty())
                <div class="alert alert-info mt-3 mb-0">Fresh plain tokens are only available in this session. Open the cards page to print or export them before navigating away.</div>
            @endif
        </div>
    </div>

    <div class="card surface-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h4 mb-0">Registered Voters</h2>
                <a class="btn btn-outline-primary" href="{{ route('admin.elections.voters.cards', $election) }}">QR Cards</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th style="width: 80px;">#</th><th>Member</th><th>Community</th><th>Phone</th><th>Status</th><th>Voted</th><th></th></tr></thead>
                    <tbody>
                    @forelse ($voters as $voter)
                        <tr>
                            <td>{{ ($voters->firstItem() ?? 1) + $loop->index }}</td>
                            <td>{{ $voter->member->name }}</td>
                            <td>{{ $voter->member->community?->name ?: 'N/A' }}</td>
                            <td>{{ $voter->phone_number ?: 'N/A' }}</td>
                            <td><span class="badge text-bg-{{ $voter->is_eligible ? 'success' : 'secondary' }}">{{ $voter->is_eligible ? 'Eligible' : 'Blocked' }}</span></td>
                            <td><span class="badge text-bg-{{ $voter->has_voted ? 'primary' : 'light' }}">{{ $voter->has_voted ? 'Yes' : 'No' }}</span></td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.elections.voters.toggle-eligibility', [$election, $voter]) }}">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-outline-dark" type="submit">{{ $voter->is_eligible ? 'Disable' : 'Enable' }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-muted">No voters have been generated yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $voters->links() }}
        </div>
    </div>
@endsection
