@extends('layouts.service')

@section('title', 'Dashboard')
@section('heading', 'Your service dashboard')

@section('content')
<div class="row g-3 mb-4">
    @foreach (['pending' => 'Pending review', 'awaiting_payment' => 'Awaiting payment', 'paid' => 'Paid', 'rejected' => 'Not approved'] as $status => $label)
        <div class="col-6 col-xl-3">
            <div class="card service-card h-100"><div class="card-body">
                <div class="text-muted small">{{ $label }}</div>
                <div class="fs-3 fw-bold">{{ $counts[$status] ?? 0 }}</div>
            </div></div>
        </div>
    @endforeach
</div>
<div class="card service-card mb-4">
    <div class="card-header bg-white"><strong>Updates</strong></div>
    <div class="card-body">
        @forelse($notifications as $notification)
            <div class="border-bottom py-2">
                <strong>{{ $notification->title }}</strong>
                <div class="text-muted small">{{ $notification->message }}</div>
            </div>
        @empty
            <p class="text-muted mb-0">No updates yet. Admin decisions and payment confirmations will appear here.</p>
        @endforelse
    </div>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-6"><a class="btn btn-primary w-100 py-3" href="{{ route('service.requests.create', 'driver') }}"><i class="fas fa-id-card me-2"></i>Request a driver</a></div>
    <div class="col-md-6"><a class="btn btn-outline-primary w-100 py-3" href="{{ route('service.requests.create', 'vehicle') }}"><i class="fas fa-car me-2"></i>Request a vehicle</a></div>
</div>
<div class="card service-card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Recent requests</strong><a href="{{ route('service.requests.index') }}">View all</a>
    </div>
    <div class="card-body">
        @forelse($requests as $serviceRequest)
            <div class="border-bottom py-3 d-flex flex-wrap justify-content-between gap-2">
                <div>
                    <strong>#{{ $serviceRequest->id }} · {{ ucfirst($serviceRequest->request_type) }}</strong>
                    <div class="text-muted">{{ $serviceRequest->service_label }} — {{ $serviceRequest->location }}</div>
                    @if($serviceRequest->admin_message)<div class="small mt-1">{{ $serviceRequest->admin_message }}</div>@endif
                </div>
                <span class="badge text-bg-{{ $serviceRequest->status === 'rejected' ? 'danger' : ($serviceRequest->status === 'paid' ? 'success' : 'primary') }} align-self-start">{{ str_replace('_', ' ', ucfirst($serviceRequest->status)) }}</span>
            </div>
        @empty
            <p class="text-muted mb-0">You have not submitted a request yet.</p>
        @endforelse
    </div>
</div>
@endsection
