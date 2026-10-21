@extends('layouts.admin')

@section('title', 'User Management - SUCCEZZ Drivers Agency')

@section('page-title', 'User Management')
@section('page-description', 'Manage platform users, roles, and account information')

@section('content')
<div class="admin-row">
    <div class="admin-card" style="grid-column: span 12;">
        <div class="section-header">
            <div>
                <h3><i class="fas fa-users"></i> User Management</h3>
                <p>Comprehensive user administration and account management</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.users', ['profile_updates' => 1]) }}" class="btn {{ request()->boolean('profile_updates') ? 'btn-primary' : 'btn-outline-warning' }}">
                    <i class="fas fa-user-clock"></i>
                    Profile Updates Awaiting Review
                    <span class="badge text-bg-light ms-1">{{ $metrics['pending_profile_updates'] }}</span>
                </a>
                <button onclick="exportUsers()" class="btn btn-outline-primary">
                    <i class="fas fa-download"></i> Export Users
                </button>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-soft">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <!-- User Statistics -->
        <div class="admin-grid" style="margin-bottom: 2rem;">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-value">{{ $metrics['total_users'] }}</div>
                <div class="stat-label">Total Users</div>
                <div class="stat-note">All registered accounts</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="stat-value">{{ $metrics['total_admins'] }}</div>
                <div class="stat-label">Administrators</div>
                <div class="stat-note">Platform admins</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user"></i>
                </div>
                <div class="stat-value">{{ $metrics['total_regular_users'] }}</div>
                <div class="stat-label">Regular Users</div>
                <div class="stat-note">Standard accounts</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-value">{{ $metrics['completed_onboarding'] }}</div>
                <div class="stat-label">Completed Onboarding</div>
                <div class="stat-note">Fully set up accounts</div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="filters-section" style="margin-bottom: 2rem;">
            <div class="d-flex gap-3 align-items-center flex-wrap">
                <div class="search-box">
                    <input type="text" id="userSearch" class="form-control" placeholder="Search users by name or email..." style="min-width: 300px;">
                    <i class="fas fa-search search-icon"></i>
                </div>

                <select id="roleFilter" class="form-select" style="min-width: 150px;">
                    <option value="">All Roles</option>
                    <option value="admin">Administrators</option>
                    <option value="user">Users</option>
                </select>

                <select id="statusFilter" class="form-select" style="min-width: 150px;">
                    <option value="">All Status</option>
                    <option value="onboarded">Onboarded</option>
                    <option value="pending">Pending Setup</option>
                </select>

                <select id="accountTypeFilter" class="form-select" style="min-width: 150px;">
                    <option value="">All Account Types</option>
                    <option value="active">Active</option>
                    <option value="driver">Driver</option>
                    <option value="vehicle">Vehicle Owner</option>
                    <option value="both">Both Services</option>
                </select>

                <button onclick="clearFilters()" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Clear Filters
                </button>
            </div>
        </div>

        <style>
            .users-container {
                background: white;
                border-radius: 16px;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                overflow: hidden;
                margin-top: 2rem;
            }

            .users-table {
                width: 100%;
                border-collapse: collapse;
            }

            .users-table thead th {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 1.25rem 1rem;
                text-align: left;
                font-weight: 600;
                font-size: 0.9rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                border-bottom: none;
            }

            .users-table tbody tr {
                border-bottom: 1px solid #e5e7eb;
                transition: all 0.2s ease;
            }

            .users-table tbody tr:hover {
                background: #f8fafc;
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            }

            .users-table tbody td {
                padding: 1.25rem 1rem;
                vertical-align: middle;
            }

            .user-avatar {
                width: 48px;
                height: 48px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #e0f2fe;
                color: #0369a1;
                font-size: 1.2rem;
                font-weight: 700;
                margin-right: 1rem;
            }

            .user-avatar img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                border-radius: 12px;
            }

            .user-info {
                display: flex;
                align-items: center;
            }

            .user-details h4 {
                margin: 0;
                font-size: 1rem;
                color: #1f2937;
                font-weight: 600;
            }

            .user-details p {
                margin: 0.25rem 0 0;
                color: #6b7280;
                font-size: 0.875rem;
            }

            .role-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.375rem;
                padding: 0.375rem 0.75rem;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.025em;
            }

            .role-badge.admin {
                background: #fef3c7;
                color: #92400e;
            }

            .role-badge.user {
                background: #dbeafe;
                color: #1e40af;
            }

            .status-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.375rem;
                padding: 0.375rem 0.75rem;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 600;
            }

            .status-badge.completed {
                background: #dcfce7;
                color: #166534;
            }

            .status-badge.pending {
                background: #fef3c7;
                color: #92400e;
            }

            .action-buttons {
                display: flex;
                gap: 0.5rem;
                opacity: 0;
                transition: opacity 0.2s ease;
            }

            .users-table tbody tr:hover .action-buttons {
                opacity: 1;
            }

            .action-btn {
                padding: 0.5rem;
                border: none;
                border-radius: 8px;
                cursor: pointer;
                transition: all 0.2s ease;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 36px;
                height: 36px;
            }

            .action-btn:hover {
                transform: scale(1.1);
            }

            .action-btn.view {
                background: #dbeafe;
                color: #2563eb;
            }

            .action-btn.edit {
                background: #fef3c7;
                color: #d97706;
            }

            .action-btn.delete {
                background: #fee2e2;
                color: #dc2626;
            }

            .action-btn.message {
                background: #e0e7ff;
                color: #5b21b6;
            }

            .checkbox-cell {
                width: 40px;
            }

            .checkbox-cell input[type="checkbox"] {
                width: 18px;
                height: 18px;
                accent-color: #2563eb;
            }

            .empty-state {
                text-align: center;
                padding: 4rem 2rem;
                color: #6b7280;
            }

            .empty-state-icon {
                width: 80px;
                height: 80px;
                border-radius: 50%;
                background: #f3f4f6;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 1rem;
                color: #9ca3af;
                font-size: 2rem;
            }

            @media (max-width: 768px) {
                .users-table {
                    font-size: 0.875rem;
                }

                .users-table thead th,
                .users-table tbody td {
                    padding: 0.75rem 0.5rem;
                }

                .user-avatar {
                    width: 40px;
                    height: 40px;
                    font-size: 1rem;
                }

                .action-buttons {
                    opacity: 1;
                    flex-direction: column;
                    gap: 0.25rem;
                }

                .action-btn {
                    width: 32px;
                    height: 32px;
                }
            }
        </style>

        <div class="users-toolbar" style="margin-bottom: 2rem; display: flex; flex-wrap: wrap; gap: 1rem; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <div style="color: #64748b; font-size: 0.95rem;">
                    Showing <strong>{{ $users->count() ? $users->firstItem() : 0 }}</strong> to <strong>{{ $users->lastItem() ?? 0 }}</strong> of <strong>{{ $users->total() }}</strong> users
                </div>
            </div>
            <button onclick="clearFilters()" class="btn btn-outline-secondary">
                <i class="fas fa-times"></i> Clear Filters
            </button>
        </div>

        <div id="usersContainer" class="users-container">
            <table class="users-table">
                <thead>
                    <tr>
                        <th class="checkbox-cell">
                            <input type="checkbox" id="selectAll">
                        </th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr data-user-id="{{ $user->id }}">
                        <form id="deleteUserForm-{{ $user->id }}" action="{{ route('admin.users.delete', $user) }}" method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                        <td class="checkbox-cell">
                            <input type="checkbox" name="user_ids[]" class="user-checkbox" value="{{ $user->id }}">
                        </td>
                        <td>
                            <div class="user-info">
                                <div class="user-avatar">
                                    @if($user->profile_picture)
                                        <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="{{ $user->name }}">
                                    @else
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    @endif
                                </div>
                                <div class="user-details">
                                    <h4>{{ $user->name }}</h4>
                                    <p>{{ $user->email }}</p>
                                    @if($user->pending_profile_changes)
                                        <a href="{{ route('admin.users.edit', $user) }}" class="status-badge pending text-decoration-none">
                                            <i class="fas fa-user-clock"></i> Profile review needed
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="role-badge {{ $user->role }}">
                                <i class="fas fa-{{ $user->role === 'admin' ? 'user-shield' : 'user' }}"></i>
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td>
                            @if($user->onboarding_completed)
                                <span class="status-badge completed">
                                    <i class="fas fa-check-circle"></i> Onboarded
                                </span>
                            @else
                                <span class="status-badge pending">
                                    <i class="fas fa-clock"></i> Setup Pending
                                </span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ $user->created_at->format('M d, Y') }}</small>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button type="button" data-user-id="{{ $user->id }}" onclick="viewUserDetails({{ $user->id }})" class="action-btn view" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="{{ route('admin.users.edit', $user) }}" class="action-btn edit" title="Edit User">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" data-user-id="{{ $user->id }}" onclick="sendNotification({{ $user->id }})" class="action-btn message" title="Send Message">
                                    <i class="fas fa-bell"></i>
                                </button>
                                <button type="button" data-user-id="{{ $user->id }}" onclick="deleteUser({{ $user->id }})" class="action-btn delete" title="Delete User">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h4>No users found</h4>
                                <p>Try adjusting your search criteria or filters.</p>
                                <button onclick="clearFilters()" class="btn btn-primary">
                                    <i class="fas fa-times"></i> Clear Filters
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

                    <!-- Bulk Export Modal -->
                    <div class="modal fade" id="bulkExportModal" tabindex="-1" aria-labelledby="bulkExportModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="bulkExportModalLabel">Export Selected Users</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Export selected users as PDF. Click Export to generate a PDF file.</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-primary" onclick="performBulkExport()">Export</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bulk Notify Modal -->
                    <div class="modal fade" id="bulkNotifyModal" tabindex="-1" aria-labelledby="bulkNotifyModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="bulkNotifyModalLabel">Send Notification</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Enter the message to send to selected users:</p>
                                    <textarea id="bulkNotifyMessage" class="form-control" rows="4" placeholder="Type your message here..."></textarea>
                                    <div id="bulkNotifyError" style="color: #dc3545; margin-top: .5rem; font-size: .95rem;"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-primary" onclick="performBulkNotify()">Send</button>
                                </div>
                            </div>
                        </div>
                    </div>

        <!-- Bulk Actions -->
        <div class="bulk-actions mt-4" id="bulkActions" style="display: none; background: #f8fafc; border-radius: 12px; padding: 1.5rem; border: 2px solid #e2e8f0;">
            <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                <div style="font-weight: 600; color: #1f2937;">
                    <i class="fas fa-check-circle" style="color: #10b981;"></i>
                    <span id="selectedCount">0 users selected</span>
                </div>
                <div style="height: 24px; width: 2px; background: #e5e7eb;"></div>
                <button onclick="bulkChangeRole('admin')" class="btn btn-sm" style="background: #fbbf24; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px;" title="Make selected users administrators">
                    <i class="fas fa-user-shield"></i> Make Admins
                </button>
                <button onclick="bulkChangeRole('user')" class="btn btn-sm" style="background: #60a5fa; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px;" title="Demote selected users">
                    <i class="fas fa-user"></i> Make Users
                </button>
                <button onclick="bulkToggleStatus()" class="btn btn-sm" style="background: #10b981; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px;" title="Toggle status for selected users">
                    <i class="fas fa-toggle-on"></i> Toggle Status
                </button>
                <button onclick="openBulkNotifyModal()" class="btn btn-sm" style="background: #8b5cf6; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px;" title="Send notification to selected users">
                    <i class="fas fa-envelope"></i> Send Message
                </button>
                <button onclick="openBulkExportModal()" class="btn btn-sm" style="background: #06b6d4; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px;" title="Export selected users">
                    <i class="fas fa-download"></i> Export Selected
                </button>
                <button onclick="bulkDelete()" class="btn btn-sm" style="background: #ef4444; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px;" title="Delete selected users permanently">
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
            </div>
        </div>

        <!-- User Summary Section -->
        <div style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 12px; padding: 1.5rem; margin-top: 2rem;">
            <h5 style="margin-bottom: 1rem; font-weight: 700; color: #1f2937;"><i class="fas fa-chart-bar"></i> User Analytics Summary</h5>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                <div style="background: white; padding: 1.25rem; border-radius: 8px; border-left: 4px solid #3b82f6;">
                    <div style="font-size: 0.85rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">New Users (Last 30 Days)</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: #1f2937; margin-top: 0.5rem;">{{ $metrics['new_last_30_days'] }}</div>
                </div>
                <div style="background: white; padding: 1.25rem; border-radius: 8px; border-left: 4px solid #10b981;">
                    <div style="font-size: 0.85rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Active This Month</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: #1f2937; margin-top: 0.5rem;">{{ $metrics['active_this_month'] }}</div>
                </div>
                <div style="background: white; padding: 1.25rem; border-radius: 8px; border-left: 4px solid #f59e0b;">
                    <div style="font-size: 0.85rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Pending Onboarding</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: #1f2937; margin-top: 0.5rem;">{{ $metrics['pending_onboarding'] }}</div>
                </div>
                <div style="background: white; padding: 1.25rem; border-radius: 8px; border-left: 4px solid #8b5cf6;">
                    <div style="font-size: 0.85rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Complete Profiles</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: #1f2937; margin-top: 0.5rem;">{{ $metrics['completed_onboarding'] }}</div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        @if($users->total())
        <div style="margin-top: 2rem; padding: 1.5rem; background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div style="color: #64748b; font-size: 0.9rem;">
                    <strong>{{ $users->total() }}</strong> total users | 
                    Showing <strong>{{ $users->firstItem() }}</strong> to <strong>{{ $users->lastItem() }}</strong> of <strong>{{ $users->total() }}</strong>
                </div>
                @if($users->hasPages())
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
                    <a href="{{ $users->previousPageUrl() ?? '#' }}" class="btn btn-sm btn-outline-secondary {{ $users->onFirstPage() ? 'disabled' : '' }}" style="min-width: 110px; text-align: center; {{ $users->onFirstPage() ? 'pointer-events: none; opacity: 0.6;' : '' }}" aria-disabled="{{ $users->onFirstPage() ? 'true' : 'false' }}">
                        &laquo; Previous
                    </a>
                    <a href="{{ $users->nextPageUrl() ?? '#' }}" class="btn btn-sm btn-outline-secondary {{ !$users->hasMorePages() ? 'disabled' : '' }}" style="min-width: 110px; text-align: center; {{ !$users->hasMorePages() ? 'pointer-events: none; opacity: 0.6;' : '' }}" aria-disabled="{{ !$users->hasMorePages() ? 'true' : 'false' }}">
                        Next &raquo;
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- User Details Modal -->
<div class="modal fade" id="userDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="userDetailsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// ============================================================================
// USER MANAGEMENT INTERFACE JAVASCRIPT
// ============================================================================

