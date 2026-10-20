@extends('layouts.admin')

@section('title', 'Vehicle Management - SUCCEZZ Drivers Agency')

@section('styles')
<style>
    .vehicles-wrapper {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        min-height: calc(100vh - 80px);
        padding: 2rem 0;
    }
    
    .vehicles-header {
        background: white;
        padding: 2rem;
        border-radius: 20px;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .vehicles-header h2 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 800;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .vehicles-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .vehicle-stat {
        background: white;
        padding: 1.5rem;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        border-left: 4px solid;
    }
    
    .vehicle-stat.total { border-left-color: #10b981; }
    .vehicle-stat.approved { border-left-color: #06b6d4; }
    .vehicle-stat.pending { border-left-color: #f59e0b; }
    .vehicle-stat.tracked { border-left-color: #8b5cf6; }
    
    .vehicle-stat-value {
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
    }
    
    .vehicle-stat-label {
        font-size: 0.9rem;
        color: #6b7280;
        font-weight: 600;
    }
    
    .vehicles-filters {
        background: white;
        padding: 1.5rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
    }
    
    .vehicles-filters input,
    .vehicles-filters select {
        padding: 0.75rem 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        font-size: 0.9rem;
    }
    
    .vehicles-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .vehicle-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .vehicle-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
    }
    
    .vehicle-card-header {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
    
    .vehicle-model-section {
        flex: 1;
    }
    
    .vehicle-model {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    
    .vehicle-plate {
        font-size: 0.85rem;
        opacity: 0.9;
        font-family: monospace;
    }
    
    .vehicle-status-badge {
        display: inline-block;
        padding: 0.35rem 0.8rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
    }
    
    .vehicle-card-body {
        padding: 1.5rem;
    }
    
    .vehicle-info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.8rem;
        font-size: 0.9rem;
    }
    
    .vehicle-info-label {
        font-weight: 600;
        color: #6b7280;
    }
    
    .vehicle-info-value {
        color: #1f2937;
        font-weight: 500;
    }
    
    .vehicle-specs {
        background: #f9fafb;
        padding: 1rem;
        border-radius: 10px;
        margin: 1rem 0;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
        font-size: 0.85rem;
    }
    
    .vehicle-spec-item {
        border-left: 2px solid #10b981;
        padding-left: 0.75rem;
    }
    
    .vehicle-spec-label {
        color: #6b7280;
        font-weight: 600;
    }
    
    .vehicle-spec-value {
        color: #1f2937;
        font-weight: 500;
    }
    
    .vehicle-card-footer {
        padding: 1rem;
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .vehicle-action-btn {
        flex: 1;
        min-width: 80px;
        padding: 0.6rem 0.8rem;
        border: none;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
    }
    
    .vehicle-action-btn.primary {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    
    .vehicle-action-btn.success {
        background: #d1fae5;
        color: #065f46;
    }
    
    .vehicle-action-btn.warning {
        background: #fef3c7;
        color: #78350f;
    }
    
    .vehicle-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .empty-state {
        grid-column: 1 / -1;
        background: white;
        padding: 3rem;
        border-radius: 16px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }
    
    .empty-state-icon {
        font-size: 3.5rem;
        color: #d1d5db;
        margin-bottom: 1rem;
    }
    
    .pagination-wrapper {
        background: white;
        padding: 1.5rem;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }
</style>
@endsection

@section('content')
<div class="vehicles-wrapper">
    <div class="container-max">
        <!-- Header -->
        <div class="vehicles-header">
            <div>
                <h2><i class="fas fa-car"></i> Vehicle Management</h2>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-soft">
                    <i class="fas fa-arrow-left"></i> Dashboard
                </a>
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="vehicles-stats">
            <div class="vehicle-stat total">
                <div class="vehicle-stat-value">{{ $vehicles->count() }}</div>
                <div class="vehicle-stat-label">Total Vehicles</div>
            </div>
            <div class="vehicle-stat approved">
                <div class="vehicle-stat-value">{{ $vehicles->where('approval_status', 'approved')->count() ?? 0 }}</div>
                <div class="vehicle-stat-label">Approved</div>
            </div>
            <div class="vehicle-stat pending">
                <div class="vehicle-stat-value">{{ $vehicles->where('approval_status', 'pending')->count() ?? 0 }}</div>
                <div class="vehicle-stat-label">Pending Approval</div>
            </div>
            <div class="vehicle-stat tracked">
                <div class="vehicle-stat-value">{{ $vehicles->where('tracking_status', 'active')->count() ?? 0 }}</div>
                <div class="vehicle-stat-label">Being Tracked</div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="vehicles-filters">
            <input type="text" id="vehicleSearch" placeholder="Search vehicles..." style="flex: 1; min-width: 200px;">
            <select id="typeFilter" style="min-width: 130px;">
                <option value="">All Types</option>
                <option value="sedan">Sedan</option>
                <option value="suv">SUV</option>
                <option value="van">Van</option>
                <option value="truck">Truck</option>
            </select>
            <select id="statusFilter" style="min-width: 130px;">
                <option value="">All Status</option>
                <option value="available">Available</option>
                <option value="unavailable">Unavailable</option>
                <option value="maintenance">Maintenance</option>
            </select>
            <button onclick="clearFilters()" class="btn btn-outline-secondary">
                <i class="fas fa-times"></i> Clear
            </button>
        </div>
        
        <!-- Vehicles Grid -->
        <div class="vehicles-grid">
            @forelse($vehicles as $vehicle)
            <div class="vehicle-card">
                <div class="vehicle-card-header">
                    <div class="vehicle-model-section">
                        <div class="vehicle-model">{{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}</div>
                        <div class="vehicle-plate">{{ $vehicle->license_plate }}</div>
                    </div>
                    <span class="vehicle-status-badge">
                        @if($vehicle->status === 'available')
                            <i class="fas fa-check-circle"></i> Available
                        @elseif($vehicle->status === 'maintenance')
                            <i class="fas fa-wrench"></i> Maintenance
                        @else
                            <i class="fas fa-pause-circle"></i> Unavailable
                        @endif
                    </span>
                    @if($vehicle->pending_application_changes)
                        <span class="badge bg-warning text-dark">Changes awaiting review</span>
                    @endif
                </div>
                
                <div class="vehicle-card-body">
                    <div class="vehicle-info-row">
                        <span class="vehicle-info-label">Owner:</span>
                        <span class="vehicle-info-value">{{ $vehicle->user?->name ?? 'Unknown' }}</span>
                    </div>
                    <div class="vehicle-info-row">
                        <span class="vehicle-info-label">Type:</span>
                        <span class="vehicle-info-value">{{ ucfirst($vehicle->vehicle_type) }}</span>
                    </div>
                    <div class="vehicle-info-row">
                        <span class="vehicle-info-label">Capacity:</span>
                        <span class="vehicle-info-value">{{ $vehicle->capacity }} seats</span>
                    </div>
                    
                    <div class="vehicle-specs">
                        <div class="vehicle-spec-item">
                            <div class="vehicle-spec-label">Color</div>
                            <div class="vehicle-spec-value">{{ $vehicle->color ?? 'N/A' }}</div>
                        </div>
                        <div class="vehicle-spec-item">
                            <div class="vehicle-spec-label">Mileage</div>
                            <div class="vehicle-spec-value">{{ $vehicle->mileage ?? 'N/A' }} km</div>
                        </div>
                        <div class="vehicle-spec-item">
                            <div class="vehicle-spec-label">Transmission</div>
                            <div class="vehicle-spec-value">{{ $vehicle->transmission ?? 'N/A' }}</div>
                        </div>
                        <div class="vehicle-spec-item">
                            <div class="vehicle-spec-label">Tracking</div>
                            <div class="vehicle-spec-value">{{ ucfirst($vehicle->tracking_status ?? 'pending') }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="vehicle-card-footer">
                    @if($vehicle->pending_application_changes)
                    <a href="{{ route('admin.vehicles.detail', $vehicle) }}" class="vehicle-action-btn warning">
                        <i class="fas fa-clipboard-check"></i> Review changes
                    </a>
                    @endif
                    <button onclick="viewVehicleDetails({{ $vehicle->id }})" class="vehicle-action-btn primary">
                        <i class="fas fa-eye"></i> View
                    </button>
                    <button onclick="editVehicle({{ $vehicle->id }})" class="vehicle-action-btn warning">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    @if($vehicle->approval_status !== 'approved')
                    <form action="{{ route('admin.vehicles.approve', $vehicle) }}" method="POST" style="flex: 1; min-width: 80px;" onsubmit="return confirm('Approve this vehicle?')">
                        @csrf
                        <button type="submit" class="vehicle-action-btn success" style="width: 100%;">
                            <i class="fas fa-check"></i> Approve
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-car"></i>
                </div>
                <h3 style="color: #1f2937;">No vehicles found</h3>
                <p style="color: #9ca3af;">Vehicle profiles will appear here once users add vehicles.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function viewVehicleDetails(id) {
        window.location.href = '/admin/vehicles/' + id;
    }
    
    function editVehicle(id) {
        window.location.href = '/admin/vehicles/' + id + '/edit-admin';
    }
    
    function clearFilters() {
        document.getElementById('vehicleSearch').value = '';
        document.getElementById('typeFilter').value = '';
        document.getElementById('statusFilter').value = '';
    }
</script>
@endsection
