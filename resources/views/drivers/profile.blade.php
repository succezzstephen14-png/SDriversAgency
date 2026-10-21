@extends('layouts.app')

@section('title', 'My Driver Profile')

@section('content')
<div class="container-max content-section">
    <div class="row justify-content-center">
        <div class="col-md-10">
            @if($driver)
                <!-- Header Card -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-3">
                                @if($driver->getProfilePictureUrl())
                                    <img
                                        src="{{ $driver->getProfilePictureUrl() }}"
                                        alt="{{ $driver->user?->name ?? 'Driver' }} profile picture"
                                        class="rounded-circle border border-3 border-white"
                                        style="width:88px;height:88px;object-fit:cover;"
                                    >
                                @else
                                    <div
                                        class="rounded-circle border border-3 border-white d-flex align-items-center justify-content-center"
                                        role="img"
                                        aria-label="No profile picture uploaded"
                                        style="width:88px;height:88px;background:rgba(255,255,255,.2);font-size:2rem;font-weight:700;"
                                    >{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($driver->user?->name ?? 'D', 0, 1)) }}</div>
                                @endif
                                <div>
                                    <h3 class="mb-1">{{ $driver->user?->name ?? 'Driver Profile' }}</h3>
                                    <p class="mb-0 small">License: {{ $driver->license_number }} • Class {{ $driver->license_class }}</p>
                                </div>
                            </div>
                            <div class="text-end">
                                @if($driver->approval_status === 'pending')
                                    <span class="badge bg-warning text-dark fs-6">Waiting for approval</span>
                                @elseif($driver->approval_status === 'rejected')
                                    <span class="badge bg-danger fs-6">Rejected</span>
                                @elseif($driver->status === 'available')
                                    <span class="badge bg-success fs-6">Active</span>
                                @elseif($driver->status === 'unavailable')
                                    <span class="badge bg-warning fs-6">Unavailable</span>
                                @else
                                    <span class="badge bg-secondary fs-6">{{ ucfirst($driver->status) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @if($driver->approval_status === 'pending')
                    <div class="alert alert-info mb-4">
                        Your payment is confirmed. Your driver profile is waiting for admin approval and will be active after it is approved.
                    </div>
                @elseif($driver->approval_status === 'rejected')
                    <div class="alert alert-danger mb-4">
                        <strong>Your driver profile was rejected.</strong>
                        {{ $driver->rejection_reason ?? 'Please contact support for more information.' }}
                    </div>
                @endif

                <!-- Personal Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user-circle"></i> Personal Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <dl class="row">
                                    <dt class="col-sm-5">Full Name</dt>
                                    <dd class="col-sm-7">{{ $driver->user?->name ?? '-' }}</dd>

                                    <dt class="col-sm-5">Email</dt>
                                    <dd class="col-sm-7">{{ $driver->user?->email ?? '-' }}</dd>

                                    <dt class="col-sm-5">Phone</dt>
                                    <dd class="col-sm-7">{{ $driver->user?->phone ?? '-' }}</dd>
                                </dl>
                            </div>
                            <div class="col-md-6 mb-3">
                                <dl class="row">
                                    <dt class="col-sm-5">Address</dt>
                                    <dd class="col-sm-7">{{ $driver->user?->address ?? '-' }}</dd>

                                    <dt class="col-sm-5">Member Since</dt>
                                    <dd class="col-sm-7">{{ $driver->created_at?->format('F j, Y') }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- License Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-id-card"></i> License Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <dl class="row">
                                    <dt class="col-sm-5">License No.</dt>
                                    <dd class="col-sm-7">
                                        <code>{{ $driver->license_number }}</code>
                                    </dd>

                                    <dt class="col-sm-5">Class</dt>
                                    <dd class="col-sm-7">
                                        <span class="badge bg-info">{{ $driver->license_class }}</span>
                                    </dd>
                                </dl>
                            </div>
                            <div class="col-md-6 mb-3">
                                <dl class="row">
                                    <dt class="col-sm-5">Expiry Date</dt>
                                    <dd class="col-sm-7">
                                        @if($driver->license_expiry)
                                            @if($driver->isLicenseExpired())
                                                <span class="text-danger"><strong>{{ $driver->license_expiry->format('F j, Y') }}</strong> (Expired)</span>
                                            @else
                                                {{ $driver->license_expiry->format('F j, Y') }}
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Experience & Performance -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-line"></i> Experience & Performance</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <dl class="row">
                                    <dt class="col-sm-6">Experience</dt>
                                    <dd class="col-sm-6">{{ $driver->experience_years ?? '-' }}</dd>
                                </dl>
                            </div>
                            <div class="col-md-4 mb-3">
                                <dl class="row">
                                    <dt class="col-sm-6">Rating</dt>
                                    <dd class="col-sm-6">
                                        <span class="text-warning">
                                            @php
                                                $rating = $driver->rating ?? 0;
                                                for($i = 0; $i < 5; $i++) {
                                                    echo $i < $rating ? '★' : '☆';
                                                }
                                            @endphp
                                        </span>
                                        <strong>{{ number_format($rating, 1) }}/5</strong>
                                    </dd>
                                </dl>
                            </div>
                            <div class="col-md-4 mb-3">
                                <dl class="row">
                                    <dt class="col-sm-6">Total Trips</dt>
                                    <dd class="col-sm-6"><strong>{{ $driver->total_trips ?? 0 }}</strong></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-file-alt"></i> Profile Details</h5>
                    </div>
                    <div class="card-body">
                        @if($driver->getProfilePictureUrl())
                            <div class="mb-3">
                                <label class="form-label">Your Profile Picture</label>
                                <div>
                                    <img src="{{ $driver->getProfilePictureUrl() }}" alt="{{ $driver->user?->name ?? 'Driver' }} profile picture" class="img-fluid rounded" style="width: 200px;height:200px;object-fit:cover;">
                                </div>
                            </div>
                        @endif

                        @if($driver->bio)
                            <div class="mb-3">
                                <label class="form-label">Bio</label>
                                <p>{{ $driver->bio }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="card mb-4 border-0">
                    <div class="card-body">
                        <div class="d-flex gap-2 justify-content-between">
                            <div>
                                <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                                </a>
                            </div>
                            <div>
                                <a href="{{ route('drivers.edit') }}" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Edit Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-user-tie fa-4x text-muted"></i>
                        </div>
                        <h5>No Driver Profile Found</h5>
                        <p class="text-muted">You do not have a driver profile yet. Create one to get started.</p>
                        <a href="{{ route('drivers.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Driver Profile
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
