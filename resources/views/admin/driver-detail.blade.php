@extends('layouts.admin')

@section('title', 'Driver Details')

@section('content')
@php
    use Illuminate\Support\Str;
    use Carbon\Carbon;
@endphp
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
    .review-header .status-pill {
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
        grid-template-columns: 1fr 360px;
        gap: 1.5rem;
    }
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
    }
    .detail-box {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 1.2rem;
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
        background: white;
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
    <a href="{{ route('admin.pending-drivers') }}" class="btn btn-outline-secondary" style="margin-bottom: 1rem;">← Back to pending drivers</a>

    <div class="review-card">
        <div class="review-header">
            <div>
                <h1>{{ $driver->user->name }}</h1>
                <p style="opacity: .85; margin: .5rem 0 0;">Driver profile submitted by {{ $driver->user->email }}</p>
            </div>
            <div class="status-pill {{ $driver->approval_status }}">{{ ucfirst($driver->approval_status) }}</div>
        </div>

        <div style="padding: .85rem 2rem; background:#f8fafc; border-bottom:1px solid #e6eefc; display:flex;gap:1rem;flex-wrap:wrap;align-items:center">
            <div class="small" style="color:#374151;font-weight:700">Driver ID: {{ $driver->id }}</div>
            <div class="small" style="color:#374151">User ID: {{ $driver->user_id }}</div>
            <div class="small" style="color:#374151">Guarantor 1 relationship: {{ $driver->relationship ?? '—' }}</div>
            <div class="small" style="color:#374151">Guarantor 2 relationship: {{ $driver->second_relationship ?? '—' }}</div>
            <div class="small" style="color:#374151">Onboarded: {{ $driver->user->onboarding_completed ? 'Yes' : 'No' }} @if($driver->user->onboarded_at) · {{ Carbon::parse($driver->user->onboarded_at)->toDateTimeString() }} @endif</div>
            <div class="small" style="color:#374151">Profile image: @if($driver->profile_image) <a href="{{ route('admin.documents.show', ['type' => 'driver', 'id' => $driver->id, 'field' => 'profile_image']) }}" target="_blank">{{ Str::afterLast($driver->profile_image,'/') }}</a> @else — @endif</div>
            <div class="small" style="color:#374151">Profile picture: @if($driver->profile_picture) <a href="{{ route('admin.documents.show', ['type' => 'driver', 'id' => $driver->id, 'field' => 'profile_picture']) }}" target="_blank">{{ Str::afterLast($driver->profile_picture,'/') }}</a> @else — @endif</div>
        </div>

        @if($driver->pending_application_changes)
            @php
                $pendingDriverLabels = [
                    'license_number' => 'License number',
                    'license_expiry' => 'License expiry',
                    'license_class' => 'License class',
                    'experience_years' => 'Experience (years)',
                    'status' => 'Availability',
                    'bio' => 'Bio',
                    'profile_picture' => 'Profile picture',
                ];
            @endphp
            <div class="detail-box m-3 border border-warning">
                <div class="detail-label">Driver application changes awaiting review</div>
                <ul class="mb-3">
                    @foreach($driver->pending_application_changes as $field => $value)
                        <li>
                            <strong>{{ $pendingDriverLabels[$field] ?? $field }}:</strong>
                            @if($field === 'profile_picture')
                                <a href="{{ route('admin.application-changes.documents.show', ['type' => 'driver', 'id' => $driver->id, 'field' => $field]) }}" target="_blank" rel="noopener">View submitted photo</a>
                            @else
                                {{ is_scalar($value) ? ($value === '' ? '—' : $value) : json_encode($value) }}
                            @endif
                        </li>
                    @endforeach
                </ul>
                <div class="d-flex flex-wrap gap-2">
                    <form action="{{ route('admin.drivers.application-changes.approve', $driver) }}" method="POST" onsubmit="return confirm('Approve these driver application changes?')">
                        @csrf
                        <button type="submit" class="btn btn-success">Approve changes</button>
                    </form>
                    <form action="{{ route('admin.drivers.application-changes.reject', $driver) }}" method="POST" onsubmit="return confirm('Reject these driver application changes?')">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">Reject changes</button>
                    </form>
                </div>
            </div>
        @endif

        <div class="review-body">
            <div>
                <div class="detail-grid">
                    <div class="detail-box">
                        <div class="detail-label">Full name</div>
                        <div class="detail-value">{{ $driver->user->name ?? '—' }}</div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">Email</div>
                        <div class="detail-value">{{ $driver->user->email ?? '—' }}</div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">Phone</div>
                        <div class="detail-value">{{ $driver->user->phone ?? '—' }}</div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">Address</div>
                        <div class="detail-value">{{ $driver->user->address ?? '—' }}</div>
                    </div>
                </div>

                <div class="detail-grid" style="margin-top: .5rem;">
                    <div class="detail-box">
                        <div class="detail-label">Date of birth</div>
                        <div class="detail-value">{{ $driver->dob ? \Carbon\Carbon::parse($driver->dob)->toDateString() : '—' }}</div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">License number</div>
                        <div class="detail-value">{{ $driver->license_number ?? '—' }}</div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">License class</div>
                        <div class="detail-value">{{ $driver->license_class ?? '—' }}</div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">License expiry</div>
                        <div class="detail-value">{{ $driver->license_expiry ? $driver->license_expiry->toDateString() : '—' }}</div>
                    </div>
                </div>

                <div class="detail-grid" style="margin-top: .5rem;">
                    <div class="detail-box">
                        <div class="detail-label">Experience</div>
                        <div class="detail-value">{{ $driver->experience_years ?? '—' }} years</div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">Guarantor 1</div>
                        <div class="detail-value">{{ $driver->guarantor ?? '—' }} — {{ $driver->guarantor_phone ?? '—' }}</div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">Guarantor 2</div>
                        <div class="detail-value">{{ $driver->second_guarantor ?? '—' }} — {{ $driver->second_guarantor_phone ?? '—' }}</div>
                    </div>
                </div>

                <div class="detail-box" style="margin-top: .5rem;">
                    <div class="detail-label">Bio</div>
                    <div class="detail-value">{{ $driver->bio ?? 'No biography provided.' }}</div>
                </div>

                <div class="detail-grid" style="margin-top: .5rem;">
                    <div class="detail-box">
                        <div class="detail-label">Ghana card</div>
                        <div class="detail-value">{{ $driver->ghana_card_no ?? $driver->g_ghana_card_no ?? '—' }}</div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">Payment</div>
                        <div class="detail-value">{{ ucfirst($driver->payment_status ?? '—') }} @if($driver->payment_reference) · {{ $driver->payment_reference }} @endif</div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">Status</div>
                        <div class="detail-value">{{ ucfirst($driver->status ?? '—') }}</div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">Approval</div>
                        <div class="detail-value">{{ ucfirst($driver->approval_status ?? 'pending') }} @if($driver->approved_at) · Approved at {{ $driver->approved_at->toDateTimeString() }} @endif</div>
                    </div>
                </div>

                <div class="detail-grid" style="margin-top: .5rem;">
                    <div class="detail-box">
                        <div class="detail-label">Rating</div>
                        <div class="detail-value">{{ $driver->rating ?? 0 }} ({{ $driver->total_trips ?? 0 }} trips)</div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">Location</div>
                        <div class="detail-value">{{ $driver->latitude && $driver->longitude ? $driver->latitude . ', ' . $driver->longitude : 'Not available' }} @if($driver->last_location_update) · Updated {{ $driver->last_location_update->diffForHumans() }} @endif</div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">Created</div>
                        <div class="detail-value">{{ $driver->created_at ? $driver->created_at->toDateTimeString() : '—' }}</div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">Last updated</div>
                        <div class="detail-value">{{ $driver->updated_at ? $driver->updated_at->toDateTimeString() : '—' }}</div>
                    </div>
                </div>

                <div class="detail-box" style="margin-top: .5rem;">
                    <div class="detail-label">Rejection reason</div>
                    <div class="detail-value">{{ $driver->rejection_reason ?? '—' }}</div>
                </div>
            </div>

            <aside>
                <div class="detail-box" style="text-align:center;">
                    @if($driver->getProfilePictureUrl())
                        <img src="{{ route('admin.documents.show', ['type' => 'driver', 'id' => $driver->id, 'field' => $driver->profile_picture ? 'profile_picture' : 'profile_image']) }}" alt="{{ $driver->user->name }} profile picture" style="width:200px;height:200px;object-fit:cover;border-radius:12px;border:1px solid #e6eefc">
                    @elseif($driver->profile_image)
                        <img src="{{ route('admin.documents.show', ['type' => 'driver', 'id' => $driver->id, 'field' => 'profile_image']) }}" alt="photo" style="width:200px;height:200px;object-fit:cover;border-radius:12px;border:1px solid #e6eefc">
                    @else
                        <div style="width:200px;height:200px;border-radius:12px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;font-size:3rem;color:#64748b">{{ Str::substr($driver->user->name,0,1) }}</div>
                    @endif
                    <div style="margin-top:.5rem;font-weight:700">{{ $driver->user->name }}</div>
                    <div class="small" style="color:#6b7280">{{ $driver->user->phone ?? '—' }}</div>
                </div>

                <div class="detail-box" style="margin-top:1rem;">
                    <div class="detail-label">Uploaded documents</div>
                    @php
                        $driverDocuments = [
                            'License' => $driver->license_file,
                            'Police Criminal Background Check' => $driver->police_check_file,
                            'Ghana Card' => $driver->ghana_card_file,
                            'Passport' => $driver->passport_file,
                            'Guarantor 1 ID' => $driver->guarantor_ghana_card_file,
                            'Guarantor 1 Passport Photo' => $driver->guarantor_passport_file,
                            'Guarantor 2 ID' => $driver->second_guarantor_ghana_card_file,
                            'Guarantor 2 Passport Photo' => $driver->second_guarantor_passport_file,
                        ];
                        $driverDocumentFields = [
                            'License' => 'license_file',
                            'Police Criminal Background Check' => 'police_check_file',
                            'Ghana Card' => 'ghana_card_file',
                            'Passport' => 'passport_file',
                            'Guarantor 1 ID' => 'guarantor_ghana_card_file',
                            'Guarantor 1 Passport Photo' => 'guarantor_passport_file',
                            'Guarantor 2 ID' => 'second_guarantor_ghana_card_file',
                            'Guarantor 2 Passport Photo' => 'second_guarantor_passport_file',
                        ];
                    @endphp
                    <ul class="documents-list">
                        @foreach($driverDocuments as $label => $path)
                            @if($path)
                                <li>
                                    <span>{{ $label }}</span>
                                    <a href="{{ route('admin.documents.show', ['type' => 'driver', 'id' => $driver->id, 'field' => $driverDocumentFields[$label]]) }}" target="_blank">View</a>
                                </li>
                            @endif
                        @endforeach
                        @if($driver->police_check_consent_at)
                            <li>
                                <span>Police check consent</span>
                                <strong>Recorded {{ $driver->police_check_consent_at->format('M j, Y g:i A') }}</strong>
                            </li>
                        @endif
                        @if($driver->getProfilePictureUrl())
                            <li>
                                <span>Photo</span>
                                <a href="{{ route('admin.documents.show', ['type' => 'driver', 'id' => $driver->id, 'field' => $driver->profile_picture ? 'profile_picture' : 'profile_image']) }}" target="_blank">View</a>
                            </li>
                        @endif
                    </ul>
                </div>

                <div class="detail-box" style="margin-top:1rem;">
                    <div class="detail-label">Schedule Driving Test</div>
                    @if($driver->drive_test_appointment_date)
                        <p class="mb-3">Current appointment: <strong>{{ $driver->drive_test_appointment_date->format('F j, Y') }}</strong></p>
                    @else
                        <p class="text-muted mb-3">No driving test appointment has been issued.</p>
                    @endif
                    <form action="{{ route('admin.drivers.drive-test.schedule', $driver) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <label for="drive_test_appointment_date" class="form-label">Driving test date</label>
                        <input
                            id="drive_test_appointment_date"
                            name="drive_test_appointment_date"
                            type="date"
                            class="form-control"
                            min="{{ now()->toDateString() }}"
                            value="{{ old('drive_test_appointment_date', $driver->drive_test_appointment_date?->toDateString()) }}"
                            required
                        >
                        @error('drive_test_appointment_date')<div class="text-danger small">{{ $message }}</div>@enderror
                        <button type="submit" class="btn btn-primary w-100 mt-3">
                            {{ $driver->drive_test_appointment_date ? 'Update test date' : 'Issue test date' }}
                        </button>
                    </form>
                    <p class="small text-muted mt-2 mb-0">The driver will receive an in-app notification and email.</p>
                </div>

                <div class="detail-box" style="margin-top:1rem;">
                    <div class="detail-label">Driver vetting checklist</div>
                    <p class="small text-muted">Mark each item complete after reviewing the evidence or recording the screening result. Saving this checklist does not approve or reject the driver.</p>
                    <form action="{{ route('admin.drivers.vetting-checks.update', $driver) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="d-grid gap-2">
                            @foreach(\App\Models\Driver::VETTING_CHECKS as $key => $label)
                                <label class="form-check d-flex gap-2 align-items-start">
                                    <input
                                        class="form-check-input mt-1"
                                        type="checkbox"
                                        name="checks[{{ $key }}]"
                                        value="1"
                                        @checked(old("checks.$key", $driver->vetting_checks[$key] ?? false))
                                    >
                                    <span class="small">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mt-3">Save vetting checklist</button>
                    </form>
                </div>

                <div class="detail-box" style="margin-top:1rem;">
                    <div class="detail-label">Actions</div>
                    <div style="display:flex;flex-direction:column;gap:.5rem;margin-top:.75rem;">
                        <form action="{{ route('admin.drivers.approve', $driver) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-action btn-approve" style="width:100%">Approve driver</button>
                        </form>
                        <button class="btn-action btn-reject" data-bs-toggle="modal" data-bs-target="#rejectDriverModal" style="width:100%">Reject driver</button>
                        <a href="{{ route('admin.pending-drivers') }}" class="btn btn-outline-secondary" style="width:100%">Back</a>
                        <button onclick="window.print()" class="btn btn-outline-primary" style="width:100%">Print</button>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <div class="modal fade" id="rejectDriverModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Driver Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.drivers.reject', $driver) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="rejection_reason" class="form-label">Reason for Rejection</label>
                            <textarea id="rejection_reason" name="rejection_reason" class="form-control" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Driver</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
