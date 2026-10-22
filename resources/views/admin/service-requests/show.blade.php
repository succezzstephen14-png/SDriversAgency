@extends('layouts.admin')

@section('title', 'Review Service Request')

@section('content')
<div class="admin-wrapper">
    <div class="container-fluid">
        <div class="admin-header">
            <div><h2>Request #{{ $serviceRequest->id }}</h2><p class="text-muted mb-0">{{ $serviceRequest->user->name }} · {{ $serviceRequest->user->email }}</p></div>
            <a href="{{ route('admin.service-requests.index') }}" class="btn btn-outline-secondary">All requests</a>
        </div>
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm"><div class="card-body">
                    <h3 class="h5">Request details</h3>
                    <p><strong>Request:</strong> {{ ucfirst($serviceRequest->request_type) }} · {{ $serviceRequest->service_label }}</p>
                    @if($serviceRequest->vehicle_type)<p><strong>Requested vehicle:</strong> {{ ucfirst($serviceRequest->vehicle_type) }}</p>@endif
                    <p><strong>Location:</strong> {{ $serviceRequest->location }}</p>
                    <p><strong>Preferred date:</strong> {{ $serviceRequest->preferred_start_date?->format('M j, Y') ?? 'Not specified' }}</p>
                    <p><strong>Duration:</strong> {{ $serviceRequest->duration ?: 'Not specified' }}</p>
                    <p><strong>Additional details:</strong><br>{{ $serviceRequest->details ?: 'None' }}</p>
                    <p><strong>Status:</strong> {{ str_replace('_', ' ', ucfirst($serviceRequest->status)) }}</p>
                    @if($serviceRequest->assignedDriver)<p><strong>Assigned driver:</strong> {{ $serviceRequest->assignedDriver->user->name }}</p>@endif
                    @if($serviceRequest->assignedVehicle)<p><strong>Assigned vehicle:</strong> {{ $serviceRequest->assignedVehicle->make }} {{ $serviceRequest->assignedVehicle->model }}</p>@endif
                    @if($serviceRequest->quoted_amount)<p><strong>Quote:</strong> GHS {{ number_format($serviceRequest->quoted_amount / 100, 2) }}</p>@endif
                    @if($serviceRequest->admin_message)<div class="alert alert-info">{{ $serviceRequest->admin_message }}</div>@endif
                    @if($serviceRequest->agreement_end_date)
                        <p><strong>Agreement ends:</strong> {{ $serviceRequest->agreement_end_date->format('M j, Y') }}</p>
                        <p><strong>Expiry reminder:</strong> {{ $serviceRequest->expiry_reminder_days }} days before expiry</p>
                        @if($serviceRequest->expiry_reminder_sent_at)
                            <p class="small text-success mb-0">Reminder sent {{ $serviceRequest->expiry_reminder_sent_at->format('M j, Y g:i A') }}</p>
                        @endif
                    @endif
                </div></div>
            </div>
            <div class="col-lg-7">
                @if($serviceRequest->status === 'pending')
                    <div class="card border-0 shadow-sm mb-4"><div class="card-body">
                        <h3 class="h5">Approve, assign and quote</h3>
                        <form method="POST" action="{{ route('admin.service-requests.review', $serviceRequest) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="decision" value="approve">
                            @if($serviceRequest->request_type === 'driver')
                                <div class="mb-3">
                                    <label class="form-label" for="assigned_driver_id">Approved available driver</label>
                                    <select class="form-select" id="assigned_driver_id" name="assigned_driver_id" required>
                                        <option value="">Select driver</option>
                                        @foreach($drivers as $driver)
                                            <option value="{{ $driver->id }}" @selected(old('assigned_driver_id') === $driver->id)>{{ $driver->user->name }} · {{ $driver->experience_years }} years experience</option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <div class="mb-3">
                                    <label class="form-label" for="assigned_vehicle_id">Approved available vehicle</label>
                                    <select class="form-select" id="assigned_vehicle_id" name="assigned_vehicle_id" required>
                                        <option value="">Select vehicle</option>
                                        @foreach($vehicles as $vehicle)
                                            <option value="{{ $vehicle->id }}" @selected((string) old('assigned_vehicle_id') === (string) $vehicle->id)>{{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }} · {{ ucfirst($vehicle->vehicle_type) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="mb-3"><label class="form-label" for="quoted_amount">Quote (GHS)</label><input class="form-control" type="number" id="quoted_amount" name="quoted_amount" value="{{ old('quoted_amount') }}" min="0.01" step="0.01" required></div>
                            <div class="mb-3"><label class="form-label" for="approve_message">Message to customer</label><textarea class="form-control" id="approve_message" name="admin_message" rows="3" required>{{ old('decision') === 'approve' ? old('admin_message') : '' }}</textarea></div>
                            <div class="mb-3">
                                <label class="form-label" for="agreement_end_date">Agreement end date (optional)</label>
                                <input class="form-control" type="date" id="agreement_end_date" name="agreement_end_date"
                                       min="{{ now()->toDateString() }}" value="{{ old('agreement_end_date') }}">
                                <div class="form-text">Set this from the agreed service term to schedule an expiry reminder.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="expiry_reminder_days">Notify admin and customer</label>
                                <select class="form-select" id="expiry_reminder_days" name="expiry_reminder_days">
                                    <option value="">Choose reminder timing</option>
                                    <option value="30" @selected(old('expiry_reminder_days', '30') === '30')>30 days before expiry</option>
                                    <option value="10" @selected(old('expiry_reminder_days') === '10')>10 days before expiry</option>
                                </select>
                            </div>
                            <button class="btn btn-success" type="submit" @disabled($serviceRequest->request_type === 'driver' ? $drivers->isEmpty() : $vehicles->isEmpty())>Approve and send quote</button>
                            @if(($serviceRequest->request_type === 'driver' ? $drivers : $vehicles)->isEmpty())
                                <p class="small text-danger mt-2 mb-0">There are no eligible approved, available resources to assign yet.</p>
                            @endif
                        </form>
                    </div></div>
                    <div class="card border-0 shadow-sm"><div class="card-body">
                        <h3 class="h5">Reject request</h3>
                        <form method="POST" action="{{ route('admin.service-requests.review', $serviceRequest) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="decision" value="reject">
                            <div class="mb-3"><label class="form-label" for="reject_message">Reason/message to customer</label><textarea class="form-control" id="reject_message" name="admin_message" rows="3" required>{{ old('decision') === 'reject' ? old('admin_message') : '' }}</textarea></div>
                            <button class="btn btn-outline-danger" type="submit">Reject request and notify customer</button>
                        </form>
                    </div></div>
                @else
                    <div class="card border-0 shadow-sm"><div class="card-body">
                        <h3 class="h5">Review complete</h3>
                        <p>This request is currently <strong>{{ str_replace('_', ' ', $serviceRequest->status) }}</strong>. Decisions cannot be changed here.</p>
                        @if(in_array($serviceRequest->status, ['awaiting_payment', 'paid', 'completed'], true))
                            <hr>
                            <h4 class="h6">Service agreement expiry reminder</h4>
                            <p class="small text-muted">Set the agreed end date and choose whether admins and the customer should be notified 30 or 10 days in advance.</p>
                            <form method="POST" action="{{ route('admin.service-requests.agreement.update', $serviceRequest) }}">
                                @csrf
                                @method('PUT')
                                <div class="mb-3">
                                    <label class="form-label" for="edit_agreement_end_date">Agreement end date</label>
                                    <input class="form-control" type="date" id="edit_agreement_end_date" name="agreement_end_date"
                                           min="{{ now()->toDateString() }}" value="{{ old('agreement_end_date', $serviceRequest->agreement_end_date?->toDateString()) }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="edit_expiry_reminder_days">Reminder timing</label>
                                    <select class="form-select" id="edit_expiry_reminder_days" name="expiry_reminder_days">
                                        <option value="">No expiry reminder</option>
                                        <option value="30" @selected((string) old('expiry_reminder_days', $serviceRequest->expiry_reminder_days) === '30')>30 days before expiry</option>
                                        <option value="10" @selected((string) old('expiry_reminder_days', $serviceRequest->expiry_reminder_days) === '10')>10 days before expiry</option>
                                    </select>
                                </div>
                                <button class="btn btn-outline-primary" type="submit">Save agreement</button>
                            </form>
                        @endif
                    </div></div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
