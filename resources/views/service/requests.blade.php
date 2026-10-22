@extends('layouts.service')

@section('title', 'My Requests')
@section('heading', 'My service requests')

@section('content')
<div class="card service-card mb-4 overflow-hidden">
    <div class="card-body p-4 p-lg-5" style="background: linear-gradient(120deg, #eff6ff, #ffffff);">
        <div class="row align-items-center g-4">
            <div class="col-lg">
                <span class="badge rounded-pill text-bg-primary mb-2">SERVICE REQUESTS</span>
                <h2 class="h3 fw-bold mb-2">Track your driver and vehicle requests</h2>
                <p class="text-muted mb-0">Follow each request from admin review to quote, payment and active service.</p>
            </div>
            <div class="col-lg-auto d-flex flex-wrap gap-2">
                <a class="btn btn-primary" href="{{ route('service.requests.create', 'driver') }}">
                    <i class="fas fa-id-card me-2"></i>Request a driver
                </a>
                <a class="btn btn-outline-primary" href="{{ route('service.requests.create', 'vehicle') }}">
                    <i class="fas fa-car me-2"></i>Request a vehicle
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    @foreach ([
        ['label' => 'All requests', 'value' => $requests->total(), 'icon' => 'fa-list-check', 'color' => 'primary'],
        ['label' => 'Driver requests', 'value' => $typeCounts['driver'] ?? 0, 'icon' => 'fa-id-card', 'color' => 'info'],
        ['label' => 'Vehicle requests', 'value' => $typeCounts['vehicle'] ?? 0, 'icon' => 'fa-car', 'color' => 'success'],
        ['label' => 'Awaiting review', 'value' => $statusCounts['pending'] ?? 0, 'icon' => 'fa-hourglass-half', 'color' => 'warning'],
    ] as $summary)
        <div class="col-6 col-xl-3">
            <div class="card service-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-{{ $summary['color'] }}-subtle text-{{ $summary['color'] }} d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px;height:48px;">
                        <i class="fas {{ $summary['icon'] }}"></i>
                    </div>
                    <div>
                        <div class="text-muted small">{{ $summary['label'] }}</div>
                        <div class="fs-4 fw-bold">{{ $summary['value'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@forelse($requests as $serviceRequest)
    @php
        $statusColor = match ($serviceRequest->status) {
            'rejected' => 'danger',
            'paid', 'completed' => 'success',
            'awaiting_payment' => 'warning',
            default => 'primary',
        };
    @endphp
    <div class="card service-card mb-3 overflow-hidden">
        <div class="card-header bg-white border-0 px-4 pt-4 pb-0">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-{{ $serviceRequest->request_type === 'driver' ? 'info' : 'success' }}-subtle text-{{ $serviceRequest->request_type === 'driver' ? 'info' : 'success' }} d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="fas {{ $serviceRequest->request_type === 'driver' ? 'fa-id-card' : 'fa-car' }}"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Request #{{ $serviceRequest->id }}</div>
                        <h2 class="h5 mb-0">{{ ucfirst($serviceRequest->request_type) }} request</h2>
                    </div>
                </div>
                <span class="badge rounded-pill text-bg-{{ $statusColor }} px-3 py-2">{{ str_replace('_', ' ', ucfirst($serviceRequest->status)) }}</span>
            </div>
        </div>
        <div class="card-body px-4 pt-3">
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="small text-muted">Service</div>
                    <div class="fw-semibold">{{ $serviceRequest->service_label }}{{ $serviceRequest->vehicle_type ? ' · ' . ucfirst($serviceRequest->vehicle_type) : '' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="small text-muted">Location</div>
                    <div class="fw-semibold">{{ $serviceRequest->location }}</div>
                </div>
                @if($serviceRequest->preferred_start_date)
                    <div class="col-md-6"><div class="small text-muted">Preferred start</div><div>{{ $serviceRequest->preferred_start_date->format('M j, Y') }}</div></div>
                @endif
                @if($serviceRequest->duration)
                    <div class="col-md-6"><div class="small text-muted">Duration</div><div>{{ $serviceRequest->duration }}</div></div>
                @endif
                @if($serviceRequest->assignedDriver)
                    <div class="col-md-6"><div class="small text-muted">Assigned driver</div><div class="fw-semibold">{{ $serviceRequest->assignedDriver->user?->name ?? 'Assigned' }}</div></div>
                @endif
                @if($serviceRequest->assignedVehicle)
                    <div class="col-md-6"><div class="small text-muted">Assigned vehicle</div><div class="fw-semibold">{{ $serviceRequest->assignedVehicle->make }} {{ $serviceRequest->assignedVehicle->model }}</div></div>
                @endif
            </div>
            @if($serviceRequest->details)
                <p class="mb-3"><span class="small text-muted d-block">Your details</span>{{ $serviceRequest->details }}</p>
            @endif
            @if($serviceRequest->quoted_amount)
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 rounded-3 bg-light p-3 mb-3">
                    <div><div class="small text-muted">Admin quote</div><strong class="fs-5">GHS {{ number_format($serviceRequest->quoted_amount / 100, 2) }}</strong></div>
                    @if($serviceRequest->status === 'awaiting_payment')
                        <form method="POST" action="{{ route('service.requests.pay', $serviceRequest) }}">
                            @csrf
                            <button type="submit" class="btn btn-success"><i class="fas fa-lock me-2"></i>Pay approved quote</button>
                        </form>
                    @endif
                </div>
            @endif
            @if($serviceRequest->agreement_end_date)
                <div class="alert alert-info py-2 mb-3">
                    <strong>Agreed service end date:</strong> {{ $serviceRequest->agreement_end_date->format('M j, Y') }}
                    <span class="d-block small">A reminder will be sent {{ $serviceRequest->expiry_reminder_days }} days before expiry.</span>
                </div>
            @endif
            @if($serviceRequest->admin_message)
                <div class="alert alert-primary mb-3"><strong>Update from the agency</strong><div>{{ $serviceRequest->admin_message }}</div></div>
            @endif
            @include('assignments._timeline', ['activities' => $serviceRequest->assignmentActivities, 'audience' => 'customer'])
            @if(in_array($serviceRequest->status, ['paid', 'completed'], true) && $serviceRequest->assignedDriver)
                @include('assignments._customer-report-form', [
                    'assignment' => $serviceRequest,
                    'assignmentType' => 'service-request',
                    'routeName' => 'service.assignments.activity',
                ])
            @endif
        </div>
    </div>
@empty
    <div class="card service-card">
        <div class="card-body p-5 text-center">
            <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width:64px;height:64px;">
                <i class="fas fa-clipboard-list fa-xl"></i>
            </div>
            <h2 class="h5">No requests yet</h2>
            <p class="text-muted">Choose a driver or vehicle service to get started.</p>
            <a class="btn btn-primary me-2" href="{{ route('service.requests.create', 'driver') }}">Request a driver</a>
            <a class="btn btn-outline-primary" href="{{ route('service.requests.create', 'vehicle') }}">Request a vehicle</a>
        </div>
    </div>
@endforelse
{{ $requests->links() }}
@endsection
