@extends('layouts.admin')

@section('title', 'Pending Driver Approvals')

@section('content')
@php use Illuminate\Support\Str; @endphp
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
    .drivers-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    .driver-approval-card {
        background: white;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 12px 40px rgba(15, 23, 42, 0.08);
        border: 1px solid rgba(15, 23, 42, 0.06);
    }
    .driver-card-header {
        background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
        color: white;
        padding: 1.5rem;
    }
    .driver-card-header h5 {
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
    .driver-card-body {
        padding: 1.5rem;
    }
    .driver-info-row {
        display: grid;
        grid-template-columns: 140px 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
        font-size: 0.95rem;
    }
    .driver-info-row strong {
        color: #6b7280;
        font-weight: 600;
    }
    .driver-info-row .value {
        color: #111827;
        font-weight: 500;
    }
    .driver-docs {
        margin: 1.5rem 0;
        padding: 1rem;
        background: #f9fafb;
        border-radius: 12px;
    }
    .driver-docs h6 {
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
        color: #0284c7;
        text-decoration: none;
    }
    .doc-links a:hover {
        border-color: #d1d5db;
        background: #f3f4f6;
    }
    .driver-actions {
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
            <h2><i class="fas fa-check-circle"></i> Pending Driver Approvals</h2>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>

        @if($drivers->count() === 0)
            <div style="background: white; padding: 3rem 2rem; border-radius: 18px; text-align: center; box-shadow: 0 12px 40px rgba(15, 23, 42, 0.08);">
                <i class="fas fa-check-circle" style="font-size: 3rem; color: #10b981; margin-bottom: 1rem; display: block;"></i>
                <p style="color: #10b981; font-weight: 600; margin: 0; font-size: 1.1rem;">All pending drivers have been processed</p>
            </div>
        @else
            <div class="drivers-grid">
                @foreach($drivers as $driver)
                    <div class="driver-approval-card">
                        <div class="driver-card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><i class="fas fa-user-check"></i> {{ $driver->user->name }}</h5>
                                <div class="small" style="opacity:.9">{{ $driver->user->address ?? '' }} · Joined {{ optional($driver->created_at)->toDateString() }}</div>
                            </div>
                            <div class="text-end">
                                @if($driver->getProfilePictureUrl())
                                    <img src="{{ route('admin.documents.show', ['type' => 'driver', 'id' => $driver->id, 'field' => $driver->profile_picture ? 'profile_picture' : 'profile_image']) }}" alt="{{ $driver->user->name }} profile picture" style="height:72px;width:72px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,.8)">
                                @elseif($driver->profile_image)
                                    <img src="{{ route('admin.documents.show', ['type' => 'driver', 'id' => $driver->id, 'field' => 'profile_image']) }}" alt="photo" style="height:56px;width:56px;border-radius:8px;object-fit:cover;border:2px solid rgba(255,255,255,.12)">
                                @else
                                    <div style="height:56px;width:56px;border-radius:8px;background:#eef2ff;display:flex;align-items:center;justify-content:center;color:#4f46e5;font-weight:700;">{{ Str::substr($driver->user->name,0,1) }}</div>
                                @endif
                                <div class="pending-badge mt-2">⏳ Pending Review</div>
                            </div>
                        </div>
                        <div class="driver-card-body">
                            <div class="driver-info-row">
                                <strong>Application ID:</strong>
                                <span class="value">{{ $driver->application_id ?: $driver->id }}</span>
                            </div>
                            <div class="driver-info-row">
                                <strong>Email:</strong>
                                <span class="value">{{ $driver->user->email }}</span>
                            </div>
                            <div class="driver-info-row">
                                <strong>Phone:</strong>
                                <span class="value">{{ $driver->user->phone ?? 'N/A' }}</span>
                            </div>
                            <div class="driver-info-row">
                                <strong>License:</strong>
                                <span class="value">{{ $driver->license_number }}</span>
                            </div>
                            <div class="driver-info-row">
                                <strong>License expiry:</strong>
                                <span class="value">{{ $driver->license_expiry ? $driver->license_expiry->toDateString() : 'N/A' }}</span>
                            </div>
                            <div class="driver-info-row">
                                <strong>Class:</strong>
                                <span class="value">{{ $driver->license_class }}</span>
                            </div>
                            <div class="driver-info-row">
                                <strong>DOB:</strong>
                                <span class="value">{{ $driver->dob ? \Carbon\Carbon::parse($driver->dob)->toDateString() : 'N/A' }}</span>
                            </div>
                            <div class="driver-info-row">
                                <strong>Experience:</strong>
                                <span class="value">{{ $driver->experience_years }} years</span>
                            </div>
                            <div class="driver-info-row">
                                <strong>Ghana Card:</strong>
                                <span class="value">{{ $driver->ghana_card_no ?? ($driver->g_ghana_card_no ?? 'N/A') }}</span>
                            </div>
                            <div class="driver-info-row">
                                <strong>Payment Ref:</strong>
                                <span class="value">{{ $driver->payment_reference ?? 'N/A' }}</span>
                            </div>
                            <div class="driver-info-row">
                                <strong>Approval status:</strong>
                                <span class="value">{{ ucfirst($driver->approval_status ?? 'pending') }}</span>
                            </div>
                            @if($driver->guarantor)
                                <div class="driver-info-row">
                                    <strong>Guarantor:</strong>
                                    <span class="value">{{ $driver->guarantor }}</span>
                                </div>
                            @endif

                            <div class="driver-docs">
                                <h6>📎 Uploaded Documents</h6>
                                <div class="doc-links">
                                    @if($driver->license_file)
                                        <a href="{{ route('admin.documents.show', ['type' => 'driver', 'id' => $driver->id, 'field' => 'license_file']) }}" target="_blank">📄 License</a>
                                    @endif
                                    @if($driver->ghana_card_file)
                                        <a href="{{ route('admin.documents.show', ['type' => 'driver', 'id' => $driver->id, 'field' => 'ghana_card_file']) }}" target="_blank">🪪 Ghana Card</a>
                                    @endif
                                    @if($driver->passport_file)
                                        <a href="{{ route('admin.documents.show', ['type' => 'driver', 'id' => $driver->id, 'field' => 'passport_file']) }}" target="_blank">📕 Passport</a>
                                    @endif
                                    @if($driver->guarantor_ghana_card_file)
                                        <a href="{{ route('admin.documents.show', ['type' => 'driver', 'id' => $driver->id, 'field' => 'guarantor_ghana_card_file']) }}" target="_blank">🪪 Guarantor ID</a>
                                    @endif
                                    @if($driver->guarantor_passport_file)
                                        <a href="{{ route('admin.documents.show', ['type' => 'driver', 'id' => $driver->id, 'field' => 'guarantor_passport_file']) }}" target="_blank">📷 Guarantor Passport</a>
                                    @endif
                                    @if($driver->second_guarantor_ghana_card_file)
                                        <a href="{{ route('admin.documents.show', ['type' => 'driver', 'id' => $driver->id, 'field' => 'second_guarantor_ghana_card_file']) }}" target="_blank">🪪 Second Guarantor ID</a>
                                    @endif
                                    @if($driver->second_guarantor_passport_file)
                                        <a href="{{ route('admin.documents.show', ['type' => 'driver', 'id' => $driver->id, 'field' => 'second_guarantor_passport_file']) }}" target="_blank">📷 Second Guarantor Passport</a>
                                    @endif
                                    @if($driver->getProfilePictureUrl())
                                        <a href="{{ route('admin.documents.show', ['type' => 'driver', 'id' => $driver->id, 'field' => $driver->profile_picture ? 'profile_picture' : 'profile_image']) }}" target="_blank">🖼️ Photo</a>
                                    @endif
                                </div>
                            </div>

                            @if($driver->bio)
                                <div style="margin-top: .75rem; padding: .75rem; background:#fff7ed;border-radius:10px;">
                                    <strong class="small">Bio</strong>
                                    <div class="small mt-1">{{ $driver->bio }}</div>
                                </div>
                            @endif

                            <div class="driver-info-row mt-2">
                                <strong>Emergency:</strong>
                                <span class="value">{{ $driver->emergency_person ?? 'N/A' }} — {{ $driver->emergency_phone ?? 'N/A' }}</span>
                            </div>

                            <div style="margin: 1rem 0;">
                                <a href="{{ route('admin.drivers.detail', $driver) }}" class="btn btn-outline-primary w-100">View full profile</a>
                            </div>

                            <div class="driver-actions">
                                <form action="{{ route('admin.drivers.approve', $driver) }}" method="POST" style="width: 100%;">
                                    @csrf
                                    <button type="submit" class="approve-btn w-100">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>
                                <button type="button" class="reject-btn" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $driver->id }}">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Reject Modal -->
                    <div class="modal fade" id="rejectModal{{ $driver->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Reject Driver Profile</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('admin.drivers.reject', $driver) }}" method="POST">
                                    @csrf
                                    <div class="modal-body">
                                        <label for="rejection_reason" class="form-label">Reason for Rejection</label>
                                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" required></textarea>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger">Reject Profile</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $drivers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
