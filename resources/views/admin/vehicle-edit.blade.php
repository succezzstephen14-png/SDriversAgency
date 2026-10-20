@extends('layouts.admin')

@section('title', 'Edit Vehicle - SUCCEZZ Drivers Agency')

@section('page-title', 'Edit Vehicle')
@section('page-description', 'Modify vehicle information and approval status')

@section('styles')
<style>
    .admin-wrapper {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        min-height: calc(100vh - 80px);
        padding: 2rem 0;
        display: flex;
    }

    .admin-sidebar {
        width: 260px;
        background: white;
        padding: 2rem 1rem;
        border-radius: 24px 0 0 24px;
        box-shadow: 0 24px 80px rgba(15, 23, 42, 0.08);
        margin-right: 1rem;
    }

    .admin-sidebar h5 {
        margin-bottom: 1.5rem;
        font-weight: 700;
        color: #1f2937;
    }

    .admin-sidebar ul {
        list-style: none;
        padding: 0;
    }

    .admin-sidebar li {
        margin-bottom: 0.5rem;
    }

    .admin-sidebar a {
        display: block;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        text-decoration: none;
        color: #4b5563;
        transition: all 0.2s ease;
        font-weight: 500;
        font-size: 0.95rem;
    }

    .admin-sidebar a:hover, .admin-sidebar a.active {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .admin-main-content {
        flex: 1;
        background: white;
        border-radius: 24px;
        box-shadow: 0 24px 80px rgba(15, 23, 42, 0.08);
        padding: 2rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .admin-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .admin-header h2 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 800;
        background: linear-gradient(135deg, #ec4899 0%, #f43f5e 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .vehicle-info-panel {
        background: #f8fafc;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .vehicle-info-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .vehicle-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }

    .vehicle-info-item {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .vehicle-info-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .vehicle-info-value {
        font-size: 1rem;
        font-weight: 500;
        color: #1f2937;
    }

    .panel {
        background: white;
        border-radius: 24px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 24px 80px rgba(15, 23, 42, 0.06);
        padding: 1.75rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 1rem;
        box-sizing: border-box;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #ec4899;
        box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.1);
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        justify-content: flex-end;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        text-decoration: none;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: all 0.2s ease;
    }

    .btn-primary {
        background: linear-gradient(135deg, #ec4899 0%, #f43f5e 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(236, 72, 153, 0.3);
    }

    .btn-secondary {
        background: #e5e7eb;
        color: #1f2937;
    }

    .btn-secondary:hover {
        background: #d1d5db;
    }

    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
    }

    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    @media (max-width: 1280px) {
        .admin-wrapper {
            flex-direction: column;
        }

        .admin-sidebar {
            border-radius: 24px;
            margin-right: 0;
            margin-bottom: 1rem;
        }

        .admin-main-content {
            border-radius: 24px;
        }
    }
</style>
@endsection

@section('content')
<div class="admin-wrapper">
    <section class="admin-main-content">
        <div class="admin-header">
            <div>
                <p style="color: #475569; margin: 0 0 0.5rem 0;">Vehicle management</p>
                <h2><i class="fas fa-edit"></i> Edit Vehicle</h2>
            </div>
            <a href="{{ route('admin.vehicles') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Vehicles
            </a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Validation errors:</strong>
                <ul style="margin: 0.5rem 0 0 1.5rem;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <!-- Vehicle Information -->
        <div class="vehicle-info-panel">
            <div class="vehicle-info-title">
                <i class="fas fa-car"></i>
                Vehicle Details
            </div>
            <div class="vehicle-info-grid">
                <div class="vehicle-info-item">
                    <div class="vehicle-info-label">Owner</div>
                    <div class="vehicle-info-value">{{ $vehicle->user?->name ?? 'Unknown' }}</div>
                </div>
                <div class="vehicle-info-item">
                    <div class="vehicle-info-label">Make & Model</div>
                    <div class="vehicle-info-value">{{ $vehicle->make }} {{ $vehicle->model }}</div>
                </div>
                <div class="vehicle-info-item">
                    <div class="vehicle-info-label">Year</div>
                    <div class="vehicle-info-value">{{ $vehicle->year }}</div>
                </div>
                <div class="vehicle-info-item">
                    <div class="vehicle-info-label">License Plate</div>
                    <div class="vehicle-info-value">{{ $vehicle->license_plate }}</div>
                </div>
                <div class="vehicle-info-item">
                    <div class="vehicle-info-label">Vehicle Type</div>
                    <div class="vehicle-info-value">{{ ucfirst($vehicle->vehicle_type ?? 'N/A') }}</div>
                </div>
                <div class="vehicle-info-item">
                    <div class="vehicle-info-label">Color</div>
                    <div class="vehicle-info-value">{{ $vehicle->color ?? 'N/A' }}</div>
                </div>
                <div class="vehicle-info-item">
                    <div class="vehicle-info-label">Capacity</div>
                    <div class="vehicle-info-value">{{ $vehicle->capacity ?? 'N/A' }} passengers</div>
                </div>
                <div class="vehicle-info-item">
                    <div class="vehicle-info-label">Mileage</div>
                    <div class="vehicle-info-value">{{ $vehicle->mileage ? number_format($vehicle->mileage) . ' km' : 'N/A' }}</div>
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="panel">
            <form action="{{ route('admin.vehicles.update-admin', $vehicle) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="make">Make</label>
                    <input type="text" id="make" name="make" value="{{ old('make', $vehicle->make) }}" required>
                </div>

                <div class="form-group">
                    <label for="model">Model</label>
                    <input type="text" id="model" name="model" value="{{ old('model', $vehicle->model) }}" required>
                </div>

                <div class="form-group">
                    <label for="year">Year</label>
                    <input type="number" id="year" name="year" value="{{ old('year', $vehicle->year) }}" min="1900" max="{{ date('Y') }}" required>
                </div>

                <div class="form-group">
                    <label for="vehicle_type">Vehicle Type</label>
                    <select id="vehicle_type" name="vehicle_type" required>
                        <option value="car" {{ old('vehicle_type', $vehicle->vehicle_type) === 'car' ? 'selected' : '' }}>Car</option>
                        <option value="van" {{ old('vehicle_type', $vehicle->vehicle_type) === 'van' ? 'selected' : '' }}>Van</option>
                        <option value="truck" {{ old('vehicle_type', $vehicle->vehicle_type) === 'truck' ? 'selected' : '' }}>Truck</option>
                        <option value="motorcycle" {{ old('vehicle_type', $vehicle->vehicle_type) === 'motorcycle' ? 'selected' : '' }}>Motorcycle</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="license_plate">License Plate</label>
                    <input type="text" id="license_plate" name="license_plate" value="{{ old('license_plate', $vehicle->license_plate) }}" placeholder="e.g., 2301-GR1 or AS 2567-12" maxlength="20" required>
                    <small>Use a Ghana plate format such as 2301-GR1 or AS 2567-12.</small>
                </div>

                <div class="form-group">
                    <label for="status">Vehicle Status</label>
                    <select id="status" name="status" required>
                        <option value="available" {{ old('status', $vehicle->status) === 'available' ? 'selected' : '' }}>Available</option>
                        <option value="unavailable" {{ old('status', $vehicle->status) === 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                        <option value="maintenance" {{ old('status', $vehicle->status) === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="on_duty" {{ old('status', $vehicle->status) === 'on_duty' ? 'selected' : '' }}>On Duty</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="approval_status">Approval Status</label>
                    <select id="approval_status" name="approval_status" required>
                        <option value="pending" {{ old('approval_status', $vehicle->approval_status) === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ old('approval_status', $vehicle->approval_status) === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ old('approval_status', $vehicle->approval_status) === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="capacity">Capacity (Passengers)</label>
                    <input type="number" id="capacity" name="capacity" value="{{ old('capacity', $vehicle->capacity) }}" min="1" max="100">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="{{ route('admin.vehicles') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection