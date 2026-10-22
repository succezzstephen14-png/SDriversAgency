@extends('layouts.admin')

@section('title', 'Admin Dashboard - SUCCEZZ Drivers Agency')

@section('page-title', 'Dashboard')
@section('page-description', 'Comprehensive platform overview and management insights')

@section('styles')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --warning-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        --danger-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        --glass-bg: rgba(255, 255, 255, 0.25);
        --glass-border: rgba(255, 255, 255, 0.18);
        --shadow-light: 0 8px 32px rgba(31, 38, 135, 0.15);
        --shadow-medium: 0 12px 40px rgba(31, 38, 135, 0.20);
        --shadow-heavy: 0 20px 60px rgba(31, 38, 135, 0.25);
    }

    .dashboard-container {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        padding: 2rem 0;
    }

    .dashboard-header {
        background: var(--primary-gradient);
        border-radius: 24px;
        padding: 3rem 2.5rem;
        color: white;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        box-shadow: var(--shadow-heavy);
    }

    .dashboard-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
    }

    .dashboard-header::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -30%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%);
        border-radius: 50%;
    }

    .dashboard-header h1 {
        font-size: 3rem;
        font-weight: 900;
        margin-bottom: 0.5rem;
        position: relative;
        z-index: 2;
        background: linear-gradient(135deg, #ffffff 0%, rgba(255,255,255,0.8) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .dashboard-header p {
        font-size: 1.2rem;
        opacity: 0.9;
        margin-bottom: 2.5rem;
        position: relative;
        z-index: 2;
        font-weight: 500;
    }

    .header-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 2rem;
        position: relative;
        z-index: 2;
    }

    .header-stat-card {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        padding: 2rem;
        text-align: center;
        border: 1px solid var(--glass-border);
        box-shadow: var(--shadow-medium);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
    }

    .header-stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--primary-gradient);
    }

    .header-stat-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: var(--shadow-heavy);
    }

    .header-stat-card .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin: 0 auto 1rem;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
    }

    .header-stat-card .stat-number {
        font-size: 2.5rem;
        font-weight: 900;
        display: block;
        margin-bottom: 0.5rem;
        background: linear-gradient(135deg, #ffffff 0%, rgba(255,255,255,0.9) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .header-stat-card .stat-label {
        font-size: 1rem;
        opacity: 0.9;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .dashboard-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 24px;
        padding: 2.5rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: var(--shadow-light);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .dashboard-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: var(--primary-gradient);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .dashboard-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-medium);
    }

    .dashboard-card:hover::before {
        opacity: 1;
    }

    .dashboard-card h3 {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1a202c;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .dashboard-card h3 i {
        color: #667eea;
        font-size: 1.3rem;
    }

    .stats-overview {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
    }

    .stat-item {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 20px;
        padding: 2rem;
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-item::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        background: radial-gradient(circle, rgba(102, 126, 234, 0.1) 0%, transparent 70%);
        border-radius: 50%;
        transform: translate(30px, -30px);
    }

    .stat-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .stat-item .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
        position: relative;
        z-index: 2;
    }

    .stat-item.users .stat-icon { background: var(--primary-gradient); color: white; }
    .stat-item.drivers .stat-icon { background: var(--success-gradient); color: white; }
    .stat-item.vehicles .stat-icon { background: var(--secondary-gradient); color: white; }
    .stat-item.bookings .stat-icon { background: var(--warning-gradient); color: white; }
    .stat-item.pending .stat-icon { background: var(--danger-gradient); color: white; }
    .stat-item.revenue .stat-icon { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333; }

    .stat-item .stat-value {
        font-size: 2.2rem;
        font-weight: 900;
        color: #1a202c;
        margin-bottom: 0.5rem;
        display: block;
        position: relative;
        z-index: 2;
    }

    .stat-item .stat-label {
        font-size: 1rem;
        font-weight: 700;
        color: #4a5568;
        margin-bottom: 0.5rem;
        position: relative;
        z-index: 2;
    }

    .stat-item .stat-change {
        font-size: 0.9rem;
        font-weight: 700;
        position: relative;
        z-index: 2;
    }

    .stat-item .stat-change.positive { color: #38a169; }
    .stat-item .stat-change.negative { color: #e53e3e; }
    .stat-item .stat-change.neutral { color: #718096; }

    .charts-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
        gap: 2rem;
    }

    .chart-container {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(15px);
        border-radius: 20px;
        padding: 2rem;
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: var(--shadow-light);
        transition: all 0.3s ease;
    }

    .chart-container:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-medium);
    }

    .chart-container h4 {
        font-size: 1.3rem;
        font-weight: 800;
        color: #2d3748;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .chart-container h4 i {
        color: #667eea;
    }

    .activity-section {
        background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(255,255,255,0.7) 100%);
        backdrop-filter: blur(20px);
        border-radius: 24px;
        padding: 2.5rem;
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: var(--shadow-light);
    }

    .activity-section h3 {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1a202c;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .activity-section h3 i {
        color: #667eea;
    }

    .activity-list {
        space-y: 1rem;
    }

    .activity-item {
        display: flex;
        align-items: center;
        padding: 1.25rem;
        background: rgba(255, 255, 255, 0.6);
        border-radius: 16px;
        margin-bottom: 1rem;
        border: 1px solid rgba(255, 255, 255, 0.4);
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }

    .activity-item:hover {
        transform: translateX(4px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .activity-item:last-child {
        margin-bottom: 0;
    }

    .activity-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1.25rem;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .activity-icon.user { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
    .activity-icon.booking { background: linear-gradient(135deg, #4facfe, #00f2fe); color: white; }
    .activity-icon.driver { background: linear-gradient(135deg, #fa709a, #fee140); color: white; }
    .activity-icon.vehicle { background: linear-gradient(135deg, #a8edea, #fed6e3); color: #333; }

    .activity-content {
        flex: 1;
    }

    .activity-content .title {
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0.25rem;
        font-size: 1rem;
    }

    .activity-content .meta {
        font-size: 0.85rem;
        color: #718096;
        font-weight: 500;
    }

    .quick-actions-section {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        border-radius: 24px;
        padding: 2.5rem;
        border: 1px solid rgba(102, 126, 234, 0.2);
        box-shadow: var(--shadow-light);
    }

    .quick-actions-section h3 {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1a202c;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .quick-actions-section h3 i {
        color: #667eea;
    }

    .action-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.25rem;
    }

    .action-card {
        display: flex;
        align-items: center;
        padding: 1.5rem;
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(15px);
        border-radius: 16px;
        text-decoration: none;
        color: #4a5568;
        font-weight: 600;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        position: relative;
        overflow: hidden;
    }

    .action-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: var(--primary-gradient);
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: -1;
    }

    .action-card:hover {
        transform: translateY(-4px) scale(1.02);
        box-shadow: 0 12px 30px rgba(102, 126, 234, 0.3);
        color: white;
    }

    .action-card:hover::before {
        opacity: 1;
    }

    .action-card:hover .action-icon {
        background: rgba(255, 255, 255, 0.2);
        color: white;
    }

    .action-icon {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        font-size: 1.3rem;
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }

    .action-content {
        flex: 1;
    }

    .action-content .title {
        font-weight: 700;
        margin-bottom: 0.25rem;
        transition: color 0.3s ease;
    }

    .action-content .description {
        font-size: 0.85rem;
        opacity: 0.8;
        font-weight: 500;
        transition: opacity 0.3s ease;
    }

    .recent-section {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 24px;
        padding: 2.5rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: var(--shadow-light);
    }

    .recent-section h3 {
        font-size: 1.5rem;
        font-weight: 800;
        color: #1a202c;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .recent-section h3 i {
        color: #667eea;
    }

    .recent-list {
        space-y: 1rem;
    }

    .recent-item {
        display: flex;
        align-items: center;
        padding: 1.25rem;
        background: rgba(248, 250, 252, 0.8);
        border-radius: 16px;
        margin-bottom: 1rem;
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .recent-item:hover {
        transform: translateX(4px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        background: rgba(255, 255, 255, 0.9);
    }

    .recent-item:last-child {
        margin-bottom: 0;
    }

    .recent-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1.25rem;
        font-size: 1.2rem;
        background: var(--primary-gradient);
        color: white;
        flex-shrink: 0;
    }

    .recent-content {
        flex: 1;
    }

    .recent-content strong {
        font-weight: 700;
        color: #2d3748;
        display: block;
        margin-bottom: 0.5rem;
    }

    .recent-meta {
        display: flex;
        gap: 1rem;
        font-size: 0.85rem;
        color: #718096;
        align-items: center;
    }

    .recent-meta span {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-pending { background: #fef3c7; color: #d97706; }
    .badge-approved { background: #d1fae5; color: #065f46; }
    .badge-rejected { background: #fee2e2; color: #dc2626; }

    .recent-time {
        font-size: 0.8rem;
        color: #a0aec0;
        font-weight: 600;
        white-space: nowrap;
    }

    @media (max-width: 768px) {
        .dashboard-header {
            padding: 2rem 1.5rem;
        }

        .dashboard-header h1 {
            font-size: 2.5rem;
        }

        .header-stats {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .dashboard-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .charts-section {
            grid-template-columns: 1fr;
        }

        .action-grid {
            grid-template-columns: 1fr;
        }
    }

    .pulse-animation {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
</style>
@endsection

@section('content')
<div class="dashboard-container">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <h1>Welcome back, Admin!</h1>
        <p>Monitor your transportation platform's performance and manage all operations from this centralized dashboard.</p>

        <div class="header-stats">
            <div class="header-stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <span class="stat-number">{{ number_format($stats['total_users'] ?? 0) }}</span>
                <span class="stat-label">Total Users</span>
            </div>
            <div class="header-stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <span class="stat-number">{{ number_format($stats['total_drivers'] ?? 0) }}</span>
                <span class="stat-label">Active Drivers</span>
            </div>
            <div class="header-stat-card">
                <div class="stat-icon">
                    <i class="fas fa-car"></i>
                </div>
                <span class="stat-number">{{ number_format($stats['total_vehicles'] ?? 0) }}</span>
                <span class="stat-label">Fleet Vehicles</span>
            </div>
            <div class="header-stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <span class="stat-number">{{ number_format($stats['total_bookings'] ?? 0) }}</span>
                <span class="stat-label">Total Bookings</span>
            </div>
        </div>
    </div>

    @if(isset($serviceRequestExpiries) && $serviceRequestExpiries->isNotEmpty())
        <div class="dashboard-card mb-4" style="grid-column: span 12; border-left: 5px solid #f59e0b;">
            <h3><i class="fas fa-hourglass-half text-warning"></i> Service agreements nearing expiry</h3>
            <p class="text-muted">Review these active service requests and contact customers before the agreed end date.</p>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Request</th>
                            <th>Customer</th>
                            <th>Assigned service</th>
                            <th>Agreement ends</th>
                            <th>Days left</th>
                            <th>Reminder</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($serviceRequestExpiries as $serviceRequest)
                            <tr>
                                <td>#{{ $serviceRequest->id }} · {{ ucfirst($serviceRequest->request_type) }}</td>
                                <td>{{ $serviceRequest->user?->name ?? 'Unknown customer' }}</td>
                                <td>
                                    @if($serviceRequest->assignedDriver)
                                        Driver: {{ $serviceRequest->assignedDriver->user?->name ?? 'Assigned' }}
                                    @elseif($serviceRequest->assignedVehicle)
                                        Vehicle: {{ $serviceRequest->assignedVehicle->make }} {{ $serviceRequest->assignedVehicle->model }}
                                    @else
                                        {{ $serviceRequest->service_label }}
                                    @endif
                                </td>
                                <td>{{ $serviceRequest->agreement_end_date->format('M j, Y') }}</td>
                                <td>{{ now()->startOfDay()->diffInDays($serviceRequest->agreement_end_date) }}</td>
                                <td>
                                    @if($serviceRequest->expiry_reminder_sent_at)
                                        Sent {{ $serviceRequest->expiry_reminder_sent_at->format('M j, Y') }}
                                    @else
                                        {{ $serviceRequest->expiry_reminder_days }} days before expiry
                                    @endif
                                </td>
                                <td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.service-requests.show', $serviceRequest) }}">Review</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Stats Overview -->
    <div class="dashboard-grid">
        <div class="dashboard-card" style="grid-column: span 12;">
            <h3><i class="fas fa-chart-line"></i> Platform Overview</h3>
            <div class="stats-overview">
                <div class="stat-item users">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value">{{ number_format($stats['total_users'] ?? 0) }}</div>
                    <div class="stat-label">Registered Users</div>
                    <div class="stat-change positive">+12% this month</div>
                </div>

                <div class="stat-item drivers">
                    <div class="stat-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="stat-value">{{ number_format($stats['total_drivers'] ?? 0) }}</div>
                    <div class="stat-label">Approved Drivers</div>
                    <div class="stat-change positive">+8% this month</div>
                </div>

                <div class="stat-item vehicles">
                    <div class="stat-icon">
                        <i class="fas fa-car"></i>
                    </div>
                    <div class="stat-value">{{ number_format($stats['total_vehicles'] ?? 0) }}</div>
                    <div class="stat-label">Fleet Vehicles</div>
                    <div class="stat-change positive">+15% this month</div>
                </div>

                <div class="stat-item bookings">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-value">{{ number_format($stats['total_bookings'] ?? 0) }}</div>
                    <div class="stat-label">Total Bookings</div>
                    <div class="stat-change positive">+25% this month</div>
                </div>

                <div class="stat-item pending">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value">{{ number_format($stats['pending_approvals'] ?? 0) }}</div>
                    <div class="stat-label">Pending Approvals</div>
                    <div class="stat-change negative">-5% this week</div>
                </div>

                <div class="stat-item revenue">
                    <div class="stat-icon">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="stat-value">{{ config('app.currency_label', 'GHC') }} {{ number_format($stats['total_revenue'] ?? 0, 2) }}</div>
                    <div class="stat-label">Total Revenue</div>
                    <div class="stat-change positive">+32% this month</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="dashboard-grid">
        <div class="dashboard-card" style="grid-column: span 8;">
            <h3><i class="fas fa-chart-area"></i> Analytics & Trends</h3>
            <div class="charts-section">
                <div class="chart-container">
                    <h4><i class="fas fa-chart-line"></i> Bookings Trend (Last 14 Days)</h4>
                    <canvas id="bookingsChart" style="max-height: 300px;"></canvas>
                </div>

                <div class="chart-container">
                    <h4><i class="fas fa-chart-pie"></i> Application Status Distribution</h4>
                    <canvas id="applicationChart" style="max-height: 300px;"></canvas>
                </div>

                <div class="chart-container">
                    <h4><i class="fas fa-chart-bar"></i> Drivers vs Vehicles</h4>
                    <canvas id="vehicleDriverChart" style="max-height: 300px;"></canvas>
                </div>

                <div class="chart-container">
                    <h4><i class="fas fa-chart-area"></i> Revenue Trend</h4>
                    <canvas id="revenueChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Activity & Quick Actions -->
        <div style="grid-column: span 4; display: flex; flex-direction: column; gap: 2rem;">
            <!-- Recent Activity -->
            <div class="activity-section">
                <h3><i class="fas fa-history"></i> Recent Activity</h3>
                <div class="activity-list">
                    @if(isset($recent_activities) && $recent_activities->count() > 0)
                        @foreach($recent_activities as $activity)
                        <div class="activity-item">
                            <div class="activity-icon {{ $activity['type'] }}">
                                <i class="fas fa-{{ $activity['icon'] }}"></i>
                            </div>
                            <div class="activity-content">
                                <div class="title">{{ $activity['title'] }}</div>
                                <div class="meta">{{ $activity['description'] }} � {{ $activity['time'] }}</div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="activity-item">
                            <div class="activity-icon user">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div class="activity-content">
                                <div class="title">Platform Active</div>
                                <div class="meta">System is running smoothly � Just now</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions-section">
                <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                <div class="action-grid">
                    <a href="{{ route('admin.recruitment-review') }}" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <div class="action-content">
                            <div class="title">Review Applications</div>
                            <div class="description">Check pending driver & vehicle applications</div>
                        </div>
                    </a>

                    <a href="{{ route('admin.vehicles.index') }}" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-car"></i>
                        </div>
                        <div class="action-content">
                            <div class="title">Vehicle Management</div>
                            <div class="description">Approve, manage, and monitor vehicles</div>
                        </div>
                    </a>

                    <a href="{{ route('admin.bookings') }}" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="action-content">
                            <div class="title">Manage Bookings</div>
                            <div class="description">View and manage all booking requests</div>
                        </div>
                    </a>

                    <a href="{{ route('admin.location-tracker') }}" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="action-content">
                            <div class="title">Live Tracking</div>
                            <div class="description">Monitor active trips and driver locations</div>
                        </div>
                    </a>

                    <a href="{{ route('admin.analytics') }}" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="action-content">
                            <div class="title">View Analytics</div>
                            <div class="description">Detailed platform performance metrics</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Items -->
    <div class="dashboard-grid">
        <div class="dashboard-card" style="grid-column: span 6;">
            <div class="recent-section">
                <h3><i class="fas fa-car"></i> Recently Approved Vehicles</h3>
                <p style="color: #718096; margin-top: -1rem; margin-bottom: 1.5rem; font-size: 0.9rem;">
                    Recently approved vehicles in your fleet.
                    <a href="{{ route('admin.vehicles.index') }}">Manage all vehicles</a>
                </p>
                <div class="recent-list">
                    @forelse($recent_vehicles ?? collect() as $vehicle)
                    <div class="recent-item">
                        <div class="recent-icon">
                            <i class="fas fa-car"></i>
                        </div>
                        <div class="recent-content">
                            <strong>{{ $vehicle->make }} {{ $vehicle->model }}</strong>
                            <div class="recent-meta">
                                <span><i class="fas fa-user"></i> {{ $vehicle->user?->name ?? 'Unknown' }}</span>
                                <span class="badge badge-{{ $vehicle->approval_status }}">{{ ucfirst($vehicle->approval_status) }}</span>
                            </div>
                        </div>
                        <div class="recent-time">{{ $vehicle->created_at->diffForHumans() }}</div>
                    </div>
                    @empty
                    <div class="recent-item">
                        <div class="recent-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="recent-content">
                            <strong>No approved vehicles yet</strong>
                            <div class="recent-meta">
                                <span>Approved vehicle registrations will appear here</span>
                            </div>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="dashboard-card" style="grid-column: span 6;">
            <div class="recent-section">
                <h3><i class="fas fa-user-tie"></i> Recent Drivers</h3>
                <p style="color: #718096; margin-top: -1rem; margin-bottom: 1.5rem; font-size: 0.9rem;">Latest driver applications and approvals</p>
                <div class="recent-list">
                    @forelse($recent_drivers ?? collect() as $driver)
                    <div class="recent-item">
                        <div class="recent-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="recent-content">
                            <strong>{{ $driver->user?->name ?? 'Driver' }}</strong>
                            <div class="recent-meta">
                                <span><i class="fas fa-id-card"></i> {{ $driver->license_number }}</span>
                                <span class="badge badge-{{ $driver->approval_status }}">{{ ucfirst($driver->approval_status) }}</span>
                            </div>
                        </div>
                        <div class="recent-time">{{ $driver->created_at->diffForHumans() }}</div>
                    </div>
                    @empty
                    <div class="recent-item">
                        <div class="recent-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="recent-content">
                            <strong>No recent drivers</strong>
                            <div class="recent-meta">
                                <span>New driver applications will appear here</span>
                            </div>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add pulse animation to pending approvals stat
    const pendingStat = document.querySelector('.stat-item.pending');
    if (pendingStat) {
        pendingStat.classList.add('pulse-animation');
    }

    // Bookings Chart
    const bookingsCtx = document.getElementById('bookingsChart');
    if (bookingsCtx) {
        new Chart(bookingsCtx, {
            type: 'line',
            data: {
                labels: @json($last14Days ?? []),
                datasets: [{
                    label: 'Daily Bookings',
                    data: @json($bookingsByDay ?? []),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: { color: '#64748b' }
                    },
                    x: {
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: { color: '#64748b' }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }

    // Application Status Chart
    const applicationCtx = document.getElementById('applicationChart');
    if (applicationCtx) {
        new Chart(applicationCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Approved', 'Rejected'],
                datasets: [{
                    data: @json(array_values($applicationStatuses ?? [0, 0, 0])),
                    backgroundColor: ['#f59e0b', '#10b981', '#ef4444'],
                    borderWidth: 0,
                    hoverBorderWidth: 4,
                    hoverBorderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            color: '#64748b'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff'
                    }
                },
                cutout: '60%'
            }
        });
    }

    // Drivers vs Vehicles Chart
    const vehicleDriverCtx = document.getElementById('vehicleDriverChart');
    if (vehicleDriverCtx) {
        new Chart(vehicleDriverCtx, {
            type: 'bar',
            data: {
                labels: ['Drivers', 'Vehicles'],
                datasets: [{
                    label: 'Count',
                    data: [@json($stats['total_drivers'] ?? 0), @json($stats['total_vehicles'] ?? 0)],
                    backgroundColor: ['#667eea', '#764ba2'],
                    borderRadius: 8,
                    borderSkipped: false,
                    borderWidth: 0,
                    hoverBackgroundColor: ['#5a67d8', '#6b46c1']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: { color: '#64748b' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b' }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeOutQuart'
                }
            }
        });
    }

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: @json($revenueMonths ?? []),
                datasets: [{
                    label: @json('Monthly Revenue (' . config('app.currency_label', 'GHC') . ')'),
                    data: @json($revenueByMonth ?? []),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: {
                            color: '#64748b',
                            callback: function(value) {
                                return '?' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: { color: '#64748b' }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }

    // Add smooth scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe all dashboard cards
    document.querySelectorAll('.dashboard-card, .header-stat-card, .stat-item, .chart-container, .activity-item, .action-card, .recent-item').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
});
</script>
@endsection
