@extends('layouts.app')

@section('content')
    <div class="card surface-card">
        <div class="card-body p-4">
            <h1 class="h3 mb-4">{{ $community->exists ? 'Edit Community' : 'Create Community' }}</h1>
            <form method="POST" action="{{ $community->exists ? route('admin.communities.update', $community) : route('admin.communities.store') }}">
                @csrf
                @if($community->exists) @method('PUT') @endif
                <div class="mb-3">
                    <label class="form-label">Community Name</label>
                    <input class="form-control" type="text" name="name" value="{{ old('name', $community->name) }}" required>
                </div>
                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="is_active">
                        <option value="1" @selected(old('is_active', (int) $community->is_active) === 1)>Active</option>
                        <option value="0" @selected(old('is_active', (int) $community->is_active) === 0)>Inactive</option>
                    </select>
                </div>
                <button class="btn btn-primary" type="submit">Save Community</button>
            </form>
        </div>
    </div>
@endsection
