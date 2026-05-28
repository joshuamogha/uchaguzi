@extends('layouts.app')

@section('content')
    <div class="card surface-card">
        <div class="card-body p-4">
            <h1 class="h3 mb-4">{{ $election->exists ? 'Edit Election' : 'Create Election' }}</h1>
            <form method="POST" action="{{ $election->exists ? route('admin.elections.update', $election) : route('admin.elections.store') }}">
                @csrf
                @if($election->exists) @method('PUT') @endif
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Title</label>
                        <input class="form-control" type="text" name="title" value="{{ old('title', $election->title) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Church Group</label>
                        <select class="form-select" name="church_group_id">
                            <option value="">General Election</option>
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}" @selected((string) old('church_group_id', $election->church_group_id) === (string) $group->id)>{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3">{{ old('description', $election->description) }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Start At</label>
                        <input class="form-control" type="datetime-local" name="start_at" value="{{ old('start_at', optional($election->start_at)->format('Y-m-d\TH:i')) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">End At</label>
                        <input class="form-control" type="datetime-local" name="end_at" value="{{ old('end_at', optional($election->end_at)->format('Y-m-d\TH:i')) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            @foreach($statuses as $status)
                                <option value="{{ $status->value }}" @selected(old('status', $election->status?->value ?? $election->status) === $status->value)>{{ ucfirst($status->value) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Public Results Access</label>
                        <select class="form-select" name="public_results_enabled">
                            <option value="0" @selected((int) old('public_results_enabled', $election->public_results_enabled) === 0)>Disabled</option>
                            <option value="1" @selected((int) old('public_results_enabled', $election->public_results_enabled) === 1)>Enabled</option>
                        </select>
                        <div class="form-text">Public users can only view results after the election is closed and this option is enabled.</div>
                    </div>
                </div>
                <button class="btn btn-primary mt-4" type="submit">Save Election</button>
            </form>
        </div>
    </div>
@endsection
