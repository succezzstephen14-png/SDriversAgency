@extends('layouts.app')

@section('title', 'My Vehicles')

@section('content')
<div class="container-max content-section">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>My Approved Vehicles</h2>
            <p class="text-muted">View the vehicles you added that have been approved by an admin.</p>
        </div>
        <a href="{{ route('vehicles.create') }}" class="btn btn-primary">Add New Vehicle</a>
    </div>

    <div class="row gy-4">
        @forelse($vehicles as $vehicle)
            <div class="col-md-6">
                <div class="card p-3">
                    @if($vehicle->imagePaths())
                        <div class="vehicle-image mb-3">
                            <img src="{{ route('vehicles.images.show', ['vehicle' => $vehicle, 'index' => 0]) }}" alt="{{ $vehicle->make }} {{ $vehicle->model }}" class="vehicle-list-thumbnail">
                        </div>
                    @endif
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5>{{ $vehicle->make }} {{ $vehicle->model }}</h5>
                        <div class="d-flex gap-2">
                            <span class="badge bg-{{ $vehicle->status === 'available' ? 'success' : ($vehicle->status === 'maintenance' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($vehicle->status) }}
                            </span>
                            <span class="badge bg-{{ $vehicle->approval_status === 'approved' ? 'success' : ($vehicle->approval_status === 'rejected' ? 'danger' : 'warning') }}">
                                {{ ucfirst($vehicle->approval_status) }}
                            </span>
                        </div>
                    </div>
                    <p class="mb-1">{{ $vehicle->year }} • {{ ucfirst($vehicle->vehicle_type) }}</p>
                    <p class="mb-1">Application ID: <code>{{ $vehicle->application_id }}</code></p>
                    <p class="mb-1">License: {{ $vehicle->license_plate }}</p>
                    @if($vehicle->inspection_appointment_date)
                        <div class="alert alert-primary mt-2 mb-2">
                            <i class="fas fa-calendar-check me-1"></i>
                            <strong>Inspection scheduled:</strong> {{ $vehicle->inspection_appointment_date->format('M j, Y') }}
                        </div>
                    @endif
                    
                    @if($vehicle->approval_status === 'pending')
                        <div class="alert alert-info alert-sm mt-2 mb-2">
                            <i class="fas fa-info-circle"></i> Pending admin approval
                        </div>
                    @elseif($vehicle->approval_status === 'rejected')
                        <div class="alert alert-danger alert-sm mt-2 mb-2">
                            <i class="fas fa-times-circle"></i> 
                            <strong>Rejected:</strong> {{ $vehicle->rejection_reason ?? 'No reason provided' }}
                        </div>
                    @elseif($vehicle->approval_status === 'approved')
                        <div class="alert alert-success alert-sm mt-2 mb-2">
                            <i class="fas fa-check-circle"></i> Approved and live on the platform
                        </div>
                    @endif
                    
                    <div class="mt-2">
                        <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-outline-primary btn-sm">View</a>
                        <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                        <form action="{{ route('vehicles.destroy', $vehicle) }}" method="POST" class="d-inline-block">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">You have not added any vehicles yet.</div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $vehicles->links() }}
    </div>
</div>
@endsection
