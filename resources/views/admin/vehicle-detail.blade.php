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
                <p style="opacity: .85; margin: .25rem 0 0;">Phone: {{ $vehicle->user->phone ?: 'Not provided' }}</p>
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
                        <div class="detail-label">Application ID</div>
                        <div class="detail-value">{{ $vehicle->application_id ?? 'N/A' }}</div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">Registration Number</div>
                        <div class="detail-value">{{ $vehicle->registration_number ?? 'N/A' }}</div>
                    </div>
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
                        <div class="detail-label">Number of Seats</div>
                        <div class="detail-value">{{ $vehicle->number_of_seats ?? 'N/A' }}</div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">Condition</div>
                        <div class="detail-value">{{ str_replace('_', ' ', $vehicle->condition_label) }}</div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">Fuel Type</div>
                        <div class="detail-value">{{ str_replace('_', ' ', ucfirst($vehicle->fuel_type ?? 'Not provided')) }}</div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">Transmission</div>
                        <div class="detail-value">{{ str_replace('_', ' ', ucfirst($vehicle->transmission ?? 'Not provided')) }}</div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">Engine Size</div>
                        <div class="detail-value">{{ $vehicle->engine_size ?: 'Not provided' }}</div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">Mileage</div>
                        <div class="detail-value">{{ $vehicle->mileage !== null ? number_format($vehicle->mileage) . ' km' : 'Not provided' }}</div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">Registration Date</div>
                        <div class="detail-value">{{ $vehicle->registration_date?->format('M j, Y') ?? 'Not provided' }}</div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">Registration Expiry</div>
                        <div class="detail-value">{{ $vehicle->registration_expiry?->format('M j, Y') ?? 'Not provided' }}</div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">Insurance Expiry</div>
                        <div class="detail-value">{{ $vehicle->insurance_expiry?->format('M j, Y') ?? 'Not provided' }}</div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">Roadworthiness Expiry</div>
                        <div class="detail-value">{{ $vehicle->inspection_expiry?->format('M j, Y') ?? 'Not provided' }}</div>
                    </div>
                    @if($vehicle->inspection_appointment_date)
                        <div class="detail-box border border-primary">
                            <div class="detail-label">Inspection Appointment</div>
                            <div class="detail-value">{{ $vehicle->inspection_appointment_date->format('M j, Y') }}</div>
                        </div>
                    @endif
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

            @if(count($vehicle->imagePaths()) > 1)
                <div class="detail-box">
                    <div class="detail-label">Additional Vehicle Images</div>
                    <div class="d-flex flex-wrap gap-3">
                        @foreach(array_slice($vehicle->imagePaths(), 1) as $index => $imagePath)
                            <a href="{{ route('admin.documents.show', ['type' => 'vehicle', 'id' => $vehicle->id, 'field' => 'image_url', 'index' => $index + 1]) }}" target="_blank" rel="noopener">
                                <img src="{{ route('admin.documents.show', ['type' => 'vehicle', 'id' => $vehicle->id, 'field' => 'image_url', 'index' => $index + 1]) }}" alt="Vehicle image {{ $index + 2 }}" style="width:140px;height:100px;object-fit:cover;border-radius:10px;">
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

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

            <div class="detail-box">
                <div class="detail-label">Schedule Vehicle Inspection</div>
                @if($vehicle->inspection_appointment_date)
                    <p class="mb-3">Current appointment: <strong>{{ $vehicle->inspection_appointment_date->format('F j, Y') }}</strong></p>
                @else
                    <p class="text-muted mb-3">No inspection appointment has been issued.</p>
                @endif
                <form action="{{ route('admin.vehicles.inspection.schedule', $vehicle) }}" method="POST" class="d-flex flex-wrap align-items-end gap-3">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="inspection_appointment_date" class="form-label">Inspection date</label>
                        <input
                            id="inspection_appointment_date"
                            name="inspection_appointment_date"
                            type="date"
                            class="form-control"
                            min="{{ now()->toDateString() }}"
                            value="{{ old('inspection_appointment_date', $vehicle->inspection_appointment_date?->toDateString()) }}"
                            required
                        >
                        @error('inspection_appointment_date')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary">
                        {{ $vehicle->inspection_appointment_date ? 'Update inspection date' : 'Issue inspection date' }}
                    </button>
                </form>
                <p class="small text-muted mt-2 mb-0">The vehicle owner will receive an in-app notification and email when the date is scheduled.</p>
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
