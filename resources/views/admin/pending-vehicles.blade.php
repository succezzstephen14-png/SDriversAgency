@extends('layouts.admin')

@section('title', 'Pending Vehicle Approvals')

@section('content')
<style>
    .admin-wrapper {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        min-height: calc(100vh - 80px);
        padding: 2rem 0;
    }
    .admin-header {
        background: white;
        padding: 1.5rem 2rem;
        border-radius: 24px;
        margin-bottom: 2rem;
        box-shadow: 0 12px 40px rgba(15, 23, 42, 0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .admin-header h2 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 800;
    }
    .vehicles-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    .vehicle-approval-card {
        background: white;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 12px 40px rgba(15, 23, 42, 0.08);
        border: 1px solid rgba(15, 23, 42, 0.06);
    }
    .vehicle-card-header {
        background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
        color: white;
        padding: 1.5rem;
    }
    .vehicle-card-header h5 {
        margin: 0 0 0.5rem;
        font-size: 1.1rem;
    }
    .pending-badge {
        display: inline-block;
        padding: 0.4rem 0.8rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 700;
        background: rgba(255, 255, 255, 0.25);
        color: white;
    }
    .vehicle-card-body {
        padding: 1.5rem;
    }
    .vehicle-info-row {
        display: grid;
        grid-template-columns: 140px 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
        font-size: 0.95rem;
    }
    .vehicle-info-row strong {
        color: #6b7280;
        font-weight: 600;
    }
    .vehicle-info-row .value {
        color: #111827;
        font-weight: 500;
    }
    .vehicle-docs {
        margin: 1.5rem 0;
        padding: 1rem;
        background: #f9fafb;
        border-radius: 12px;
    }
    .vehicle-docs h6 {
        margin-bottom: 0.75rem;
        font-size: 0.85rem;
        font-weight: 700;
        color: #6b7280;
    }
    .doc-links {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.5rem;
    }
    .doc-links a {
        padding: 0.5rem 0.75rem;
        background: white;
        border: 1.5px solid #e5e7eb;
        border-radius: 8px;
        font-size: 0.8rem;
        text-align: center;
        transition: all 0.2s ease;
        color: #10b981;
        text-decoration: none;
    }
    .doc-links a:hover {
        border-color: #d1d5db;
        background: #f3f4f6;
    }
    .vehicle-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
        margin-top: 1.5rem;
    }
    .approve-btn {
        padding: 0.75rem 1rem;
        background: #10b981;
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .approve-btn:hover {
        background: #059669;
    }
    .reject-btn {
        padding: 0.75rem 1rem;
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .reject-btn:hover {
        background: #dc2626;
    }
</style>

<div class="admin-wrapper">
    <div class="container-max">
        <div class="admin-header">
            <h2><i class="fas fa-check-circle"></i> Pending Vehicle Approvals</h2>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>

        @if($vehicles->count() === 0)
            <div style="background: white; padding: 3rem 2rem; border-radius: 18px; text-align: center; box-shadow: 0 12px 40px rgba(15, 23, 42, 0.08);">
                <i class="fas fa-check-circle" style="font-size: 3rem; color: #10b981; margin-bottom: 1rem; display: block;"></i>
                <p style="color: #10b981; font-weight: 600; margin: 0; font-size: 1.1rem;">All pending vehicles have been processed</p>
            </div>
        @else
            <div class="vehicles-grid">
                @foreach($vehicles as $vehicle)
                    <div class="vehicle-approval-card">
                        <div class="vehicle-card-header">
                            <h5><i class="fas fa-car-side"></i> {{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}</h5>
                            <span class="pending-badge">⏳ Pending Review</span>
                        </div>
                        <div class="vehicle-card-body">
                            <div class="vehicle-info-row">
                                <strong>Application ID:</strong>
                                <span class="value">{{ $vehicle->application_id }}</span>
                            </div>
                            <div class="vehicle-info-row">
                                <strong>Owner:</strong>
                                <span class="value">{{ $vehicle->user->name }}</span>
                            </div>
                            <div class="vehicle-info-row">
                                <strong>Plate:</strong>
                                <span class="value">{{ $vehicle->license_plate }}</span>
                            </div>
                            <div class="vehicle-info-row">
                                <strong>Type:</strong>
                                <span class="value">{{ ucfirst($vehicle->vehicle_type) }}</span>
                            </div>
                            <div class="vehicle-info-row">
                                <strong>Capacity:</strong>
                                <span class="value">{{ $vehicle->capacity }} seats</span>
                            </div>
                            <div class="vehicle-info-row">
                                <strong>Color:</strong>
                                <span class="value">{{ $vehicle->color ?? 'N/A' }}</span>
                            </div>
                            <div class="vehicle-info-row">
                                <strong>Vin:</strong>
                                <span class="value">{{ $vehicle->vin ?? 'N/A' }}</span>
                            </div>

                            <div class="vehicle-docs">
                                <h6>📎 Uploaded Documents</h6>
                                <div class="doc-links">
                                    @if($vehicle->registration_document)
                                        <a href="{{ route('admin.documents.show', ['type' => 'vehicle', 'id' => $vehicle->id, 'field' => 'registration_document']) }}" target="_blank">📋 Registration</a>
                                    @endif
                                    @if($vehicle->insurance_document)
                                        <a href="{{ route('admin.documents.show', ['type' => 'vehicle', 'id' => $vehicle->id, 'field' => 'insurance_document']) }}" target="_blank">🛡️ Insurance</a>
                                    @endif
                                    @if($vehicle->roadworthiness_certificate)
                                        <a href="{{ route('admin.documents.show', ['type' => 'vehicle', 'id' => $vehicle->id, 'field' => 'roadworthiness_certificate']) }}" target="_blank">🛣️ Roadworthy</a>
                                    @endif
                                </div>
                            </div>

                            <div style="margin: 1rem 0;">
                                <a href="{{ route('admin.vehicles.detail', $vehicle) }}" class="btn btn-outline-primary w-100">View full vehicle details</a>
                            </div>

                            <div class="vehicle-actions">
                                <form action="{{ route('admin.vehicles.approve', $vehicle) }}" method="POST" style="width: 100%;">
                                    @csrf
                                    <button type="submit" class="approve-btn w-100">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>
                                <button type="button" class="reject-btn" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $vehicle->id }}">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Reject Modal -->
                    <div class="modal fade" id="rejectModal{{ $vehicle->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Reject Vehicle</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('admin.vehicles.reject', $vehicle) }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                        <label for="rejection_reason" class="form-label">Reason for Rejection</label>
                                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" required></textarea>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Reject Vehicle</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $vehicles->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
