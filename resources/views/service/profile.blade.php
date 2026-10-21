@extends('layouts.service')

@section('title', 'Account Settings')
@section('heading', 'Account settings')

@section('content')
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card service-card"><div class="card-body p-4">
            <h2 class="h5">Personal details</h2>
            <p class="text-muted small">Changes are sent to an administrator for review. Your current details stay active until approval.</p>
            @if($user->pending_profile_changes)
                <div class="alert alert-warning">Your personal detail changes are awaiting admin review.</div>
            @endif
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')
                <div class="mb-3"><label class="form-label" for="name">Full name</label><input class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required></div>
                <div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control" id="email" value="{{ $user->email }}" disabled><div class="form-text">Contact support to change your verified email address.</div></div>
                <div class="mb-3"><label class="form-label" for="phone">Phone</label><input class="form-control" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"></div>
                <div class="mb-3"><label class="form-label" for="address">Address</label><input class="form-control" id="address" name="address" value="{{ old('address', $user->address) }}"></div>
                <button class="btn btn-primary" type="submit" @disabled((bool) $user->pending_profile_changes)>Submit details for review</button>
            </form>
        </div></div>
    </div>
    <div class="col-lg-6">
        <div class="card service-card"><div class="card-body p-4">
            <h2 class="h5">Change password</h2>
            <form method="POST" action="{{ route('profile.password.update') }}">
                @csrf
                @method('PUT')
                <div class="mb-3"><label class="form-label" for="current_password">Current password</label><input class="form-control" type="password" id="current_password" name="current_password" required autocomplete="current-password"></div>
                <div class="mb-3"><label class="form-label" for="password">New password</label><input class="form-control" type="password" id="password" name="password" required autocomplete="new-password"></div>
                <div class="mb-3"><label class="form-label" for="password_confirmation">Confirm new password</label><input class="form-control" type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"></div>
                <button class="btn btn-outline-primary" type="submit">Update password</button>
            </form>
        </div></div>
    </div>
</div>
@endsection
