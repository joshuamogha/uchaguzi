@extends('layouts.app')

@section('content')
    <div class="card surface-card">
        <div class="card-body p-4">
            <h1 class="h3 mb-2">{{ $contest->exists ? 'Edit Contest' : 'Create Contest' }}</h1>
            <p class="page-subtle mb-4">{{ $election->title }}</p>
            <form method="POST" action="{{ $contest->exists ? route('admin.elections.contests.update', [$election, $contest]) : route('admin.elections.contests.store', $election) }}">
                @csrf
                @if($contest->exists) @method('PUT') @endif
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Contest Name</label>
                        <input class="form-control" type="text" name="name" value="{{ old('name', $contest->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contest Type</label>
                        <select class="form-select" name="contest_type">
                            @foreach($contestTypes as $type)
                                <option value="{{ $type->value }}" @selected(old('contest_type', $contest->contest_type?->value ?? $contest->contest_type) === $type->value)>{{ ucfirst($type->value) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Community</label>
                        <select class="form-select" name="community_id">
                            <option value="">Not community specific</option>
                            @foreach($communities as $community)
                                <option value="{{ $community->id }}" @selected((string) old('community_id', $contest->community_id) === (string) $community->id)>{{ $community->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Required</label>
                        <input class="form-control" type="number" min="1" name="required_selections" value="{{ old('required_selections', $contest->required_selections) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Min</label>
                        <input class="form-control" type="number" min="1" name="min_selections" value="{{ old('min_selections', $contest->min_selections) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Max</label>
                        <input class="form-control" type="number" min="1" name="max_selections" value="{{ old('max_selections', $contest->max_selections) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Sort Order</label>
                        <input class="form-control" type="number" min="1" name="sort_order" value="{{ old('sort_order', $contest->sort_order) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="is_active">
                            <option value="1" @selected(old('is_active', (int) $contest->is_active) === 1)>Active</option>
                            <option value="0" @selected(old('is_active', (int) $contest->is_active) === 0)>Inactive</option>
                        </select>
                    </div>
                </div>
                <button class="btn btn-primary mt-4" type="submit">Save Contest</button>
            </form>
        </div>
    </div>
@endsection
