@extends('layouts.app')

@section('title', 'Edit Vehicle')

@section('content')
<div class="container-max content-section">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-edit"></i> Edit Vehicle</h4>
                </div>
                <div class="card-body">
                    @if($vehicle->pending_application_changes)
                        <div class="alert alert-info" role="status">
                            Your vehicle application changes are waiting for admin review. Your approved details remain active until the changes are approved.
                        </div>
                    @endif
                    <form action="{{ route('vehicles.update', $vehicle) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <fieldset @disabled($vehicle->pending_application_changes) class="border-0 p-0 m-0">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="make" class="form-label">Make</label>
                                <input type="text" class="form-control" id="make" name="make" value="{{ old('make', $vehicle->make) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="model" class="form-label">Model</label>
                                <input type="text" class="form-control" id="model" name="model" value="{{ old('model', $vehicle->model) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="year" class="form-label">Year</label>
                                <input type="number" class="form-control" id="year" name="year" value="{{ old('year', $vehicle->year) }}" min="1900" max="{{ date('Y') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="vehicle_type" class="form-label">Type</label>
                                <select class="form-select" id="vehicle_type" name="vehicle_type" required>
                                    <option value="">Choose</option>
                                    <option value="car" {{ old('vehicle_type', $vehicle->vehicle_type) === 'car' ? 'selected' : '' }}>Car</option>
                                    <option value="van" {{ old('vehicle_type', $vehicle->vehicle_type) === 'van' ? 'selected' : '' }}>Van</option>
                                    <option value="truck" {{ old('vehicle_type', $vehicle->vehicle_type) === 'truck' ? 'selected' : '' }}>Truck</option>
                                    <option value="motorcycle" {{ old('vehicle_type', $vehicle->vehicle_type) === 'motorcycle' ? 'selected' : '' }}>Motorcycle</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="">Choose</option>
                                    <option value="available" {{ old('status', $vehicle->status) === 'available' ? 'selected' : '' }}>Available</option>
                                    <option value="unavailable" {{ old('status', $vehicle->status) === 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                                    <option value="maintenance" {{ old('status', $vehicle->status) === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                    <option value="on_duty" {{ old('status', $vehicle->status) === 'on_duty' ? 'selected' : '' }}>On Duty</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="license_plate" class="form-label">License Plate</label>
                                <input type="text" class="form-control @error('license_plate') is-invalid @enderror" id="license_plate" name="license_plate" value="{{ old('license_plate', $vehicle->license_plate) }}" placeholder="e.g., 2301-GR1 or AS 2567-12" maxlength="20" required>
                                @error('license_plate') <small class="text-danger">{{ $message }}</small> @enderror
                                <small class="form-text text-muted">Use a Ghana plate format such as 2301-GR1 or AS 2567-12.</small>
                            </div>
                            <div class="col-md-6">
                                <label for="vin" class="form-label">VIN</label>
                                <input type="text" class="form-control @error('vin') is-invalid @enderror" id="vin" name="vin" value="{{ old('vin', $vehicle->vin) }}" placeholder="17 uppercase letters/numbers, excluding I, O, Q" pattern="[A-HJ-NPR-Z0-9]{17}" minlength="17" maxlength="17">
                                @error('vin') <small class="text-danger">{{ $message }}</small> @enderror
                                <small class="form-text text-muted">Exactly 17 characters; do not use I, O, or Q.</small>
                            </div>
                            <div class="col-md-6">
                                <label for="registration_date" class="form-label">Registration Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('registration_date') is-invalid @enderror" id="registration_date" name="registration_date" value="{{ old('registration_date', $vehicle->registration_date?->format('Y-m-d')) }}" required>
                                @error('registration_date') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="registration_expiry" class="form-label">Registration Expiry <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('registration_expiry') is-invalid @enderror" id="registration_expiry" name="registration_expiry" value="{{ old('registration_expiry', $vehicle->registration_expiry?->format('Y-m-d')) }}" required>
                                @error('registration_expiry') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="insurance_expiry" class="form-label">Insurance Expiry <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('insurance_expiry') is-invalid @enderror" id="insurance_expiry" name="insurance_expiry" value="{{ old('insurance_expiry', $vehicle->insurance_expiry?->format('Y-m-d')) }}" required>
                                @error('insurance_expiry') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="inspection_expiry" class="form-label">Roadworthiness Expiry <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('inspection_expiry') is-invalid @enderror" id="inspection_expiry" name="inspection_expiry" value="{{ old('inspection_expiry', $vehicle->inspection_expiry?->format('Y-m-d')) }}" required>
                                @error('inspection_expiry') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="capacity" class="form-label">Capacity</label>
                                <input type="number" class="form-control" id="capacity" name="capacity" value="{{ old('capacity', $vehicle->capacity) }}" min="1">
                            </div>
                            <div class="col-12">
                                <label for="image_url" class="form-label">Vehicle Images</label>
                                <input type="file" class="form-control @error('image_url') is-invalid @enderror" id="image_url" name="image_url[]" accept="image/jpeg,image/png,image/gif,image/webp,.jpg,.jpeg,.png,.gif,.webp" multiple @if(empty($vehicle->imagePaths())) required @endif>
                                @error('image_url') <small class="text-danger">{{ $message }}</small> @enderror
                                @if($vehicle->imagePaths())
                                    <div class="mt-2">
                                        <small class="text-muted">Current images:</small>
                                            @foreach($vehicle->imagePaths() as $index => $image)
                                            <div class="d-inline-block me-2 mb-2">
                                                <img src="{{ route('vehicles.images.show', ['vehicle' => $vehicle, 'index' => $index]) }}" alt="Vehicle image" class="vehicle-thumbnail">
                                            </div>
                                            @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $vehicle->description) }}</textarea>
                            </div>
                        </div>
                        <div class="mt-4 d-flex justify-content-between">
                            <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-secondary">Back</a>
                            <button type="submit" class="btn btn-primary">Update Vehicle</button>
                        </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
