@extends('layouts.app')

@section('title', 'Booking Details')

@section('content')
<div class="container-max content-section">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-receipt"></i> Booking #{{ $booking->id }}</h4>
                        <span class="badge bg-{{ 
                            $booking->approval_status === 'approved' ? 'success' : 
                            ($booking->approval_status === 'rejected' ? 'danger' : 'warning')
                        }}">
                            {{ ucfirst($booking->approval_status) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">

                    <!-- Status Information -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="form-label text-muted">Booking Status</label>
                            <p><span class="badge bg-info">{{ ucfirst($booking->status) }}</span></p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted">Approval Status</label>
                            <p>
                                <span class="badge bg-{{ 
                                    $booking->approval_status === 'approved' ? 'success' : 
                                    ($booking->approval_status === 'rejected' ? 'danger' : 'warning')
                                }}">
                                    {{ ucfirst($booking->approval_status) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted">Payment Status</label>
                            <p><span class="badge bg-{{ $booking->payment_status === 'paid' ? 'success' : 'warning' }}">{{ ucfirst($booking->payment_status) }}</span></p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted">Booking Type</label>
                            <p><strong>{{ ucfirst($booking->booking_type) }}</strong></p>
                        </div>
                    </div>

                    @if($booking->guest_name || $booking->guest_email || $booking->guest_phone)
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Guest Contact Information</h6>
                            </div>
                            <div class="card-body">
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Name</dt>
                                    <dd class="col-sm-8">{{ $booking->guest_name ?? 'Guest' }}</dd>

                                    <dt class="col-sm-4">Email</dt>
                                    <dd class="col-sm-8">{{ $booking->guest_email ?? 'Not provided' }}</dd>

                                    <dt class="col-sm-4">Phone</dt>
                                    <dd class="col-sm-8">{{ $booking->guest_phone ?? 'Not provided' }}</dd>
                                </dl>
                            </div>
                        </div>
                    @endif

                    <!-- Booking Details -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Booking Information</h6>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-4">Booking Date</dt>
                                <dd class="col-sm-8">{{ $booking->booking_date?->format('F j, Y') }}</dd>

                                @if($booking->start_time)
                                    <dt class="col-sm-4">Start Time</dt>
                                    <dd class="col-sm-8">{{ $booking->start_time?->format('g:i A') }}</dd>

                                    <dt class="col-sm-4">End Time</dt>
                                    <dd class="col-sm-8">{{ $booking->end_time?->format('g:i A') }}</dd>
                                @endif

                                @if($booking->location)
                                    <dt class="col-sm-4">Location</dt>
                                    <dd class="col-sm-8">{{ $booking->location }}</dd>
                                @endif

                                @if($booking->description)
                                    <dt class="col-sm-4">Description</dt>
                                    <dd class="col-sm-8">{{ $booking->description }}</dd>
                                @endif

                                @if($booking->application_role)
                                    <dt class="col-sm-4">Application Role</dt>
                                    <dd class="col-sm-8">{{ str_replace('_', ' ', ucfirst($booking->application_role)) }}</dd>
                                @endif

                                @if(!empty($booking->application_documents))
                                    <dt class="col-sm-4">Uploaded Documents</dt>
                                    <dd class="col-sm-8">
                                        <ul class="list-unstyled mb-0">
                                            @foreach($booking->application_documents as $index => $document)
                                                <li>
                                                    <a href="{{ route('admin.documents.show', ['type' => 'booking', 'id' => $booking->id, 'field' => 'application_documents', 'index' => $index]) }}" target="_blank" rel="noopener noreferrer">{{ basename($document) }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </dd>
                                @endif
                            </dl>
                        </div>
                    </div>

                    <!-- Service Details -->
                    @if(auth()->user()->isAdmin() && $booking->driver)
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Assigned Driver</h6>
                            </div>
                            <div class="card-body">
                                <dl class="row">
                                    <dt class="col-sm-4">Driver Name</dt>
                                    <dd class="col-sm-8">{{ $booking->driver->user->name }}</dd>

                                    @if(auth()->user()->isAdmin())
                                        <dt class="col-sm-4">License</dt>
                                        <dd class="col-sm-8">{{ $booking->driver->license_number }}</dd>

                                        <dt class="col-sm-4">Experience</dt>
                                        <dd class="col-sm-8">{{ $booking->driver->experience_years }} years</dd>
                                    @endif

                                    <dt class="col-sm-4">Rating</dt>
                                    <dd class="col-sm-8">
                                        <span class="text-warning">
                                            @php
                                                $rating = $booking->driver->rating ?? 0;
                                                for($i = 0; $i < 5; $i++) {
                                                    echo $i < $rating ? '★' : '☆';
                                                }
                                            @endphp
                                        </span>
                                        {{ number_format($rating, 1) }}/5
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    @endif

                    @if($booking->vehicle)
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Vehicle Information</h6>
                            </div>
                            <div class="card-body">
                                <dl class="row">
                                    <dt class="col-sm-4">Vehicle</dt>
                                    <dd class="col-sm-8">{{ $booking->vehicle->year }} {{ $booking->vehicle->make }} {{ $booking->vehicle->model }}</dd>

                                    <dt class="col-sm-4">Type</dt>
                                    <dd class="col-sm-8">{{ ucfirst($booking->vehicle->vehicle_type) }}</dd>

                                    <dt class="col-sm-4">License Plate</dt>
                                    <dd class="col-sm-8"><code>{{ $booking->vehicle->license_plate }}</code></dd>

                                    <dt class="col-sm-4">Capacity</dt>
                                    <dd class="col-sm-8">{{ $booking->vehicle->capacity }} passengers</dd>
                                </dl>
                            </div>
                        </div>
                    @endif

                    @if($booking->recruitment)
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Opportunity Information</h6>
                            </div>
                            <div class="card-body">
                                <dl class="row">
                                    <dt class="col-sm-4">Title</dt>
                                    <dd class="col-sm-8">{{ $booking->recruitment->title }}</dd>

                                    <dt class="col-sm-4">Position Type</dt>
                                    <dd class="col-sm-8">{{ str_replace('_', ' ', ucfirst($booking->recruitment->position_type)) }}</dd>

                                    <dt class="col-sm-4">Location</dt>
                                    <dd class="col-sm-8">{{ $booking->recruitment->location }}</dd>
                                </dl>
                            </div>
                        </div>
                    @endif

                    <!-- Payment Information -->
                    @if($booking->amount > 0)
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Payment Information</h6>
                            </div>
                            <div class="card-body">
                                <dl class="row">
                                    <dt class="col-sm-4">Amount</dt>
                                    <dd class="col-sm-8"><strong>{{ config('app.currency_label', 'GHC') }} {{ number_format($booking->amount / 100, 2) }}</strong></dd>

                                    <dt class="col-sm-4">Payment Status</dt>
                                    <dd class="col-sm-8">
                                        <span class="badge bg-{{ $booking->payment_status === 'paid' ? 'success' : 'warning' }}">
                                            {{ ucfirst($booking->payment_status) }}
                                        </span>
                                    </dd>

                                    @if($booking->payment_reference)
                                        <dt class="col-sm-4">Payment Reference</dt>
                                        <dd class="col-sm-8"><code>{{ $booking->payment_reference }}</code></dd>
                                    @endif

                                    @if($booking->payment_date)
                                        <dt class="col-sm-4">Payment Date</dt>
                                        <dd class="col-sm-8">{{ $booking->payment_date?->format('F j, Y g:i A') }}</dd>
                                    @endif
                                </dl>
                            </div>
                        </div>
                    @endif

                    <!-- Rejection Reason -->
                    @if($booking->approval_status === 'rejected' && $booking->rejection_reason)
                        <div class="alert alert-danger">
                            <strong>Rejection Reason:</strong>
                            <p>{{ $booking->rejection_reason }}</p>
                        </div>
                    @endif

                    <!-- Approval Timestamp -->
                    @if($booking->approved_at)
                        <div class="alert alert-info">
                            <i class="fas fa-check-circle me-2"></i>
                            Approved on {{ $booking->approved_at->format('F j, Y g:i A') }}
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="mt-4">
                        <a href="{{ route('bookings.my-bookings') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to My Bookings
                        </a>

                        @if($booking->isPendingPayment() && Auth::id() === $booking->user_id)
                            <a href="#" class="btn btn-primary">
                                <i class="fas fa-credit-card"></i> Complete Payment
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('assignments._timeline', ['activities' => $booking->assignmentActivities, 'audience' => 'customer'])
@if((string) Auth::id() === (string) $booking->user_id
    && $booking->driver
    && $booking->approval_status === 'approved'
    && $booking->payment_status === 'paid'
    && $booking->status !== 'cancelled')
    @include('assignments._customer-report-form', [
        'assignment' => $booking,
        'assignmentType' => 'booking',
        'routeName' => 'assignments.activity',
    ])
@endif
@endsection
