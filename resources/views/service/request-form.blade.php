@extends('layouts.service')

@section('title', 'New ' . ucfirst($type) . ' Request')
@section('heading', 'Request a ' . $type)

@section('content')
<div class="card service-card"><div class="card-body p-4">
    <p class="text-muted">Submit your request for review. Payment is only requested after an administrator assigns a resource and sends you a quote.</p>
    <form method="POST" action="{{ route('service.requests.store') }}">
        @csrf
        <input type="hidden" name="request_type" value="{{ $type }}">
        <div class="mb-3">
            <label class="form-label" for="service_type">Service</label>
            <select class="form-select" id="service_type" name="service_type" required>
                <option value="">Choose a service</option>
                @foreach($services as $value => $label)
                    <option value="{{ $value }}" @selected(old('service_type') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('service_type')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        @if($type === 'vehicle')
            <div class="mb-3">
                <label class="form-label" for="vehicle_type">Vehicle type</label>
                <select class="form-select" id="vehicle_type" name="vehicle_type" required>
                    <option value="">Choose a vehicle type</option>
                    @foreach($vehicleTypes as $value => $label)
                        <option value="{{ $value }}" @selected(old('vehicle_type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('vehicle_type')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
        @endif
        <div class="mb-3">
            <label class="form-label" for="location">Service location</label>
            <input class="form-control" id="location" name="location" value="{{ old('location') }}" required maxlength="255" placeholder="Town, city or pickup area">
            @error('location')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="preferred_start_date">Preferred start date</label>
            <input class="form-control" type="date" id="preferred_start_date" name="preferred_start_date" value="{{ old('preferred_start_date') }}" min="{{ now()->toDateString() }}">
            @error('preferred_start_date')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="duration">Expected duration</label>
            <input class="form-control" id="duration" name="duration" value="{{ old('duration') }}" maxlength="100" placeholder="For example: 3 months, 1 day, ongoing">
            @error('duration')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="details">Additional details</label>
            <textarea class="form-control" id="details" name="details" rows="4" maxlength="3000">{{ old('details') }}</textarea>
            @error('details')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-primary">Submit for admin review</button>
    </form>
</div></div>
@endsection
