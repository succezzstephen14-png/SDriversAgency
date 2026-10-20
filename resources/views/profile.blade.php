@extends('layouts.dashboard')

@section('title', 'Profile')

@section('content')
<div class="container-max content-section">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-user"></i> Profile</h4>
                        @if(!empty($user->phone) && !empty($user->address))
                            <span class="badge bg-success">Complete</span>
                        @else
                            <span class="badge bg-warning">Incomplete</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success" role="status">{{ session('success') }}</div>
                    @endif

                    @if($user->pending_profile_changes)
                        <div class="alert alert-warning" role="status">
                            Your profile update is awaiting admin review. The details shown below remain your active approved information until the request is approved.
                        </div>
                    @endif

                    <dl class="row">
                        <dt class="col-sm-4">Name</dt>
                        <dd class="col-sm-8">{{ $user->name }}</dd>

                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8">{{ $user->email }}</dd>

                        <dt class="col-sm-4">Role</dt>
                        <dd class="col-sm-8">{{ ucfirst($user->role) }}</dd>

                        <dt class="col-sm-4">Phone</dt>
                        <dd class="col-sm-8">
                            {{ $user->phone ?? '-' }}
                            @if(!$user->phone)
                                <small class="text-danger d-block">Missing</small>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Address</dt>
                        <dd class="col-sm-8">
                            {{ $user->address ?? '-' }}
                            @if(!$user->address)
                                <small class="text-danger d-block">Missing</small>
                            @endif
                        </dd>
                    </dl>

                    <div class="mt-4 d-flex justify-content-between">
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
                        <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Edit Profile
                        </a>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-lock"></i> Change Password</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Enter your current password and choose a new password.</p>

                    <form action="{{ route('profile.password.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                   id="current_password" name="current_password" autocomplete="current-password" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password" autocomplete="new-password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control"
                                   id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key me-2"></i>Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
