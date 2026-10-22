@extends('layouts.dashboard')

@section('title', 'Create Driver Profile')

@section('content')
<div class="container-max content-section">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-user-plus"></i> Create Driver Profile</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Payment Required:</strong> A payment of {{ config('app.currency_label', 'GHC') }} 0.20 is required to submit your driver profile for admin review. Approved profiles are not shown on the public website.
                    </div>

                    <form action="{{ route('drivers.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Personal Information Section -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-user-circle"></i> Personal Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="dob" class="form-label">Date of Birth *</label>
                                        <input type="date" class="form-control @error('dob') is-invalid @enderror" 
                                               id="dob" name="dob" value="{{ old('dob') }}" required>
                                        @error('dob') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="emergency_person" class="form-label">Emergency Contact Person *</label>
                                        <input type="text" class="form-control @error('emergency_person') is-invalid @enderror" 
                                               id="emergency_person" name="emergency_person" value="{{ old('emergency_person') }}" required>
                                        @error('emergency_person') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="emergency_phone" class="form-label">Emergency Contact Phone *</label>
                                        <input type="tel" class="form-control @error('emergency_phone') is-invalid @enderror" 
                                               id="emergency_phone" name="emergency_phone" value="{{ old('emergency_phone') }}" required>
                                        @error('emergency_phone') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="profile_picture" class="form-label">Profile Picture * (Required)</label>
                                        <input type="file" class="form-control @error('profile_picture') is-invalid @enderror" 
                                               id="profile_picture" name="profile_picture" 
                                               accept="image/jpeg,image/png,image/gif,image/webp,.jpg,.jpeg,.png,.gif,.webp" required>
                                        <small class="text-muted d-block mt-2">
                                            <i class="fas fa-info-circle"></i> Max 5MB. Supported image formats: JPEG, PNG, GIF, WebP, and others.
                                        </small>
                                        @error('profile_picture') <small class="text-danger">{{ $message }}</small> @enderror
                                        <div id="profile-picture-preview" class="mt-2" style="display: none;">
                                            <img id="profile-picture-img" style="max-width: 150px; border-radius: 8px; border: 2px solid #e5e7eb;">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label for="bio" class="form-label">Bio * (Required)</label>
                                        <textarea class="form-control @error('bio') is-invalid @enderror" 
                                                  id="bio" name="bio" rows="3" 
                                                  placeholder="Tell us about yourself, your driving experience, and what makes you a great driver..." required>{{ old('bio') }}</textarea>
                                        @error('bio') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- License Information Section -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-id-card"></i> License Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="license_number" class="form-label">License Number *</label>
                                        <input type="text" class="form-control @error('license_number') is-invalid @enderror" 
                                               id="license_number" name="license_number" value="{{ old('license_number') }}"
                                               placeholder="Enter your driver's license number" maxlength="50" required>
                                        <small class="text-muted d-block mt-1">Enter the license number as printed on your license.</small>
                                        @error('license_number') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="license_class" class="form-label">License Class *</label>
                                        <select class="form-select @error('license_class') is-invalid @enderror" 
                                                id="license_class" name="license_class" required>
                                            <option value="">Select Class</option>
                                            <option value="A" {{ old('license_class') === 'A' ? 'selected' : '' }}>Class A - Motorcycle</option>
                                            <option value="B" {{ old('license_class') === 'B' ? 'selected' : '' }}>Class B - Car</option>
                                            <option value="C" {{ old('license_class') === 'C' ? 'selected' : '' }}>Class C - Van/Small Truck</option>
                                            <option value="D" {{ old('license_class') === 'D' ? 'selected' : '' }}>Class D - Truck</option>
                                            <option value="E" {{ old('license_class') === 'E' ? 'selected' : '' }}>Class E - Heavy Truck</option>
                                        </select>
                                        @error('license_class') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="license_expiry" class="form-label">License Expiry Date *</label>
                                        <input type="date" class="form-control @error('license_expiry') is-invalid @enderror" 
                                               id="license_expiry" name="license_expiry" value="{{ old('license_expiry') }}" required>
                                        @error('license_expiry') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="license_file" class="form-label">License Copy * (Required)</label>
                                        <input type="file" class="form-control @error('license_file') is-invalid @enderror" 
                                               id="license_file" name="license_file" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf" required>
                                        <small class="text-muted d-block mt-1"><i class="fas fa-info-circle"></i> Upload a photo or PDF of your driver's license (max 5MB).</small>
                                        @error('license_file') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="experience_years" class="form-label">Years of Experience *</label>
                                        <input type="number" class="form-control @error('experience_years') is-invalid @enderror" 
                                               id="experience_years" name="experience_years" value="{{ old('experience_years') }}" 
                                               min="0" max="60" placeholder="e.g., 5" required>
                                        @error('experience_years') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ghana Card & Passport Information Section -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-passport"></i> Identity Documents</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="ghana_card_no" class="form-label">Ghana Card Number * (Required)</label>
                                        <input type="text" class="form-control @error('ghana_card_no') is-invalid @enderror" 
                                               id="ghana_card_no" name="ghana_card_no" value="{{ old('ghana_card_no') }}" 
                                               placeholder="GHA-001122330-1" pattern="GHA-[0-9]{9}-[0-9]" maxlength="15" required>
                                        <small class="text-muted d-block mt-1"><i class="fas fa-info-circle"></i> Format: GHA-001122330-1</small>
                                        @error('ghana_card_no') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="ghana_card_file" class="form-label">Ghana Card Copy * (Required)</label>
                                        <input type="file" class="form-control @error('ghana_card_file') is-invalid @enderror" 
                                               id="ghana_card_file" name="ghana_card_file" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf" required>
                                        <small class="text-muted d-block mt-1"><i class="fas fa-info-circle"></i> Upload a photo or PDF of your Ghana Card (max 5MB).</small>
                                        @error('ghana_card_file') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="passport_file" class="form-label">Passport Bio Data Page Copy (Optional)</label>
                                        <input type="file" class="form-control @error('passport_file') is-invalid @enderror" 
                                               id="passport_file" name="passport_file" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf">
                                        <small class="text-muted d-block mt-1"><i class="fas fa-info-circle"></i> Optional. Upload a photo or PDF (max 5MB).</small>
                                        @error('passport_file') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-shield-alt"></i> Police Background Check</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="police_check_file" class="form-label">Police Criminal Background Check * (Required)</label>
                                        <input type="file" class="form-control @error('police_check_file') is-invalid @enderror"
                                               id="police_check_file" name="police_check_file" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf" required>
                                        <small class="text-muted d-block mt-1">Upload a clear photo or PDF of your police criminal background check (max 5MB).</small>
                                        @error('police_check_file') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input @error('police_check_consent') is-invalid @enderror"
                                                   id="police_check_consent" name="police_check_consent" value="1"
                                                   {{ old('police_check_consent') ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="police_check_consent">
                                                I consent to the agency reviewing this document and conducting the police criminal background check as part of driver vetting. *
                                            </label>
                                        </div>
                                        @error('police_check_consent') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Guarantor Information Section -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-handshake"></i> Guarantor Information * (Required)</h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning" role="alert">
                                    <i class="fas fa-exclamation-circle"></i> <strong>Required:</strong> All guarantor information fields must be completed to activate your driver profile.
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="guarantor" class="form-label">Guarantor Name * (Required)</label>
                                        <input type="text" class="form-control @error('guarantor') is-invalid @enderror" 
                                               id="guarantor" name="guarantor" value="{{ old('guarantor') }}" 
                                               placeholder="Full name of your guarantor" required>
                                        @error('guarantor') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="guarantor_phone" class="form-label">Guarantor Phone * (Required)</label>
                                        <input type="tel" class="form-control @error('guarantor_phone') is-invalid @enderror" 
                                               id="guarantor_phone" name="guarantor_phone" value="{{ old('guarantor_phone') }}" required>
                                        @error('guarantor_phone') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="g_ghana_card_no" class="form-label">Guarantor Ghana Card Number * (Required)</label>
                                        <input type="text" class="form-control @error('g_ghana_card_no') is-invalid @enderror" 
                                               id="g_ghana_card_no" name="g_ghana_card_no" value="{{ old('g_ghana_card_no') }}"
                                               pattern="GHA-[0-9]{9}-[0-9]" maxlength="15"
                                               placeholder="GHA-001122330-1" required>
                                        <small class="text-muted d-block mt-1"><i class="fas fa-info-circle"></i> Ghana Card format: GHA-001122330-1</small>
                                        @error('g_ghana_card_no') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="guarantor_ghana_card_file" class="form-label">Guarantor Ghana Card Copy * (Required)</label>
                                        <input type="file" class="form-control @error('guarantor_ghana_card_file') is-invalid @enderror" 
                                               id="guarantor_ghana_card_file" name="guarantor_ghana_card_file" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf" required>
                                        <small class="text-muted d-block mt-1"><i class="fas fa-info-circle"></i> Upload a photo or PDF of the guarantor's Ghana Card (max 5MB).</small>
                                        @error('guarantor_ghana_card_file') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="guarantor_passport_file" class="form-label">Guarantor Passport Photo * (Required)</label>
                                        <input type="file" class="form-control @error('guarantor_passport_file') is-invalid @enderror"
                                               id="guarantor_passport_file" name="guarantor_passport_file"
                                               accept="image/jpeg,image/png,image/gif,image/webp,.jpg,.jpeg,.png,.gif,.webp" required>
                                        <small class="text-muted d-block mt-1"><i class="fas fa-info-circle"></i> Upload a clear passport-style photo (max 5MB).</small>
                                        @error('guarantor_passport_file') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>

                                    <div class="col-md-12">
                                        <label for="relationship" class="form-label">Relationship to Guarantor * (Required)</label>
                                        <select class="form-select @error('relationship') is-invalid @enderror" 
                                                id="relationship" name="relationship" required>
                                            <option value="">Select Relationship</option>
                                            <option value="family" {{ old('relationship') === 'family' ? 'selected' : '' }}>Family Member</option>
                                            <option value="employer" {{ old('relationship') === 'employer' ? 'selected' : '' }}>Former Employer</option>
                                            <option value="friend" {{ old('relationship') === 'friend' ? 'selected' : '' }}>Friend</option>
                                            <option value="colleague" {{ old('relationship') === 'colleague' ? 'selected' : '' }}>Colleague</option>
                                        </select>
                                        @error('relationship') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-handshake"></i> Second Guarantor Information * (Required)</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="second_guarantor" class="form-label">Second Guarantor Name *</label>
                                        <input type="text" class="form-control @error('second_guarantor') is-invalid @enderror"
                                               id="second_guarantor" name="second_guarantor" value="{{ old('second_guarantor') }}"
                                               placeholder="Full name of second guarantor" required>
                                        @error('second_guarantor') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="second_guarantor_phone" class="form-label">Second Guarantor Phone *</label>
                                        <input type="tel" class="form-control @error('second_guarantor_phone') is-invalid @enderror"
                                               id="second_guarantor_phone" name="second_guarantor_phone" value="{{ old('second_guarantor_phone') }}" required>
                                        @error('second_guarantor_phone') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="second_g_ghana_card_no" class="form-label">Second Guarantor Ghana Card Number *</label>
                                        <input type="text" class="form-control @error('second_g_ghana_card_no') is-invalid @enderror"
                                               id="second_g_ghana_card_no" name="second_g_ghana_card_no" value="{{ old('second_g_ghana_card_no') }}"
                                               pattern="GHA-[0-9]{9}-[0-9]" maxlength="15" placeholder="GHA-001122330-1" required>
                                        <small class="text-muted d-block mt-1">Format: GHA-001122330-1</small>
                                        @error('second_g_ghana_card_no') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="second_guarantor_ghana_card_file" class="form-label">Second Guarantor Ghana Card Copy *</label>
                                        <input type="file" class="form-control @error('second_guarantor_ghana_card_file') is-invalid @enderror"
                                               id="second_guarantor_ghana_card_file" name="second_guarantor_ghana_card_file"
                                               accept=".jpg,.jpeg,.png,.gif,.webp,.pdf" required>
                                        <small class="text-muted d-block mt-1">Upload a photo or PDF (max 5MB).</small>
                                        @error('second_guarantor_ghana_card_file') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="second_guarantor_passport_file" class="form-label">Second Guarantor Passport Photo *</label>
                                        <input type="file" class="form-control @error('second_guarantor_passport_file') is-invalid @enderror"
                                               id="second_guarantor_passport_file" name="second_guarantor_passport_file"
                                               accept="image/jpeg,image/png,image/gif,image/webp,.jpg,.jpeg,.png,.gif,.webp" required>
                                        <small class="text-muted d-block mt-1">Upload a clear passport-style photo (max 5MB).</small>
                                        @error('second_guarantor_passport_file') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                    <div class="col-md-12">
                                        <label for="second_relationship" class="form-label">Relationship to Second Guarantor *</label>
                                        <select class="form-select @error('second_relationship') is-invalid @enderror"
                                                id="second_relationship" name="second_relationship" required>
                                            <option value="">Select Relationship</option>
                                            <option value="family" {{ old('second_relationship') === 'family' ? 'selected' : '' }}>Family Member</option>
                                            <option value="employer" {{ old('second_relationship') === 'employer' ? 'selected' : '' }}>Former Employer</option>
                                            <option value="friend" {{ old('second_relationship') === 'friend' ? 'selected' : '' }}>Friend</option>
                                            <option value="colleague" {{ old('second_relationship') === 'colleague' ? 'selected' : '' }}>Colleague</option>
                                        </select>
                                        @error('second_relationship') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('drivers.profile') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Profile
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-credit-card"></i> Proceed to Payment
                            </button>
                        </div>
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
    const previewImage = document.getElementById('profile-picture-img');

    if (profilePictureInput) {
        profilePictureInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    previewImage.src = event.target.result;
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }
</script>
@endsection
