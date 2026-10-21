@extends('layouts.app')

@section('title', 'Pending Vehicles')

@section('content')
<style>
    .pending-vehicles-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .pending-vehicles-header > div {
        min-width: 0;
        flex: 1 1 16rem;
    }

    .pending-vehicle-card {
        height: 100%;
        overflow: hidden;
    }

    .pending-vehicle-image img {
        display: block;
        width: 100%;
        max-height: 240px;
        object-fit: cover;
        border-radius: 0.5rem;
    }

    .pending-vehicle-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .pending-vehicle-heading h5 {
        min-width: 0;
        overflow-wrap: anywhere;
    }

    .pending-vehicle-badges,
    .pending-vehicle-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
    }

    .pending-vehicle-actions > * {
        margin: 0;
    }

    @media (max-width: 575.98px) {
        .pending-vehicles-header {
            align-items: stretch;
        }

        .pending-vehicles-header .btn {
            width: 100%;
        }

        .pending-vehicle-card {
            padding: 1rem !important;
        }

        .pending-vehicle-actions > a,
        .pending-vehicle-actions > form,
        .pending-vehicle-actions > form button {
            flex: 1 1 auto;
        }

        .pending-vehicle-actions > a,
        .pending-vehicle-actions > form button {
            width: 100%;
            min-height: 44px;
        }
    }
</style>
<div class="container-max content-section">
    <div class="pending-vehicles-header mb-4">
        <div>
            <h2>Pending Vehicles</h2>
            <p class="text-muted">Review the vehicles you have submitted that are awaiting admin approval.</p>
        </div>
        <a href="{{ route('vehicles.create') }}" class="btn btn-primary">Add New Vehicle</a>
    </div>

    <div class="row gy-4">
        @forelse($vehicles as $vehicle)
            <div class="col-md-6">
                <div class="card pending-vehicle-card p-3">
                    @if($vehicle->imagePaths())
                        <div class="pending-vehicle-image mb-3">
                            <img src="{{ route('vehicles.images.show', ['vehicle' => $vehicle, 'index' => 0]) }}" alt="{{ $vehicle->make }} {{ $vehicle->model }}">
                        </div>
                    @endif
                    <div class="pending-vehicle-heading mb-2">
                        <h5>{{ $vehicle->make }} {{ $vehicle->model }}</h5>
                        <div class="pending-vehicle-badges">
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
                    @if($vehicle->approval_status === 'pending')
                        <div class="alert alert-info alert-sm mt-2 mb-2">
                            <i class="fas fa-info-circle"></i> Pending admin approval
                        </div>
                    @elseif($vehicle->approval_status === 'rejected')
                        <div class="alert alert-danger alert-sm mt-2 mb-2">
                            <i class="fas fa-times-circle"></i>
                            <strong>Rejected:</strong> {{ $vehicle->rejection_reason ?? 'No reason provided' }}
                        </div>
                    @endif
                    <div class="pending-vehicle-actions mt-2">
                        <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-outline-primary btn-sm">View</a>
                        <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                        <form action="{{ route('vehicles.destroy', $vehicle) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">You do not have any pending or rejected vehicles at the moment.</div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $vehicles->links() }}
    </div>
</div>
@endsection