// Initialize on page load
function initializeUserManagement() {
    console.log('Initializing user management scripts');
    attachButtonListeners();
    attachCheckboxListeners();
    setupFilterListeners();
    setupSearchListener();
    updateBulkActionsUI();
}

document.addEventListener('DOMContentLoaded', initializeUserManagement);
if (document.readyState !== 'loading') {
    initializeUserManagement();
}

function attachButtonListeners() {
    console.log('Attaching button listeners...');
    
    document.querySelectorAll('.action-btn.view').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            if (userId && typeof window.viewUserDetails === 'function') {
                window.viewUserDetails(userId);
            }
        });
        btn.removeAttribute('onclick');
    });
    
    document.querySelectorAll('.action-btn.message').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            if (userId && typeof window.sendNotification === 'function') {
                window.sendNotification(userId);
            }
        });
        btn.removeAttribute('onclick');
    });
    
    document.querySelectorAll('.action-btn.delete').forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            if (userId && typeof window.deleteUser === 'function') {
                window.deleteUser(userId);
            }
        });
        btn.removeAttribute('onclick');
    });
    
    console.log('Button listeners attached successfully');
}

// ============================================================================
// CHECKBOX AND SELECTION MANAGEMENT
// ============================================================================

function attachCheckboxListeners() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const userCheckboxes = document.querySelectorAll('input[name="user_ids[]"]');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            userCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActionsUI();
        });
    }
    
    userCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkActionsUI();
        });
    });
}

