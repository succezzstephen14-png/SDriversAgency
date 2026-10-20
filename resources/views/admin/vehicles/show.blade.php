@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="fas fa-car me-2"></i>Vehicle Details</h2>
            <p class="text-muted">{{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.vehicles.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Vehicles
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <!-- Vehicle Information -->
        <div class="col-md-8">
            <!-- Basic Information -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Basic Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Make & Model</small>
                            <strong>{{ $vehicle->make }} {{ $vehicle->model }}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Year</small>
                            <strong>{{ $vehicle->year }}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">License Plate</small>
                            <strong>{{ $vehicle->license_plate }}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Vehicle Type</small>
                            <strong>{{ ucfirst($vehicle->vehicle_type) }}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Color</small>
                            <strong>{{ $vehicle->color ?? '—' }}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">VIN</small>
                            <strong>{{ $vehicle->vin ?? '—' }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Technical Specifications -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-cogs"></i> Technical Specifications</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Condition</small>
                            <strong>
                                <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $vehicle->condition)) }}</span>
                            </strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Fuel Type</small>
                            <strong>{{ ucfirst($vehicle->fuel_type ?? '—') }}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Transmission</small>
                            <strong>{{ ucfirst($vehicle->transmission ?? '—') }}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Engine Size</small>
                            <strong>{{ $vehicle->engine_size ?? '—' }} cc</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Seating Capacity</small>
                            <strong>{{ $vehicle->capacity ?? '—' }} passengers</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Mileage</small>
                            <strong>{{ $vehicle->mileage ?? '—' }} km</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Compliance & Expiry -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-calendar"></i> Compliance & Expiry Dates</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <small class="text-muted d-block">Registration Date</small>
                            <strong>{{ $vehicle->registration_date?->format('M d, Y') ?? '—' }}</strong>
                        </div>
                        <div class="col-md-4 mb-3">
                            <small class="text-muted d-block">Registration Expiry</small>
                            @if($vehicle->registration_expiry)
                                <strong>{{ \Carbon\Carbon::parse($vehicle->registration_expiry)->format('M d, Y') }}</strong>
                                @if(\Carbon\Carbon::parse($vehicle->registration_expiry)->isPast())
                                    <span class="badge bg-danger ms-2">Expired</span>
                                @endif
                            @else
                                <strong class="text-muted">—</strong>
                            @endif
                        </div>
                        <div class="col-md-4 mb-3">
                            <small class="text-muted d-block">Insurance Expiry</small>
                            @if($vehicle->insurance_expiry)
                                <strong>{{ \Carbon\Carbon::parse($vehicle->insurance_expiry)->format('M d, Y') }}</strong>
                                @if(\Carbon\Carbon::parse($vehicle->insurance_expiry)->isPast())
                                    <span class="badge bg-danger ms-2">Expired</span>
                                @endif
                            @else
                                <strong class="text-muted">—</strong>
                            @endif
                        </div>
                        <div class="col-md-4 mb-3">
                            <small class="text-muted d-block">Roadworthiness Expiry</small>
                            @if($vehicle->roadworthiness_certificate || $vehicle->inspection_expiry)
                                <strong>{{ $vehicle->inspection_expiry ? \Carbon\Carbon::parse($vehicle->inspection_expiry)->format('M d, Y') : '—' }}</strong>
                                @if($vehicle->inspection_expiry && \Carbon\Carbon::parse($vehicle->inspection_expiry)->isPast())
                                    <span class="badge bg-danger ms-2">Expired</span>
                                @endif
                            @else
                                <strong class="text-muted">—</strong>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-file"></i> Uploaded Documents</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Registration Document</small>
                            @if($vehicle->registration_document)
                                <a href="{{ route('admin.documents.show', ['type' => 'vehicle', 'id' => $vehicle->id, 'field' => 'registration_document']) }}"
                                   target="_blank" class="btn btn-sm btn-primary">
                                    <i class="fas fa-download me-2"></i>View
                                </a>
                            @else
                                <span class="text-muted">Not uploaded</span>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Insurance Document</small>
                            @if($vehicle->insurance_document)
                                <a href="{{ route('admin.documents.show', ['type' => 'vehicle', 'id' => $vehicle->id, 'field' => 'insurance_document']) }}"
                                   target="_blank" class="btn btn-sm btn-primary">
                                    <i class="fas fa-download me-2"></i>View
                                </a>
                            @else
                                <span class="text-muted">Not uploaded</span>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Roadworthiness Certificate</small>
                            @if($vehicle->roadworthiness_certificate)
                                <a href="{{ route('admin.documents.show', ['type' => 'vehicle', 'id' => $vehicle->id, 'field' => 'roadworthiness_certificate']) }}"
                                   target="_blank" class="btn btn-sm btn-primary">
                                    <i class="fas fa-download me-2"></i>View
                                </a>
                            @else
                                <span class="text-muted">Not uploaded</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            @if($vehicle->description)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-align-left"></i> Description</h6>
                </div>
                <div class="card-body">
                    <p>{{ $vehicle->description }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Status & Actions -->
        <div class="col-md-4">
            <!-- Owner Information -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-user"></i> Owner Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Name</small>
                        <strong>{{ $vehicle->user->name }}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Email</small>
                        <strong>{{ $vehicle->user->email }}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Phone</small>
                        <strong>{{ $vehicle->user->phone ?? '—' }}</strong>
                    </div>
                    <a href="{{ route('admin.users.show', $vehicle->user) }}" class="btn btn-sm btn-info w-100">
                        <i class="fas fa-user-circle me-2"></i>View Owner Profile
                    </a>
                </div>
            </div>

            <!-- Approval Status -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-check-circle"></i> Approval Status</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Status</small>
                        @if($vehicle->approval_status === 'approved')
                            <span class="badge bg-success p-2">✓ Approved</span>
                        @elseif($vehicle->approval_status === 'rejected')
                            <span class="badge bg-danger p-2">✗ Rejected</span>
                        @elseif($vehicle->approval_status === 'pending')
                            <span class="badge bg-warning p-2">⧗ Pending</span>
                        @else
                            <span class="badge bg-secondary p-2">{{ ucfirst($vehicle->approval_status) }}</span>
                        @endif
                    </div>

                    @if($vehicle->rejection_reason)
                    <div class="alert alert-warning small mb-3">
                        <strong>Rejection Reason:</strong><br>
                        {{ $vehicle->rejection_reason }}
                    </div>
                    @endif

                    @if($vehicle->approval_status === 'pending')
                    <div class="d-grid gap-2 mb-3">
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" 
                                data-bs-target="#approveModal">
                            <i class="fas fa-check me-2"></i>Approve Vehicle
                        </button>
                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" 
                                data-bs-target="#rejectModal">
                            <i class="fas fa-times me-2"></i>Reject Vehicle
                        </button>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Availability Status -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-sliders-h"></i> Availability Status</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Current Status</small>
                        @if($vehicle->availability_status === 'available')
                            <span class="badge bg-success p-2">Available</span>
                        @elseif($vehicle->availability_status === 'in_use')
                            <span class="badge bg-info p-2">In Use</span>
                        @else
                            <span class="badge bg-danger p-2">Unavailable</span>
                        @endif
                    </div>

                    @if($vehicle->suspension_reason)
                    <div class="alert alert-danger small mb-3">
                        <strong>Suspension Reason:</strong><br>
                        {{ $vehicle->suspension_reason }}
                    </div>
                    @endif

                    <div class="d-grid gap-2">
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" 
                                data-bs-target="#availabilityModal">
                            <i class="fas fa-edit me-2"></i>Change Status
                        </button>
                        @if($vehicle->availability_status === 'unavailable')
                        <form method="POST" action="{{ route('admin.vehicles.resume', $vehicle) }}" 
                              style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-info btn-sm w-100">
                                <i class="fas fa-play me-2"></i>Resume Vehicle
                            </button>
                        </form>
                        @else
                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" 
                                data-bs-target="#suspendModal">
                            <i class="fas fa-pause me-2"></i>Suspend Vehicle
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Timestamps -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-clock"></i> Timestamps</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted d-block">Created</small>
                        <small>{{ $vehicle->created_at->format('M d, Y H:i') }}</small>
                    </div>
                    <div>
                        <small class="text-muted d-block">Last Updated</small>
                        <small>{{ $vehicle->updated_at->format('M d, Y H:i') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.vehicles.approve', $vehicle) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Approve Vehicle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to approve this vehicle for use?</p>
                    <p class="mb-0"><strong>{{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.vehicles.reject', $vehicle) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Vehicle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Rejection Reason *</label>
                    <textarea name="rejection_reason" class="form-control" rows="4" 
                              placeholder="Explain why this vehicle is being rejected..." required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Availability Modal -->
<div class="modal fade" id="availabilityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.vehicles.update-availability', $vehicle) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Change Availability Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Availability Status *</label>
                    <select name="availability_status" class="form-select" required>
                        <option value="">Select Status</option>
                        <option value="available" {{ $vehicle->availability_status === 'available' ? 'selected' : '' }}>
                            Available (can be booked)
                        </option>
                        <option value="in_use" {{ $vehicle->availability_status === 'in_use' ? 'selected' : '' }}>
                            In Use (currently assigned)
                        </option>
                        <option value="unavailable" {{ $vehicle->availability_status === 'unavailable' ? 'selected' : '' }}>
                            Unavailable (maintenance/suspended)
                        </option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Suspend Modal -->
<div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.vehicles.suspend', $vehicle) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Suspend Vehicle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Suspension Reason *</label>
                    <textarea name="suspension_reason" class="form-control" rows="4" 
                              placeholder="Explain why this vehicle is being suspended..." required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Suspend</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
