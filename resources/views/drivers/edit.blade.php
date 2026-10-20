@extends('layouts.app')

@section('title', 'Edit Driver Profile')

@section('content')
<div class="container-max content-section">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-edit"></i> Edit Driver Profile</h4>
                </div>
                <div class="card-body">
                    @if($driver->pending_application_changes)
                        <div class="alert alert-info" role="status">
                            Your driver application changes are waiting for admin review. Your approved details remain active until the changes are approved.
                        </div>
                    @endif
                    <form action="{{ route('drivers.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <fieldset @disabled($driver->pending_application_changes) class="border-0 p-0 m-0">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="license_number" class="form-label">License Number</label>
                                <input type="text" class="form-control @error('license_number') is-invalid @enderror" id="license_number" name="license_number" value="{{ old('license_number', $driver->license_number) }}" placeholder="ADB-00000000-11111" pattern="ADB-[0-9]{8}-[0-9]{5}" maxlength="18" required>
                                @error('license_number') <small class="text-danger">{{ $message }}</small> @enderror
                                <small class="form-text text-muted">Format: ADB-00000000-11111</small>
                            </div>

                            <div class="col-md-6">
                                <label for="license_class" class="form-label">License Class</label>
                                <select class="form-select" id="license_class" name="license_class" required>
                                    <option value="">Select Class</option>
                                    @foreach(['A','B','C','D','E'] as $class)
                                        <option value="{{ $class }}" {{ old('license_class', $driver->license_class) === $class ? 'selected' : '' }}>{{ $class }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="license_expiry" class="form-label">License Expiry Date</label>
                                <input type="date" class="form-control" id="license_expiry" name="license_expiry" value="{{ old('license_expiry', $driver->license_expiry?->format('Y-m-d')) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label for="experience_years" class="form-label">Experience (Years)</label>
                                <input type="text" class="form-control" id="experience_years" name="experience_years" value="{{ old('experience_years', $driver->experience_years) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="available" {{ old('status', $driver->status) === 'available' ? 'selected' : '' }}>Available</option>
                                    <option value="unavailable" {{ old('status', $driver->status) === 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                                    <option value="on_duty" {{ old('status', $driver->status) === 'on_duty' ? 'selected' : '' }}>On Duty</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="profile_picture" class="form-label">Profile Picture (Upload)</label>
                                <input type="file" class="form-control @error('profile_picture') is-invalid @enderror" 
                                       id="profile_picture" name="profile_picture" 
                                       accept="image/jpeg,image/png,image/gif,image/webp,.jpg,.jpeg,.png,.gif,.webp">
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-info-circle"></i> Max 5MB. Supported image formats
                                </small>
                                @error('profile_picture') <small class="text-danger">{{ $message }}</small> @enderror
                                <div id="profile-picture-preview" class="mt-2">
                                    @if($driver->getProfilePictureUrl())
                                        <img src="{{ $driver->getProfilePictureUrl() }}" 
                                             style="max-width: 150px; border-radius: 8px; border: 2px solid #e5e7eb;">
                                    @endif
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="bio" class="form-label">Bio</label>
                                <textarea class="form-control" id="bio" name="bio" rows="4">{{ old('bio', $driver->bio) }}</textarea>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-between">
                            <a href="{{ route('drivers.profile') }}" class="btn btn-secondary">Back</a>
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Profile picture preview
    const profilePictureInput = document.getElementById('profile_picture');
    const previewContainer = document.getElementById('profile-picture-preview');
    const existingImage = previewContainer.querySelector('img');

    if (profilePictureInput) {
        profilePictureInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    if (existingImage) {
                        existingImage.src = event.target.result;
                    } else {
                        const img = document.createElement('img');
                        img.src = event.target.result;
                        img.style.maxWidth = '150px';
                        img.style.borderRadius = '8px';
                        img.style.border = '2px solid #e5e7eb';
                        previewContainer.appendChild(img);
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
</script>
@endsection