function updateBulkActionsUI() {
    const selectedCount = document.querySelectorAll('input[name="user_ids[]"]:checked').length;
    const bulkActionsDiv = document.getElementById('bulkActions');
    const selectedCountSpan = document.getElementById('selectedCount');
    
    if (selectedCount > 0) {
        bulkActionsDiv.style.display = 'block';
        selectedCountSpan.textContent = `${selectedCount} user${selectedCount !== 1 ? 's' : ''} selected`;
    } else {
        bulkActionsDiv.style.display = 'none';
        selectedCountSpan.textContent = '0 users selected';
    }
}

// ============================================================================
// INDIVIDUAL USER ACTIONS
// ============================================================================

function viewUserDetails(userId) {
    fetch(`/admin/users/${userId}/details`)
        .then(response => response.json())
        .then(data => {
            showDetailModal(data);
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Failed to load user details', 'error');
        });
}

function showDetailModal(user) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    `;
    
    const statusValue = user.status || (user.onboarding_completed ? 'onboarded' : 'pending');
    const modalContent = document.createElement('div');
    modalContent.style.cssText = `
        background: white;
        border-radius: 12px;
        padding: 2rem;
        max-width: 600px;
        width: 90%;
        box-shadow: 0 20px 25px rgba(0,0,0,0.15);
    `;
    
    modalContent.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1.5rem;">
            <h4 style="margin: 0; color: #1f2937; font-size: 1.25rem;">User Details</h4>
            <button onclick="this.closest('.modal-overlay').remove()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b;">×</button>
        </div>
        
        <div style="display: grid; gap: 1rem; margin-bottom: 1.5rem;">
            <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; border-left: 4px solid #3b82f6;">
                <div style="font-size: 0.8rem; color: #64748b; font-weight: 600;">Name</div>
                <div style="font-size: 1rem; font-weight: 600; color: #1f2937;">${user.name}</div>
            </div>
            
            <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; border-left: 4px solid #10b981;">
                <div style="font-size: 0.8rem; color: #64748b; font-weight: 600;">Email</div>
                <div style="font-size: 1rem; font-weight: 600; color: #1f2937;">${user.email}</div>
            </div>
            
            <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; border-left: 4px solid #f59e0b;">
                <div style="font-size: 0.8rem; color: #64748b; font-weight: 600;">Phone</div>
                <div style="font-size: 1rem; font-weight: 600; color: #1f2937;">${user.phone || 'Not provided'}</div>
            </div>
            
            <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; border-left: 4px solid #8b5cf6;">
                <div style="font-size: 0.8rem; color: #64748b; font-weight: 600;">Role</div>
                <div style="font-size: 1rem; font-weight: 600; color: #1f2937;">
                    <span style="background: ${user.role === 'admin' ? '#fef08a' : '#d1fae5'}; color: ${user.role === 'admin' ? '#92400e' : '#065f46'}; padding: 0.25rem 0.75rem; border-radius: 20px;">
                        ${user.role.toUpperCase()}
                    </span>
                </div>
            </div>
            
            <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; border-left: 4px solid #06b6d4;">
                <div style="font-size: 0.8rem; color: #64748b; font-weight: 600;">Status</div>
                <div style="font-size: 1rem; font-weight: 600; color: #1f2937;">
                    <span style="background: ${statusValue === 'onboarded' ? '#d1fae5' : '#fee2e2'}; color: ${statusValue === 'onboarded' ? '#065f46' : '#991b1b'}; padding: 0.25rem 0.75rem; border-radius: 20px;">
                        ${statusValue.toUpperCase()}
                    </span>
                </div>
            </div>
            
            <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; border-left: 4px solid #ec4899;">
                <div style="font-size: 0.8rem; color: #64748b; font-weight: 600;">Onboarding Status</div>
                <div style="font-size: 1rem; font-weight: 600; color: #1f2937;">
                    <span style="background: ${user.onboarding_completed ? '#d1fae5' : '#fef3c7'}; color: ${user.onboarding_completed ? '#065f46' : '#92400e'}; padding: 0.25rem 0.75rem; border-radius: 20px;">
                        ${user.onboarding_completed ? 'COMPLETED' : 'PENDING'}
                    </span>
                </div>
            </div>
            
            <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; border-left: 4px solid #14b8a6;">
                <div style="font-size: 0.8rem; color: #64748b; font-weight: 600;">Member Since</div>
                <div style="font-size: 1rem; font-weight: 600; color: #1f2937;">${new Date(user.created_at).toLocaleDateString()}</div>
            </div>
        </div>
        
        <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
            <button onclick="this.closest('.modal-overlay').remove()" style="background: #e5e7eb; color: #374151; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 600;">Close</button>
            <a href="/admin/users/${user.id}/edit" style="background: #3b82f6; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-weight: 600; text-decoration: none; display: inline-block;">Edit User</a>
        </div>
    `;
    
    modal.appendChild(modalContent);
    document.body.appendChild(modal);
    
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
        }
    });
}

