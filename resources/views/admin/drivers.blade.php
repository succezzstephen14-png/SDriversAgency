@extends('layouts.admin')

@section('title', 'Driver Management - SUCCEZZ Drivers Agency')

@section('page-title', 'Driver Management')
@section('page-description', 'Manage driver profiles, approvals, and tracking information')

@section('styles')
<style>
    .drivers-wrapper {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        min-height: calc(100vh - 80px);
        padding: 2rem 0;
    }
    
    .drivers-header {
        background: white;
        padding: 2rem;
        border-radius: 20px;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .drivers-header h2 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 800;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .drivers-header-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    
    .drivers-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .driver-stat {
        background: white;
        padding: 1.5rem;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        border-left: 4px solid;
    }
    
    .driver-stat.total { border-left-color: #667eea; }
    .driver-stat.approved { border-left-color: #10b981; }
    .driver-stat.pending { border-left-color: #f59e0b; }
    .driver-stat.available { border-left-color: #06b6d4; }
    
    .driver-stat-value {
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
    }
    
    .driver-stat-label {
        font-size: 0.9rem;
        color: #6b7280;
        font-weight: 600;
    }
    
    .drivers-filters {
        background: white;
        padding: 1.5rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
    }
    
    .drivers-filters input,
    .drivers-filters select {
        padding: 0.75rem 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }
    
    .drivers-filters input:focus,
    .drivers-filters select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .drivers-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .driver-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .driver-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
    }
    
    .driver-card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
    
    .driver-name-section {
        flex: 1;
    }
    
    .driver-name {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    
    .driver-email {
        font-size: 0.85rem;
        opacity: 0.9;
    }
    
    .driver-status-badge {
        display: inline-block;
        padding: 0.35rem 0.8rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
    }
    
    .driver-card-body {
        padding: 1.5rem;
    }
    
    .driver-info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
        font-size: 0.9rem;
    }
    
    .driver-info-label {
        font-weight: 600;
        color: #6b7280;
    }
    
    .driver-info-value {
        color: #1f2937;
        font-weight: 500;
    }
    
    .driver-documents {
        display: flex;
        gap: 0.5rem;
        margin: 1rem 0;
        padding: 1rem;
        background: #f9fafb;
        border-radius: 10px;
        flex-wrap: wrap;
    }
    
    .doc-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 0.8rem;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        font-size: 0.8rem;
        color: #4b5563;
        font-weight: 600;
    }
    
    .doc-badge:hover {
        border-color: #667eea;
        color: #667eea;
    }
    
    .driver-card-footer {
        padding: 1rem;
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .driver-action-btn {
        flex: 1;
        min-width: 80px;
        padding: 0.6rem 0.8rem;
        border: none;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
    }
    
    .driver-action-btn.primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .driver-action-btn.success {
        background: #d1fae5;
        color: #065f46;
    }
    
    .driver-action-btn.danger {
        background: #fee2e2;
        color: #991b1b;
    }
    
    .driver-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .empty-state {
        grid-column: 1 / -1;
        background: white;
        padding: 3rem;
        border-radius: 16px;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }
    
    .empty-state-icon {
        font-size: 3.5rem;
        color: #d1d5db;
        margin-bottom: 1rem;
    }
    
    .empty-state-text {
        color: #6b7280;
        font-size: 1.1rem;
    }
    
    .pagination-wrapper {
        background: white;
        padding: 1.5rem;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }

    .driver-profile-photo {
        width: 88px;
        height: 88px;
        margin-bottom: .75rem;
        border: 3px solid #e5e7eb;
        border-radius: 50%;
        object-fit: cover;
    }

    .driver-profile-photo-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #eef2ff;
        color: #4f46e5;
        font-size: 2rem;
        font-weight: 700;
    }
</style>
@endsection

@section('content')
<div class="drivers-wrapper">
    <div class="container-max">
        <!-- Header -->
        <div class="drivers-header">
            <div>
                <h2><i class="fas fa-users"></i> Driver Management</h2>
            </div>
            <div class="drivers-header-actions">
                <button onclick="openExportModal()" class="btn btn-outline-primary">
                    <i class="fas fa-download"></i> Export
                </button>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-soft">
                    <i class="fas fa-arrow-left"></i> Dashboard
                </a>
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="drivers-stats">
            <div class="driver-stat total">
                <div class="driver-stat-value">{{ $drivers->total() }}</div>
                <div class="driver-stat-label">Total Drivers</div>
            </div>
            <div class="driver-stat approved">
                <div class="driver-stat-value">{{ $drivers->where('approval_status', 'approved')->count() }}</div>
                <div class="driver-stat-label">Approved</div>
            </div>
            <div class="driver-stat pending">
                <div class="driver-stat-value">{{ $drivers->where('approval_status', 'pending')->count() }}</div>
                <div class="driver-stat-label">Pending Approval</div>
            </div>
            <div class="driver-stat available">
                <div class="driver-stat-value">{{ $drivers->where('status', 'available')->count() }}</div>
                <div class="driver-stat-label">Available</div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="drivers-filters">
            <input type="text" id="driverSearch" placeholder="Search drivers..." style="flex: 1; min-width: 200px;">
            <select id="statusFilter" style="min-width: 130px;">
                <option value="">All Status</option>
                <option value="approved">Approved</option>
                <option value="pending">Pending</option>
                <option value="rejected">Rejected</option>
            </select>
            <select id="availabilityFilter" style="min-width: 130px;">
                <option value="">All Availability</option>
                <option value="available">Available</option>
                <option value="unavailable">Unavailable</option>
                <option value="on_duty">On Duty</option>
            </select>
            <button onclick="clearFilters()" class="btn btn-outline-secondary">
                <i class="fas fa-times"></i> Clear
            </button>
        </div>
        
        <!-- Drivers Grid -->
        <div class="drivers-grid">
            @forelse($drivers as $driver)
            <div class="driver-card">
                <div class="driver-card-header">
                    <div class="driver-name-section">
                        @if($driver->getProfilePictureUrl())
                            <img
                                src="{{ route('admin.documents.show', ['type' => 'driver', 'id' => $driver->id, 'field' => $driver->profile_picture ? 'profile_picture' : 'profile_image']) }}"
                                alt="{{ $driver->user?->name ?? 'Driver' }} profile picture"
                                class="driver-profile-photo"
                            >
                        @else
                            <div class="driver-profile-photo driver-profile-photo-placeholder" role="img" aria-label="No profile picture uploaded">
                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($driver->user?->name ?? 'D', 0, 1)) }}
                            </div>
                        @endif
                        <div class="driver-name">{{ $driver->user?->name ?? 'Unknown Driver' }}</div>
                        <div class="driver-email">{{ $driver->user?->email ?? 'No email' }}</div>
                    </div>
                    <span class="driver-status-badge">
                        @if($driver->approval_status === 'approved')
                            <i class="fas fa-check-circle"></i> Approved
                        @elseif($driver->approval_status === 'pending')
                            <i class="fas fa-clock"></i> Pending
                        @else
                            <i class="fas fa-times-circle"></i> Rejected
                        @endif
                    </span>
                    @if($driver->pending_application_changes)
                        <span class="badge bg-warning text-dark">Changes awaiting review</span>
                    @endif
                </div>
                
                <div class="driver-card-body">
                    <div class="driver-info-row">
                        <span class="driver-info-label">License:</span>
                        <span class="driver-info-value">{{ $driver->license_number ?? 'N/A' }}</span>
                    </div>
                    <div class="driver-info-row">
                        <span class="driver-info-label">Class:</span>
                        <span class="driver-info-value">{{ $driver->license_class ?? 'N/A' }}</span>
                    </div>
                    <div class="driver-info-row">
                        <span class="driver-info-label">Experience:</span>
                        <span class="driver-info-value">{{ $driver->experience_years ?? 0 }} years</span>
                    </div>
                    <div class="driver-info-row">
                        <span class="driver-info-label">Availability:</span>
                        <span class="driver-info-value">
                            @if($driver->status === 'available')
                                <i class="fas fa-circle text-success"></i> Available
                            @elseif($driver->status === 'on_duty')
                                <i class="fas fa-circle text-warning"></i> On Duty
                            @else
                                <i class="fas fa-circle text-secondary"></i> Unavailable
                            @endif
                        </span>
                    </div>
                    <div class="driver-info-row">
                        <span class="driver-info-label">Approved:</span>
                        <span class="driver-info-value">
                            @if($driver->approved_at)
                                {{ $driver->approved_at->format('M d, Y') }}
                            @else
                                Not yet
                            @endif
                        </span>
                    </div>
                    
                    @if($driver->license_file || $driver->police_check_file || $driver->ghana_card_file || $driver->passport_file)
                    <div class="driver-documents">
                        @if($driver->license_file)
                            <a href="{{ route('admin.documents.show', ['type' => 'driver', 'id' => $driver->id, 'field' => 'license_file']) }}" target="_blank" class="doc-badge">
                                <i class="fas fa-file-pdf"></i> License
                            </a>
                        @endif
                        @if($driver->police_check_file)
                            <a href="{{ route('admin.documents.show', ['type' => 'driver', 'id' => $driver->id, 'field' => 'police_check_file']) }}" target="_blank" class="doc-badge">
                                <i class="fas fa-shield-alt"></i> Police Background Check
                            </a>
                        @endif
                        @if($driver->ghana_card_file)
                            <a href="{{ route('admin.documents.show', ['type' => 'driver', 'id' => $driver->id, 'field' => 'ghana_card_file']) }}" target="_blank" class="doc-badge">
                                <i class="fas fa-id-card"></i> Ghana Card
                            </a>
                        @endif
                        @if($driver->passport_file)
                            <a href="{{ route('admin.documents.show', ['type' => 'driver', 'id' => $driver->id, 'field' => 'passport_file']) }}" target="_blank" class="doc-badge">
                                <i class="fas fa-passport"></i> Passport
                            </a>
                        @endif
                    </div>
                    @endif
                </div>
                
                <div class="driver-card-footer">
                    @if($driver->pending_application_changes)
                        <a href="{{ route('admin.drivers.detail', $driver) }}" class="driver-action-btn warning">
                            <i class="fas fa-clipboard-check"></i> Review changes
                        </a>
                    @endif
                    <button onclick="viewDriverDetails('{{ $driver->id }}')" class="driver-action-btn primary">
                        <i class="fas fa-eye"></i> View
                    </button>
                    <button onclick="editDriver('{{ $driver->id }}')" class="driver-action-btn primary" style="flex: 1; min-width: 80px;">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    @if($driver->approval_status === 'pending')
                        <form action="{{ route('admin.drivers.approve', $driver) }}" method="POST" style="flex: 1; min-width: 80px;" onsubmit="return confirm('Approve this driver?')">
                            @csrf
                            <button type="submit" class="driver-action-btn success" style="width: 100%;">
                                <i class="fas fa-check"></i> Approve
                            </button>
                        </form>
                        <form action="{{ route('admin.drivers.reject', $driver) }}" method="POST" style="flex: 1; min-width: 80px;" onsubmit="return confirm('Reject this driver?')">
                            @csrf
                            <button type="submit" class="driver-action-btn danger" style="width: 100%;">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            @empty
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="empty-state-text">No drivers found</h3>
                <p style="color: #9ca3af;">Driver profiles will appear here once users apply to become drivers.</p>
            </div>
            @endforelse
        </div>
        
        <!-- Pagination -->
        @if($drivers->hasPages())
        <div class="pagination-wrapper">
            {{ $drivers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

    <!-- Export Modal -->
    <div class="modal fade" id="exportDriversModal" tabindex="-1" aria-labelledby="exportDriversModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportDriversModalLabel">Export Drivers</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>The export is available only in PDF format. Click Export to download a PDF file of drivers.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="performDriversExport()">Export</button>
                </div>
            </div>
        </div>
    </div>

@section('scripts')
<script>
        function viewDriverDetails(id) {
        window.location.href = '/admin/drivers/' + id;
    }
    
    function editDriver(id) {
        window.location.href = '/admin/drivers/' + id + '/edit-admin';
    }
    
    function openExportModal() {
        const modalEl = document.getElementById('exportDriversModal');
        if (!modalEl) return;
        if (typeof bootstrap === 'undefined') {
            // Fallback: only PDF supported
            window.location.href = `{{ route('admin.drivers.export') }}?format=pdf`;
            return;
        }
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    function performDriversExport() {
        // Only PDF export supported
        const modalEl = document.getElementById('exportDriversModal');
        const modal = (typeof bootstrap !== 'undefined') ? bootstrap.Modal.getInstance(modalEl) : null;
        if (modal) modal.hide();
        window.location.href = `{{ route('admin.drivers.export') }}?format=pdf`;
    }
    
    function clearFilters() {
        document.getElementById('driverSearch').value = '';
        document.getElementById('statusFilter').value = '';
        document.getElementById('availabilityFilter').value = '';
    }
</script>
@endsection
