@extends('layouts.service')

@section('title', 'My Requests')
@section('heading', 'My service requests')

@section('content')
<div class="d-flex flex-wrap gap-2 mb-3">
    <a class="btn btn-primary" href="{{ route('service.requests.create', 'driver') }}">Request a driver</a>
    <a class="btn btn-outline-primary" href="{{ route('service.requests.create', 'vehicle') }}">Request a vehicle</a>
</div>
@forelse($requests as $serviceRequest)
    <div class="card service-card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between flex-wrap gap-2">
                <h2 class="h5 mb-1">Request #{{ $serviceRequest->id }} · {{ ucfirst($serviceRequest->request_type) }}</h2>
                <span class="badge text-bg-{{ $serviceRequest->status === 'rejected' ? 'danger' : ($serviceRequest->status === 'paid' ? 'success' : 'primary') }}">{{ str_replace('_', ' ', ucfirst($serviceRequest->status)) }}</span>
            </div>
            <p class="text-muted mb-2">{{ $serviceRequest->service_label }} · {{ $serviceRequest->location }}
                @if($serviceRequest->vehicle_type) · {{ ucfirst($serviceRequest->vehicle_type) }}@endif
            </p>
            @if($serviceRequest->preferred_start_date)<p class="mb-1"><strong>Preferred date:</strong> {{ $serviceRequest->preferred_start_date->format('M j, Y') }}</p>@endif
            @if($serviceRequest->duration)<p class="mb-1"><strong>Duration:</strong> {{ $serviceRequest->duration }}</p>@endif
            @if($serviceRequest->details)<p class="mb-1"><strong>Details:</strong> {{ $serviceRequest->details }}</p>@endif
            @if($serviceRequest->assignedDriver)
                <p class="mb-1"><strong>Assigned driver:</strong> {{ $serviceRequest->assignedDriver->user->name }}</p>
            @endif
            @if($serviceRequest->assignedVehicle)
                <p class="mb-1"><strong>Assigned vehicle:</strong> {{ $serviceRequest->assignedVehicle->make }} {{ $serviceRequest->assignedVehicle->model }}</p>
            @endif
            @if($serviceRequest->quoted_amount)
                <p class="mb-1"><strong>Quote:</strong> GHS {{ number_format($serviceRequest->quoted_amount / 100, 2) }}</p>
            @endif
            @if($serviceRequest->agreement_end_date)
                <p class="mb-1"><strong>Agreed service end date:</strong> {{ $serviceRequest->agreement_end_date->format('M j, Y') }}</p>
                <p class="mb-1 small text-muted">You and the agency will be reminded {{ $serviceRequest->expiry_reminder_days }} days before expiry.</p>
            @endif
            @if($serviceRequest->admin_message)<div class="alert alert-info mt-3 mb-0">{{ $serviceRequest->admin_message }}</div>@endif
            @if($serviceRequest->status === 'awaiting_payment')
                <form method="POST" action="{{ route('service.requests.pay', $serviceRequest) }}" class="mt-3">
                    @csrf
                    <button type="submit" class="btn btn-success">Pay approved quote</button>
                </form>
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
    <div class="card service-card"><div class="card-body text-muted">No requests yet. Use one of the buttons above to get started.</div></div>
@endforelse
{{ $requests->links() }}
@endsection