function toggleUserStatus(userId) {
    showConfirmDialog('Are you sure you want to toggle this user\'s status?', (confirmed) => {
        if (!confirmed) return;
        
        fetch(`/admin/users/${userId}/toggle-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('User status toggled successfully', 'success');
            location.reload();
        } else {
            showNotification(data.message || 'Failed to toggle user status', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
    });
}

function openNotificationModal(userId) {
    closeNotificationModal();

    const modal = document.createElement('div');
    modal.id = 'notificationModal';
    modal.style.cssText = `
        position: fixed;
        inset: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        background: rgba(0, 0, 0, 0.5);
        z-index: 4000;
        padding: 1rem;
    `;

    modal.innerHTML = `
        <div style="background: #fff; border-radius: 12px; width: min(480px, 100%); box-shadow: 0 20px 60px rgba(0,0,0,0.2); overflow: hidden;">
            <div style="padding: 1rem 1.25rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h2 style="margin: 0; font-size: 1.1rem;">Send Notification</h2>
                    <p style="margin: 0.25rem 0 0; color: #6b7280; font-size: 0.95rem;">Send a message to this user.</p>
                </div>
                <button id="notificationModalClose" style="background: transparent; border: none; font-size: 1.25rem; cursor: pointer; color: #374151;">&times;</button>
            </div>
            <div style="padding: 1rem 1.25rem;">
                <textarea id="notificationMessageInput" placeholder="Enter your message here" style="width: 100%; min-height: 130px; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.75rem; font-size: 0.95rem; resize: vertical;"></textarea>
            </div>
            <div style="padding: 0 1.25rem 1rem; display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button id="notificationModalCancel" style="background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.75rem 1rem; cursor: pointer;">Cancel</button>
                <button id="notificationModalSend" style="background: #4f46e5; color: white; border: none; border-radius: 8px; padding: 0.75rem 1rem; cursor: pointer;">Send</button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    document.getElementById('notificationModalClose').addEventListener('click', closeNotificationModal);
    document.getElementById('notificationModalCancel').addEventListener('click', closeNotificationModal);
    document.getElementById('notificationModalSend').addEventListener('click', () => {
        const message = document.getElementById('notificationMessageInput').value.trim();
        if (!message) {
            showNotification('Please enter a message before sending.', 'warning');
            return;
        }

        fetch(`/admin/users/${userId}/notify`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ message })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Notification sent successfully', 'success');
                closeNotificationModal();
            } else {
                showNotification(data.message || 'Failed to send notification', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred', 'error');
        });
    });
}

function closeNotificationModal() {
    const existingModal = document.getElementById('notificationModal');
    if (existingModal) {
        existingModal.remove();
    }
}

function sendNotification(userId) {
    openNotificationModal(userId);
}

function deleteUser(userId) {
    showConfirmDialog('Are you sure you want to delete this user? This action cannot be undone!', (confirmed) => {
        if (!confirmed) return;
        
        const form = document.getElementById(`deleteUserForm-${userId}`);
        if (form) {
            form.submit();
        } else {
            // Fallback if form doesn't exist
            fetch(`/admin/users/${userId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => {
            if (response.ok) {
                showNotification('User deleted successfully', 'success');
                location.reload();
            } else {
                showNotification('Failed to delete user', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred', 'error');
        });
        }
    });
}


// ============================================================================
// BULK ACTIONS
// ============================================================================

function getSelectedUserIds() {
    return Array.from(document.querySelectorAll('input[name="user_ids[]"]:checked')).map(cb => cb.value);
}

function bulkChangeRole(role) {
    const selectedIds = getSelectedUserIds();
    if (selectedIds.length === 0) {
        showNotification('Please select at least one user', 'warning');
        return;
    }
    
    showConfirmDialog(`Are you sure you want to change the role of ${selectedIds.length} user(s) to '${role}'?`, (confirmed) => {
        if (!confirmed) return;
        
        fetch('/admin/users/bulk-role', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ user_ids: selectedIds, role: role })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`Role changed for ${selectedIds.length} user(s)`, 'success');
            location.reload();
        } else {
            showNotification(data.message || 'Failed to change roles', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
    });
}

function bulkToggleStatus() {
    const selectedIds = getSelectedUserIds();
    if (selectedIds.length === 0) {
        showNotification('Please select at least one user', 'warning');
        return;
    }
    
    showConfirmDialog(`Are you sure you want to toggle the status of ${selectedIds.length} user(s)?`, (confirmed) => {
        if (!confirmed) return;
    
    fetch('/admin/users/bulk-toggle-status', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ user_ids: selectedIds })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`Status toggled for ${selectedIds.length} user(s)`, 'success');
            location.reload();
        } else {
            showNotification(data.message || 'Failed to toggle statuses', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
    });
}

function bulkSendNotification() {
    const selectedIds = getSelectedUserIds();
    if (selectedIds.length === 0) {
        showNotification('Please select at least one user', 'warning');
        return;
    }
    // open bulk notify modal (uses selectedIds from page state)
    openBulkNotifyModal();
}

function bulkExport() {
    const selectedIds = getSelectedUserIds();
    if (selectedIds.length === 0) {
        showNotification('Please select at least one user', 'warning');
        return;
    }
    
    // open bulk export modal
    openBulkExportModal();
}

function bulkDelete() {
    const selectedIds = getSelectedUserIds();
    if (selectedIds.length === 0) {
        showNotification('Please select at least one user', 'warning');
        return;
    }
    
    showConfirmDialog(`Are you sure you want to DELETE ${selectedIds.length} user(s)? This action CANNOT be undone!`, (confirmed) => {
        if (!confirmed) return;
        
        showConfirmDialog('This is your FINAL confirmation. Are you absolutely sure?', (finalConfirmed) => {
            if (!finalConfirmed) return;
            
            fetch('/admin/users/bulk-delete', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ user_ids: selectedIds })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(`${selectedIds.length} user(s) deleted successfully`, 'success');
                    location.reload();
                } else {
                    showNotification(data.message || 'Failed to delete users', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred', 'error');
            });
        });
    });
}

// --------------------------
// Bulk Export / Notify Modals
// --------------------------

function openBulkExportModal() {
    const selectedIds = getSelectedUserIds();
    if (selectedIds.length === 0) {
        showNotification('Please select at least one user', 'warning');
        return;
    }
    const modalEl = document.getElementById('bulkExportModal');
    if (!modalEl) return;
    if (typeof bootstrap === 'undefined') {
        // Fallback: only PDF supported
        window.location.href = `/admin/users/bulk-export?user_ids=${selectedIds.join(',')}&format=pdf`;
        return;
    }
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

function performBulkExport() {
    const selectedIds = getSelectedUserIds();
    if (selectedIds.length === 0) {
        showNotification('Please select at least one user', 'warning');
        return;
    }
    const modalEl = document.getElementById('bulkExportModal');
    const modal = (typeof bootstrap !== 'undefined') ? bootstrap.Modal.getInstance(modalEl) : null;
    if (modal) modal.hide();
    window.location.href = `/admin/users/bulk-export?user_ids=${selectedIds.join(',')}&format=pdf`;
}

function openBulkNotifyModal() {
    const selectedIds = getSelectedUserIds();
    if (selectedIds.length === 0) {
        showNotification('Please select at least one user', 'warning');
        return;
    }
    const modalEl = document.getElementById('bulkNotifyModal');
    if (!modalEl) return;
    if (typeof bootstrap === 'undefined') {
        const message = prompt('Please enter the notification message to send:');
        if (!message) return;
        fetch('/admin/users/bulk-notify', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ user_ids: selectedIds, message: message })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) showNotification(`Notification sent to ${selectedIds.length} user(s)`, 'success');
            else showNotification(data.message || 'Failed to send notifications', 'error');
        })
        .catch(err => { console.error(err); showNotification('An error occurred', 'error'); });
        return;
    }
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

function performBulkNotify() {
    const selectedIds = getSelectedUserIds();
    if (selectedIds.length === 0) {
        showNotification('Please select at least one user', 'warning');
        return;
    }
    const textarea = document.getElementById('bulkNotifyMessage');
    const message = textarea ? textarea.value.trim() : '';
    const err = document.getElementById('bulkNotifyError');
    if (!message) { if (err) err.textContent = 'Please enter a message.'; return; }
    if (err) err.textContent = '';
    fetch('/admin/users/bulk-notify', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ user_ids: selectedIds, message: message })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`Notification sent to ${selectedIds.length} user(s)`, 'success');
            const modalEl = document.getElementById('bulkNotifyModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        } else {
            showNotification(data.message || 'Failed to send notifications', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
}

// ============================================================================
// FILTERING AND SEARCHING
// ============================================================================

function setupFilterListeners() {
    const roleFilter = document.getElementById('roleFilter');
    const statusFilter = document.getElementById('statusFilter');
    const accountTypeFilter = document.getElementById('accountTypeFilter');
    
    if (roleFilter) {
        roleFilter.addEventListener('change', applyFilters);
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', applyFilters);
    }

    if (accountTypeFilter) {
        accountTypeFilter.addEventListener('change', applyFilters);
    }
}

function setupSearchListener() {
    const searchInput = document.getElementById('userSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', debounce(applyFilters, 500));
    }
}

function applyFilters() {
    const searchValue = document.getElementById('userSearch')?.value || '';
    const role = document.getElementById('roleFilter')?.value || '';
    const status = document.getElementById('statusFilter')?.value || '';
    const accountType = document.getElementById('accountTypeFilter')?.value || '';
    
    const params = new URLSearchParams();
    if (searchValue) params.append('search', searchValue);
    if (role) params.append('role', role);
    if (status) params.append('status', status);
    if (accountType) params.append('accountType', accountType);
    
    window.location.href = `/admin/users?${params.toString()}`;
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// ============================================================================
// CUSTOM CONFIRMATION MODAL
// ============================================================================

function showConfirmDialog(message, callback, title = 'Confirm Action') {
    // Remove any existing modal
    const existingModal = document.getElementById('customConfirmModal');
    if (existingModal) existingModal.remove();

    const modal = document.createElement('div');
    modal.id = 'customConfirmModal';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 3000;
        animation: fadeIn 0.2s ease-out;
    `;

    modal.innerHTML = `
        <div style="
            background: white;
            border-radius: 16px;
            padding: 2rem;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.3s ease-out;
            border: 1px solid rgba(0, 0, 0, 0.1);
        ">
            <div style="
                display: flex;
                align-items: center;
                margin-bottom: 1.5rem;
                padding-bottom: 1rem;
                border-bottom: 1px solid #e5e7eb;
            ">
                <div style="
                    width: 48px;
                    height: 48px;
                    border-radius: 12px;
                    background: linear-gradient(135deg, #fbbf24, #f59e0b);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin-right: 1rem;
                    color: white;
                    font-size: 1.2rem;
                ">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 style="
                    margin: 0;
                    color: #1f2937;
                    font-size: 1.25rem;
                    font-weight: 700;
                ">${title}</h3>
            </div>
            <p style="
                margin: 0 0 2rem 0;
                color: #6b7280;
                line-height: 1.6;
                font-size: 1rem;
            ">${message}</p>
            <div style="
                display: flex;
                gap: 1rem;
                justify-content: flex-end;
            ">
                <button id="confirmCancel" style="
                    padding: 0.75rem 1.5rem;
                    border: 2px solid #d1d5db;
                    background: white;
                    color: #6b7280;
                    border-radius: 8px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.2s ease;
                ">Cancel</button>
                <button id="confirmOk" style="
                    padding: 0.75rem 1.5rem;
                    border: none;
                    background: linear-gradient(135deg, #ef4444, #dc2626);
                    color: white;
                    border-radius: 8px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
                ">Confirm</button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    // Add event listeners
    document.getElementById('confirmCancel').onclick = () => {
        modal.style.animation = 'fadeOut 0.2s ease-out';
        setTimeout(() => {
            modal.remove();
            if (callback) callback(false);
        }, 200);
    };

    document.getElementById('confirmOk').onclick = () => {
        modal.style.animation = 'fadeOut 0.2s ease-out';
        setTimeout(() => {
            modal.remove();
            if (callback) callback(true);
        }, 200);
    };

    // Close on background click
    modal.onclick = (e) => {
        if (e.target === modal) {
            modal.style.animation = 'fadeOut 0.2s ease-out';
            setTimeout(() => {
                modal.remove();
                if (callback) callback(false);
            }, 200);
        }
    };

    // Close on Escape key
    document.addEventListener('keydown', function escHandler(e) {
        if (e.key === 'Escape') {
            modal.style.animation = 'fadeOut 0.2s ease-out';
            setTimeout(() => {
                modal.remove();
                if (callback) callback(false);
            }, 200);
            document.removeEventListener('keydown', escHandler);
        }
    });
}

// ============================================================================
// NOTIFICATIONS
// ============================================================================

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        z-index: 9999;
        animation: slideIn 0.3s ease-in-out;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    `;
    
    const colors = {
        success: { bg: '#d1fae5', text: '#065f46', border: '#6ee7b7' },
        error: { bg: '#fee2e2', text: '#991b1b', border: '#fca5a5' },
        info: { bg: '#dbeafe', text: '#1d4ed8', border: '#93c5fd' },
        warning: { bg: '#fef3c7', text: '#92400e', border: '#fcd34d' }
    };
    
    const color = colors[type] || colors.info;
    notification.style.background = color.bg;
    notification.style.color = color.text;
    notification.style.border = `2px solid ${color.border}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-in-out';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Add animation styles
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }
    
    @keyframes fadeOut {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }
    
    @keyframes slideUp {
        from {
            transform: translateY(30px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    #customConfirmModal button:hover {
        transform: translateY(-1px);
    }
    
    #customConfirmModal #confirmCancel:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }
    
    #customConfirmModal #confirmOk:hover {
        background: linear-gradient(135deg, #dc2626, #b91c1c);
        box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
    }
`;
document.head.appendChild(style);

// ============================================================================
// EXPORT FUNCTIONS
// ============================================================================

function exportUsers() {
    const format = prompt('Please choose an export format (enter 1, 2, or 3):\n1. CSV\n2. JSON\n3. Excel', '1');
    if (!format) return;
    
    const formatMap = { '1': 'csv', '2': 'json', '3': 'excel' };
    const exportFormat = formatMap[format];
    
    if (!exportFormat) {
        showNotification('Invalid format selected', 'error');
        return;
    }
    
    window.location.href = `{{ route('admin.users.export') }}?format=${exportFormat}`;
}
</script>
@endsection