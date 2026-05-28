@extends('layouts.app')

@section('content')
    <div class="card surface-card">
        <div class="card-body p-4">
            <h1 class="h3 mb-4">{{ $member->exists ? 'Edit Member' : 'Create Member' }}</h1>
            <form method="POST" action="{{ $member->exists ? route('admin.members.update', $member) : route('admin.members.store') }}">
                @csrf
                @if($member->exists) @method('PUT') @endif
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input class="form-control" type="text" name="name" value="{{ old('name', $member->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Member Number</label>
                        <input class="form-control" type="text" name="member_no" value="{{ old('member_no', $member->member_no) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone Number</label>
                        <input class="form-control" type="text" name="phone_number" value="{{ old('phone_number', $member->phone_number) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input class="form-control" type="email" name="email" value="{{ old('email', $member->email) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Community</label>
                        <select class="form-select" name="community_id">
                            <option value="">No Community</option>
                            @foreach($communities as $community)
                                <option value="{{ $community->id }}" @selected((string) old('community_id', $member->community_id) === (string) $community->id)>{{ $community->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="is_active">
                            <option value="1" @selected(old('is_active', (int) $member->is_active) === 1)>Active</option>
                            <option value="0" @selected(old('is_active', (int) $member->is_active) === 0)>Inactive</option>
                        </select>
                    </div>
                </div>
                <button class="btn btn-primary mt-4" type="submit">Save Member</button>
            </form>
        </div>
    </div>
@endsection
