@extends('layouts.dashboard')

@section('title', 'User Dashboard')

@section('content')
<style>
    /* Dashboard Enhancements */
    .stat-value {
        font-size: 2.75rem;
        font-weight: 900;
        color: var(--dashboard-primary);
        margin-bottom: 0.5rem;
    }

    .stat-label {
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.75rem;
    }

    .stat-note {
        color: #94a3b8;
        font-size: 0.95rem;
        font-weight: 500;
    }

    /* Action Buttons */
    .action-buttons {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 1rem;
        margin-top: 1.5rem;
    }

    .action-button {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        padding: 1.25rem;
        border-radius: 18px;
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        border: 2px solid rgba(14, 165, 233, 0.2);
        color: var(--dashboard-text);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        text-align: center;
    }

    .action-button i {
        font-size: 1.75rem;
        color: var(--dashboard-primary);
    }

    .action-button:hover {
        background: linear-gradient(135deg, #e0f2fe 0%, #cffafe 100%);
        border-color: rgba(14, 165, 233, 0.5);
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(14, 165, 233, 0.2);
        color: var(--dashboard-primary-dark);
    }

    .action-button:hover i {
        color: var(--dashboard-primary-dark);
    }

    /* Recent Items Styling */
    .recent-item {
        background: white;
        border-radius: 18px;
        border: 1px solid rgba(148, 163, 184, 0.12);
        padding: 1.25rem;
        display: grid;
        gap: 0.75rem;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        transition: all 0.3s ease;
    }

    .recent-item:hover {
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
        border-color: rgba(14, 165, 233, 0.2);
        transform: translateX(2px);
    }

    .recent-item h5 {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }

    .recent-item .meta {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .recent-item .meta span {
        color: #94a3b8;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .recent-item .badge {
        font-size: 0.8rem;
        font-weight: 700;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
    }

    .dashboard-grid {
        display: grid;
        gap: 1.75rem;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        margin-top: 2rem;
    }

    .dashboard-grid.full {
        grid-template-columns: 1fr;
    }

    .hero-intro {
        display: grid;
        gap: 1.75rem;
        grid-template-columns: minmax(0, 1.65fr) minmax(280px, 1fr);
        align-items: center;
    }

    .hero-image-grid {
        display: grid;
        gap: 1rem;
    }

    .hero-image-card {
        min-height: 180px;
        border-radius: 24px;
        background-size: cover;
        background-position: center;
        position: relative;
        overflow: hidden;
        display: grid;
        align-items: end;
        padding: 1rem;
        color: white;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.12);
    }

    .hero-image-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, transparent 40%, rgba(15, 23, 42, 0.75) 100%);
    }

    .hero-image-card div {
        position: relative;
        z-index: 1;
        font-weight: 700;
    }

    .hero-pill-group {
        display: flex;
        flex-wrap: wrap;
        gap: 0.85rem;
        margin-top: 1.5rem;
    }

    .hero-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.9rem 1.15rem;
        border-radius: 999px;
        background: rgba(14, 165, 233, 0.1);
        color: #0f172a;
        font-weight: 700;
        font-size: 0.95rem;
    }

    .featured-grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    }

    .featured-card {
        border-radius: 24px;
        overflow: hidden;
        position: relative;
        background: white;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.12);
        border: 1px solid rgba(148, 163, 184, 0.16);
    }

    .featured-card img {
        width: 100%;
        height: 170px;
        object-fit: cover;
    }

    .featured-card-content {
        padding: 1.4rem;
    }

    .featured-card h5 {
        margin: 0 0 0.65rem;
        color: #0f172a;
    }

    .featured-card p {
        margin: 0;
        color: #64748b;
        line-height: 1.8;
    }

    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .section-header h3 {
        margin: 0;
        font-size: 1.4rem;
        font-weight: 800;
        color: #0f172a;
    }

    .section-header p {
        margin: 0;
        color: #64748b;
        font-size: 0.95rem;
    }

    .btn-soft {
        background: white;
        color: var(--dashboard-primary);
        border: 1.5px solid rgba(14, 165, 233, 0.3);
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-soft:hover {
        background: var(--dashboard-primary-soft);
        border-color: rgba(14, 165, 233, 0.6);
        color: var(--dashboard-primary-dark);
    }

    @media (max-width: 768px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }

        .hero-intro {
            grid-template-columns: minmax(0, 1fr);
        }

        .stat-value {
            font-size: 2rem;
        }

        .action-buttons {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 480px) {
        .hero-welcome {
            padding: 1rem;
        }

        .featured-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .action-buttons {
            grid-template-columns: minmax(0, 1fr);
        }
    }
</style>

<div class="user-dashboard-content">
    <!-- Welcome Hero Section -->
    <div class="hero-welcome dashboard-card mb-4">
        <div class="hero-intro">
            <div>
                <span class="hero-pill"><i class="fas fa-thumbs-up"></i> Trusted transport</span>
                <h2 class="hero-heading mb-3">Welcome back, {{ $user->name }}!</h2>
                <p class="hero-text mb-4">Your dashboard is designed to help you manage bookings, applications, drivers, vehicles, and recruitment with clarity and speed. Navigate actions from the sidebar for a more focused workflow.</p>
                <div class="hero-pill-group">
                    <span class="hero-pill"><i class="fas fa-check-circle"></i> Personalized insights</span>
                    <span class="hero-pill"><i class="fas fa-shield-alt"></i> Secure and reliable</span>
                </div>
            </div>
            <div class="hero-image-grid">
                <div class="hero-image-card" style="background-image: url('https://images.unsplash.com/photo-1491553895911-0055eca6402d?auto=format&fit=crop&w=900&q=80');">
                    <div>Reliable drivers</div>
                </div>
                <div class="hero-image-card" style="background-image: url('https://images.unsplash.com/photo-1511919884226-fd3cad34687c?auto=format&fit=crop&w=900&q=80');">
                    <div>Modern fleet</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="mb-4">
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-label"><i class="fas fa-calendar-check me-2"></i>Total Bookings</div>
                <div class="stat-value">{{ $stats['bookings'] ?? 0 }}</div>
                <div class="stat-note">Service bookings</div>
            </div>
            <div class="stat-card">
                <div class="stat-label"><i class="fas fa-user-tie me-2"></i>Drivers</div>
                <div class="stat-value">{{ $stats['drivers'] ?? 0 }}</div>
                <div class="stat-note">Approved driver profiles</div>
            </div>
            <div class="stat-card">
                <div class="stat-label"><i class="fas fa-car-side me-2"></i>Vehicles</div>
                <div class="stat-value">{{ $stats['vehicles'] ?? 0 }}</div>
                <div class="stat-note">Approved vehicles</div>
            </div>
        </div>
    </div>

    <div class="dashboard-grid full mb-4">
        <div class="dashboard-card">
            <div class="section-header">
                <div>
                    <h3><i class="fas fa-clipboard-check me-2" style="color: var(--dashboard-primary);"></i>My submissions</h3>
                    <p>Track your driver and vehicle applications, including approved vehicles.</p>
                </div>
            </div>

            @if($driverProfile)
                <div class="recent-item mb-3">
                    <div class="meta">
                        <div>
                            <h5>Driver profile</h5>
                            <span>Submitted {{ $driverProfile->created_at?->format('M d, Y') ?? 'recently' }}</span>
                        </div>
                        <span class="badge bg-{{ $driverProfile->approval_status === 'approved' ? 'success' : ($driverProfile->approval_status === 'rejected' ? 'danger' : 'warning text-dark') }}">
                            {{ $driverProfile->approval_status === 'approved' ? 'Approved' : ($driverProfile->approval_status === 'rejected' ? 'Rejected' : 'Waiting for approval') }}
                        </span>
                    </div>
                    @if($driverProfile->approval_status === 'pending')
                        <p class="text-muted mb-0">Your payment is confirmed. Your driver profile will be active after admin approval.</p>
                    @elseif($driverProfile->approval_status === 'rejected')
                        <p class="text-muted mb-0">Reason: {{ $driverProfile->rejection_reason ?? 'No reason provided' }}</p>
                    @else
                        <p class="text-muted mb-0">Your driver profile has been approved.</p>
                    @endif
                    @if($driverProfile->drive_test_appointment_date)
                        <p class="text-primary mt-2 mb-0">
                            <i class="fas fa-calendar-check me-1"></i>
                            <strong>Driving test:</strong> {{ $driverProfile->drive_test_appointment_date->format('M j, Y') }}
                        </p>
                    @endif
                    <a href="{{ route('drivers.profile') }}" class="btn btn-soft btn-sm mt-2">View driver profile</a>
                </div>
            @else
                <div class="recent-item mb-3">
                    <div class="meta">
                        <div>
                            <h5>Driver profile</h5>
                            <span>No profile submitted</span>
                        </div>
                    </div>
                    <a href="{{ route('drivers.create') }}" class="btn btn-soft btn-sm mt-2">Create driver profile</a>
                </div>
            @endif

            @forelse($dashboardVehicles as $vehicle)
                <div class="recent-item mb-3">
                    <div class="meta">
                        <div>
                            <h5>{{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}</h5>
                            <span>Submitted {{ $vehicle->created_at?->format('M d, Y') ?? 'recently' }}</span>
                        </div>
                        <span class="badge bg-{{ $vehicle->approval_status === 'approved' ? 'success' : ($vehicle->approval_status === 'rejected' ? 'danger' : 'warning text-dark') }}">
                            {{ $vehicle->approval_status === 'approved' ? 'Approved' : ($vehicle->approval_status === 'rejected' ? 'Rejected' : 'Waiting for approval') }}
                        </span>
                    </div>
                    @if($vehicle->approval_status === 'pending')
                        <p class="text-muted mb-0">Your vehicle is waiting for admin approval.</p>
                    @elseif($vehicle->approval_status === 'rejected')
                        <p class="text-muted mb-0">Reason: {{ $vehicle->rejection_reason ?? 'No reason provided' }}</p>
                    @else
                        <p class="text-muted mb-0">Your vehicle has been approved.</p>
                    @endif
                </div>
            @empty
                <div class="recent-item mb-3">
                    <div class="meta">
                        <div>
                            <h5>Pending or rejected vehicles</h5>
                            <span>No vehicles awaiting review or requiring changes</span>
                        </div>
                    </div>
                </div>
            @endforelse

            @forelse($approvedVehicles as $vehicle)
                <div class="recent-item mb-3">
                    <div class="meta">
                        <div>
                            <h5>{{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}</h5>
                            <span>Approved vehicle · Submitted {{ $vehicle->created_at?->format('M d, Y') ?? 'recently' }}</span>
                        </div>
                        <span class="badge bg-success">Approved</span>
                    </div>
                    <p class="text-muted mb-0">Your vehicle has been approved. You can manage it from your approved vehicles list.</p>
                </div>
            @empty
                <div class="recent-item">
                    <div class="meta">
                        <div>
                            <h5>Approved vehicles</h5>
                            <span>You do not have any approved vehicles yet</span>
                        </div>
                    </div>
                </div>
            @endforelse

            @if($dashboardVehicles->isEmpty() && $approvedVehicles->isEmpty())
                <a href="{{ route('vehicles.create') }}" class="btn btn-soft btn-sm mb-3">Add a vehicle</a>
            @endif
            <a href="{{ route('vehicles.my-vehicles') }}" class="btn btn-soft btn-sm">View all approved vehicles</a>
        </div>
    </div>

    <!-- Notifications Section -->
    <div class="dashboard-grid full mb-4">
        <div class="dashboard-card">
            <div class="section-header">
                <div>
                    <h3><i class="fas fa-bell me-2" style="color: var(--dashboard-primary);"></i>Notifications</h3>
                    <p>Recent updates for your bookings, applications, and account activity.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if(!empty($unreadNotifications) && $unreadNotifications > 0)
                        <span class="badge bg-warning text-dark">{{ $unreadNotifications }} new</span>
                    @endif
                    <a href="{{ route('notifications.index') }}" class="btn btn-soft btn-sm">View All</a>
                </div>
            </div>

            @if($notifications->isEmpty())
                <div class="alert alert-info rounded-18" style="border-radius: 18px;">
                    <i class="fas fa-info-circle me-2"></i> You have no notifications yet. Status updates will appear here once your application or booking is reviewed.
                </div>
            @else
                <div class="recent-list">
                    @foreach($notifications as $notification)
                        <div class="recent-item">
                            <div class="meta">
                                <div>
                                    <h5>{{ $notification->title }}</h5>
                                    <span>{{ $notification->created_at?->format('M d, Y \\a\\t H:i') ?? 'Recently' }}</span>
                                </div>
                                <span class="badge bg-{{ $notification->status === 'unread' ? 'warning text-dark' : 'secondary' }}">
                                    {{ ucfirst($notification->status) }}
                                </span>
                            </div>
                            <p class="text-muted mb-2 fw-500">{{ $notification->message }}</p>
                            @if($notification->status === 'unread')
                                <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-soft btn-sm">Mark as read</button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Sidebar Guidance & Featured Fleet -->
    <div class="dashboard-grid full">
        <div class="dashboard-card">
            <div class="section-header">
                <div>
                    <h3><i class="fas fa-compass me-2" style="color: var(--dashboard-primary);"></i>Navigate with confidence</h3>
                    <p>Use the sidebar to access profile completion, driver and vehicle creation, recruitment posting, bookings, and applications.</p>
                </div>
            </div>
            <div class="alert alert-info rounded-18" style="border-radius: 18px;">
                <i class="fas fa-info-circle me-2"></i> The quick actions have moved to the sidebar for a cleaner dashboard experience. Choose any task from the menu to continue.
            </div>
        </div>
    </div>

    <div class="dashboard-grid full">
        <div class="dashboard-card">
            <div class="section-header">
                <div>
                    <h3><i class="fas fa-star me-2" style="color: var(--dashboard-primary);"></i>Featured fleet</h3>
                    <p>Quick preview of available service-ready vehicle options.</p>
                </div>
            </div>
            <div class="featured-grid">
                <div class="featured-card">
                    <img src="https://images.unsplash.com/photo-1542362567-b07e54358753?auto=format&fit=crop&w=900&q=80" alt="Executive Sedan">
                    <div class="featured-card-content">
                        <h5>Executive Sedan</h5>
                        <p>Premium comfort for business travel and VIP transfers.</p>
                    </div>
                </div>
                <div class="featured-card">
                    <img src="https://images.unsplash.com/photo-1502877338535-766e1452684a?auto=format&fit=crop&w=900&q=80" alt="Spacious Van">
                    <div class="featured-card-content">
                        <h5>Spacious Van</h5>
                        <p>Flexible capacity for group transport and daily sales routes.</p>
                    </div>
                </div>
                <div class="featured-card">
                    <img src="https://images.unsplash.com/photo-1511919884226-fd3cad34687c?auto=format&fit=crop&w=900&q=80" alt="Corporate Shuttle">
                    <div class="featured-card-content">
                        <h5>Corporate Shuttle</h5>
                        <p>Reliable vehicles and skilled drivers for business shuttles.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="dashboard-grid full mt-3">
        <div class="dashboard-card">
            <div class="section-header">
                <div>
                    <h3><i class="fas fa-clock me-2" style="color: var(--dashboard-primary);"></i>Recent Activity</h3>
                    <p>Your latest bookings</p>
                </div>
                @if(!$recentBookings->isEmpty())
                    <a href="{{ route('bookings.my-bookings') }}" class="btn btn-soft btn-sm">View all →</a>
                @endif
            </div>

            @if($recentBookings->isEmpty())
                <div class="alert alert-info rounded-18" style="border-radius: 18px;">
                    <i class="fas fa-lightbulb me-2"></i> No activity yet! Start by <a href="{{ route('profile.edit') }}">completing your profile</a>, <a href="{{ route('drivers.create') }}">creating a driver</a>, or <a href="{{ route('vehicles.create') }}">adding a vehicle</a>.
                </div>
            @else
                <div class="recent-list">
                    @foreach($recentBookings->take(5) as $booking)
                        <div class="recent-item">
                            <div class="meta">
                                <div>
                                    <h5>
                                        @if($booking->booking_type === 'driver')
                                            <i class="fas fa-user-tie me-2" style="color: var(--dashboard-primary);"></i>Driver Booking
                                        @elseif($booking->booking_type === 'vehicle')
                                            <i class="fas fa-car-side me-2" style="color: var(--dashboard-primary);"></i>Vehicle Booking
                                        @else
                                            <i class="fas fa-briefcase me-2" style="color: var(--dashboard-primary);"></i>Application
                                        @endif
                                    </h5>
                                    <span>{{ $booking->created_at?->format('M d, Y \a\t H:i') ?? 'Recently' }}</span>
                                </div>
                                <span class="badge bg-{{ $booking->approval_status === 'approved' ? 'success' : ($booking->approval_status === 'rejected' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($booking->approval_status ?? 'pending') }}
                                </span>
                            </div>
                            <p class="text-muted mb-2 fw-500">
                                @if($booking->booking_type === 'driver' && $booking->driver)
                                    Driver booking
                                @elseif($booking->booking_type === 'vehicle' && $booking->vehicle)
                                    {{ $booking->vehicle->make }} {{ $booking->vehicle->model }}
                                @else
                                    {{ $booking->recruitment?->title ?? 'Recruitment opportunity' }}
                                @endif
                            </p>
                            <a href="{{ route('bookings.show', $booking) }}" class="btn btn-sm btn-outline-primary">View details →</a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
