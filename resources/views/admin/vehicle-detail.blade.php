@extends('layouts.admin')

@section('title', 'Vehicle Details')

@section('content')
<style>
    .review-wrapper {
        max-width: 1000px;
        margin: 2rem auto;
        padding: 1rem;
    }
    .review-card {
        background: white;
        border-radius: 24px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 24px 80px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }
    .review-header {
        background: #1d4ed8;
        color: white;
        padding: 2rem;
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .review-header h1 {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 800;
    }
    .status-pill {
        background: #f59e0b;
        color: white;
        padding: 0.65rem 1rem;
        border-radius: 999px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .review-body {
        padding: 2rem;
        display: grid;
        gap: 1.5rem;
    }
    .vehicle-grid {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 1.5rem;
    }
    .vehicle-summary {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        padding: 1.5rem;
        text-align: center;
    }
    .vehicle-summary img {
        width: 100%;
        height: 220px;
        object-fit: cover;
        border-radius: 16px;
        margin-bottom: 1rem;
    }
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
    }
    .detail-box {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 1.25rem;
    }
    .detail-label {
        font-size: 0.8rem;
        font-weight: 700;
        color: #6b7280;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .detail-value {
        color: #111827;
        font-weight: 600;
        line-height: 1.5;
    }
    .documents-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        gap: 0.75rem;
    }
    .documents-list li {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
    }
    .documents-list a {
        color: #1d4ed8;
        text-decoration: none;
        font-weight: 700;
    }
    .action-row {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .btn-action {
        border: none;
        border-radius: 14px;
        padding: 0.9rem 1.4rem;
        font-weight: 700;
        cursor: pointer;
    }
    .btn-approve {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    .btn-reject {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }
</style>

<div class="review-wrapper">
    <a href="{{ route('admin.pending-vehicles') }}" class="btn btn-outline-secondary" style="margin-bottom: 1rem;">← Back to pending vehicles</a>

    <div class="review-card">
        <div class="review-header">
            <div>
                <h1>{{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}</h1>
                <p style="opacity: .85; margin: .5rem 0 0;">Posted by {{ $vehicle->user->name }} — {{ $vehicle->user->email }}</p>
            </div>
            <div class="status-pill {{ $vehicle->approval_status }}">{{ ucfirst($vehicle->approval_status) }}</div>
        </div>

        <div class="review-body">
            @if($vehicle->pending_application_changes)
                @php
                    $pendingVehicleLabels = [
                        'make' => 'Make',
                        'model' => 'Model',
                        'year' => 'Year',
                        'vehicle_type' => 'Vehicle type',
                        'color' => 'Color',
                        'license_plate' => 'License plate',
                        'registration_number' => 'Registration number',
                        'vin' => 'VIN',
                        'fuel_type' => 'Fuel type',
                        'transmission' => 'Transmission',
                        'engine_size' => 'Engine size',
                        'capacity' => 'Capacity',
                        'number_of_seats' => 'Number of seats',
                        'mileage' => 'Mileage',
                        'condition' => 'Condition',
                        'status' => 'Status',
                        'registration_expiry' => 'Registration expiry',
                        'registration_date' => 'Registration date',
                        'insurance_expiry' => 'Insurance expiry',
                        'inspection_expiry' => 'Inspection expiry',
                        'registration_document' => 'Registration document',
                        'insurance_document' => 'Insurance document',
                        'roadworthiness_certificate' => 'Roadworthiness certificate',
                        'image_url' => 'Vehicle images',
                        'description' => 'Description',
                    ];
                    $pendingVehicleFiles = ['registration_document', 'insurance_document', 'roadworthiness_certificate'];
                @endphp
                <div class="detail-box border border-warning mb-3">
                    <div class="detail-label">Vehicle application changes awaiting review</div>
                    <ul class="mb-3">
                        @foreach($vehicle->pending_application_changes as $field => $value)
                            <li>
                                <strong>{{ $pendingVehicleLabels[$field] ?? $field }}:</strong>
                                @if(in_array($field, $pendingVehicleFiles, true))
                                    <a href="{{ route('admin.application-changes.documents.show', ['type' => 'vehicle', 'id' => $vehicle->id, 'field' => $field]) }}" target="_blank" rel="noopener">View submitted document</a>
                                @elseif($field === 'image_url' && is_array($value))
                                    @foreach($value as $index => $path)
                                        <a class="me-2" href="{{ route('admin.application-changes.documents.show', ['type' => 'vehicle', 'id' => $vehicle->id, 'field' => $field, 'index' => $index]) }}" target="_blank" rel="noopener">View image {{ $index + 1 }}</a>
                                    @endforeach
                                @else
                                    {{ is_scalar($value) ? ($value === '' ? '—' : $value) : json_encode($value) }}
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    <div class="d-flex flex-wrap gap-2">
                        <form action="{{ route('admin.vehicles.application-changes.approve', $vehicle) }}" method="POST" onsubmit="return confirm('Approve these vehicle application changes?')">
                            @csrf
                            <button type="submit" class="btn btn-success">Approve changes</button>
                        </form>
                        <form action="{{ route('admin.vehicles.application-changes.reject', $vehicle) }}" method="POST" onsubmit="return confirm('Reject these vehicle application changes?')">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger">Reject changes</button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="vehicle-grid">
                <div class="vehicle-summary">
                    <img src="{{ $vehicle->imagePaths() ? route('admin.documents.show', ['type' => 'vehicle', 'id' => $vehicle->id, 'field' => 'image_url', 'index' => 0]) : 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=800&q=60' }}" alt="Vehicle image" class="vehicle-main-image">
                    <div class="detail-label">Overview</div>
                    <div class="detail-value">{{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }} · {{ ucfirst($vehicle->vehicle_type ?? 'Vehicle') }}</div>
                </div>

                <div class="detail-grid">
                    <div class="detail-box">
                        <div class="detail-label">License Plate</div>
                        <div class="detail-value">{{ $vehicle->license_plate ?? 'N/A' }}</div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">VIN</div>
                        <div class="detail-value">{{ $vehicle->vin ?? 'N/A' }}</div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">Color</div>
                        <div class="detail-value">{{ ucfirst($vehicle->color ?? 'Unknown') }}</div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">Capacity</div>
                        <div class="detail-value">{{ $vehicle->capacity ?? 'N/A' }} seats</div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">Status</div>
                        <div class="detail-value">{{ ucfirst($vehicle->status ?? 'Unknown') }}</div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">Description</div>
                        <div class="detail-value">{{ $vehicle->description ?? 'No description provided.' }}</div>
                    </div>
                </div>
            </div>

            <div class="detail-box">
                <div class="detail-label">Uploaded Documents</div>
                @php
                    $documents = [
                        'Registration' => $vehicle->registration_document,
                        'Insurance' => $vehicle->insurance_document,
                        'Roadworthy' => $vehicle->roadworthiness_certificate,
                    ];
                @endphp
                <ul class="documents-list">
                    @foreach($documents as $label => $path)
                        @if($path)
                            <li>
                                <span>{{ $label }}</span>
                                <a href="{{ route('admin.documents.show', ['type' => 'vehicle', 'id' => $vehicle->id, 'field' => match ($label) { 'Registration' => 'registration_document', 'Insurance' => 'insurance_document', default => 'roadworthiness_certificate' }]) }}" target="_blank">View</a>
                            </li>
                        @endif
                    @endforeach
                    @if(count(array_filter($documents)) === 0)
                        <li>No documents uploaded for this vehicle.</li>
                    @endif
                </ul>
            </div>

            <div class="action-row">
                <form action="{{ route('admin.vehicles.approve', $vehicle) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-action btn-approve">Approve Vehicle</button>
                </form>
                <button type="button" class="btn-action btn-reject" data-bs-toggle="modal" data-bs-target="#rejectVehicleModal">Reject Vehicle</button>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rejectVehicleModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Vehicle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.vehicles.reject', $vehicle) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="rejection_reason" class="form-label">Reason for Rejection</label>
                            <textarea id="rejection_reason" name="rejection_reason" class="form-control" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Vehicle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
