@extends('layouts.app')

@section('content')
    <div class="card surface-card">
        <div class="card-body p-4">
            <h1 class="h3 mb-2">{{ $candidate->exists ? 'Edit Candidate' : 'Add Candidate' }}</h1>
            <p class="page-subtle mb-4">{{ $election->title }} | {{ $contest->name }}</p>
            <form method="POST" enctype="multipart/form-data" action="{{ $candidate->exists ? route('admin.elections.candidates.update', [$election, $contest, $candidate]) : route('admin.elections.candidates.store', [$election, $contest]) }}">
                @csrf
                @if($candidate->exists) @method('PUT') @endif
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Display Name</label>
                        <input class="form-control" type="text" name="name" value="{{ old('name', $candidate->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Member Link</label>
                        <select class="form-select" name="member_id">
                            <option value="">No linked member</option>
                            @foreach($members as $member)
                                <option value="{{ $member->id }}" @selected((string) old('member_id', $candidate->member_id) === (string) $member->id)>{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Photo</label>
                        <input class="form-control" type="file" name="photo" accept="image/*">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sort Order</label>
                        <input class="form-control" type="number" min="1" name="sort_order" value="{{ old('sort_order', $candidate->sort_order) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="is_active">
                            <option value="1" @selected(old('is_active', (int) $candidate->is_active) === 1)>Active</option>
                            <option value="0" @selected(old('is_active', (int) $candidate->is_active) === 0)>Inactive</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Biography</label>
                        <textarea class="form-control" name="bio" rows="4">{{ old('bio', $candidate->bio) }}</textarea>
                    </div>
                </div>
                <button class="btn btn-primary mt-4" type="submit">Save Candidate</button>
            </form>
        </div>
    </div>
@endsection
