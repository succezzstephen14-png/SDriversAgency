<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard - SUCCEZZ Drivers Agency')</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    @stack('styles')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --admin-bg: #f8fafc;
            --admin-surface: #ffffff;
            --admin-surface-strong: #0f172a;
            --admin-text: #334155;
            --admin-muted: #64748b;
            --admin-primary: #dc2626;
            --admin-primary-dark: #b91c1c;
            --admin-primary-soft: #fef2f2;
            --admin-accent: #f59e0b;
            --admin-border: rgba(148, 163, 184, 0.12);
            --admin-shadow: 0 20px 60px rgba(15, 23, 42, 0.08);
            --admin-shadow-sm: 0 4px 12px rgba(15, 23, 42, 0.06);
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            min-height: 100%;
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: var(--admin-text);
            background: var(--admin-bg);
        }

        body {
            overflow-x: hidden;
        }

        .admin-shell {
            display: flex;
            min-height: 100vh;
        }

        .admin-sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: 280px;
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            color: #e2e8f0;
            padding: 1.75rem 0 1.25rem;
            box-shadow: 8px 0 40px rgba(0, 0, 0, 0.25);
            z-index: 1200;
            transition: transform 0.28s ease, width 0.28s ease;
            overflow-y: auto;
        }

        .admin-sidebar.open {
            transform: translateX(0);
        }

        .sidebar-brand,
        .sidebar-user,
        .sidebar-footer {
            padding: 0 1.75rem;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            margin-bottom: 1.75rem;
            color: white;
            font-weight: 700;
            font-size: 1.05rem;
        }

        .sidebar-brand .brand-mark {
            width: 44px;
            height: 44px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #dc2626, #ef4444);
            box-shadow: 0 16px 40px rgba(220, 38, 38, 0.3);
        }

        .sidebar-brand .brand-mark i {
            color: white;
            font-size: 1rem;
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding-bottom: 1rem;
        }

        .sidebar-user .avatar {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: rgba(239, 68, 68, 0.18);
            display: grid;
            place-items: center;
            color: white;
            font-weight: 700;
            font-size: 1rem;
        }

        .sidebar-user .meta {
            flex: 1;
            min-width: 0;
        }

        .sidebar-user .meta h4,
        .sidebar-user .meta p {
            margin: 0;
            line-height: 1.3;
        }

        .sidebar-user .meta h4 {
            font-size: 1rem;
            font-weight: 700;
            color: white;
        }

        .sidebar-user .meta p {
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .sidebar-nav {
            margin-bottom: 1.5rem;
        }

        .sidebar-nav a,
        .sidebar-nav button {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            width: 100%;
            padding: 0.95rem 1.5rem;
            border-radius: 16px;
            text-decoration: none;
            color: #cbd5e1;
            border: none;
            background: transparent;
            transition: background 0.2s ease, color 0.2s ease;
            font-weight: 500;
            text-align: left;
        }

        .sidebar-nav a .icon,
        .sidebar-nav button .icon {
            width: 1.2rem;
            display: inline-flex;
            justify-content: center;
        }

        .sidebar-nav a:hover,
        .sidebar-nav button:hover,
        .sidebar-nav a.active {
            background: rgba(220, 38, 38, 0.18);
            color: white;
        }

        .sidebar-nav a.active {
            box-shadow: inset 3px 0 0 #dc2626;
        }

        .sidebar-nav .badge {
            margin-left: auto;
            font-size: 0.75rem;
            background: var(--admin-accent);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-weight: 600;
        }

        .sidebar-close {
            display: none;
            border: none;
            background: transparent;
            color: rgba(241, 245, 249, 0.85);
            font-size: 1.15rem;
            padding: 0.75rem 1.25rem;
            cursor: pointer;
            margin-left: auto;
        }

        .sidebar-footer {
            margin-top: auto;
            padding-top: 1rem;
        }

        .sidebar-footer form button {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: white;
            padding: 0.95rem 1rem;
            background: rgba(248, 250, 252, 0.03);
            transition: all 0.2s ease;
        }

        .sidebar-footer form button:hover {
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.3);
        }

        .admin-main {
            margin-left: 280px;
            flex: 1;
            min-height: 100vh;
            transition: margin-left 0.28s ease;
            padding: 0 0 2rem;
        }

        .admin-main.sidebar-hidden {
            margin-left: 0;
        }

        .admin-topbar {
            position: sticky;
            top: 0;
            z-index: 900;
            background: #ffffff;
            border-bottom: 1px solid rgba(148, 163, 184, 0.18);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.75rem;
            backdrop-filter: blur(14px);
        }

        .admin-topbar .topbar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .admin-topbar h1 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 800;
        }

        .admin-topbar p {
            margin: 0;
            color: #64748b;
            font-size: 0.95rem;
        }

        .admin-toggle {
            display: none;
            border: none;
            background: rgba(220, 38, 38, 0.12);
            color: #dc2626;
            border-radius: 14px;
            width: 46px;
            height: 46px;
            font-size: 1.1rem;
            cursor: pointer;
        }

        .admin-panel {
            padding: 1.5rem 1.75rem 0;
            max-width: 1320px;
            margin: 0 auto;
        }

        .admin-row {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: repeat(12, minmax(0, 1fr));
        }

        .admin-card {
            background: var(--admin-surface);
            border-radius: 24px;
            border: 1px solid rgba(148, 163, 184, 0.16);
            box-shadow: var(--admin-shadow);
            padding: 1.6rem;
        }

        .admin-card h2,
        .admin-card h3,
        .admin-card h4,
        .admin-card h5 {
            margin: 0 0 0.8rem;
            color: var(--admin-surface-strong);
        }

        .admin-grid {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        .stat-card {
            background: white;
            border-radius: 24px;
            border: 1px solid rgba(148, 163, 184, 0.12);
            padding: 1.5rem;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(220, 38, 38, 0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .stat-card:hover {
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.12);
            transform: translateY(-2px);
            border-color: rgba(220, 38, 38, 0.25);
        }

        .stat-card .stat-label {
            color: #64748b;
            font-size: 0.92rem;
            margin-bottom: 0.75rem;
            font-weight: 700;
        }

        .stat-card .stat-value {
            font-size: 2.25rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 0.5rem;
        }

        .stat-card .stat-note {
            color: #475569;
            font-size: 0.95rem;
        }

        .hero-welcome {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border-radius: 28px;
            padding: 2.5rem;
            display: grid;
            gap: 1.5rem;
            grid-template-columns: minmax(0, 1fr);
            box-shadow: 0 24px 80px rgba(15, 23, 42, 0.08);
            border: 1px solid rgba(220, 38, 38, 0.2);
            position: relative;
            overflow: hidden;
        }

        .hero-welcome::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(239, 68, 68, 0.15) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-heading {
            font-size: clamp(2rem, 3vw, 2.75rem);
            margin: 0;
            color: #0f172a;
            line-height: 1.05;
        }

        .hero-text {
            max-width: 56rem;
            color: #475569;
            font-size: 1rem;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.9rem;
        }

        .hero-actions .btn {
            min-width: 170px;
            border-radius: 999px;
            font-weight: 600;
        }

        .recent-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: 1rem;
        }

        .recent-item {
            background: white;
            border-radius: 20px;
            border: 1px solid rgba(148, 163, 184, 0.12);
            padding: 1.25rem;
            display: grid;
            gap: 0.75rem;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.06);
        }

        .recent-item .meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .recent-item .meta span {
            color: #64748b;
            font-size: 0.95rem;
        }

        .recent-item .badge {
            font-size: 0.8rem;
            font-weight: 700;
        }

        .btn-soft {
            background: white;
            color: #0f172a;
            border: 1px solid rgba(148, 163, 184, 0.18);
        }

        .btn-soft:hover {
            background: #f8fafc;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1.25rem;
        }

        .section-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 800;
        }

        .section-header p {
            margin: 0;
            color: #64748b;
        }

        .content-section {
            padding-top: 1rem;
        }

        .breadcrumb-links {
            display: inline-flex;
            gap: 0.35rem;
            align-items: center;
            color: #64748b;
            font-size: 0.94rem;
        }

        .breadcrumb-links a {
            color: #dc2626;
        }

        .page-section {
            margin-top: 0.75rem;
        }

        /* Modal Styles */
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
        }

        .modal-header {
            border-bottom: 1px solid #e5e7eb;
            padding: 1.5rem 2rem;
        }

        .modal-title {
            font-weight: 700;
            color: #111827;
        }

        .modal-body {
            padding: 2rem;
        }

        .modal-footer {
            border-top: 1px solid #e5e7eb;
            padding: 1.5rem 2rem;
        }

        /* Table Styles */
        .table {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .table thead th {
            background: #f8fafc;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 700;
            color: #374151;
            padding: 1rem;
        }

        .table tbody td {
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .table tbody tr:hover {
            background: #f8fafc;
        }

        /* Image Styles */
        .profile-image {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            object-fit: cover;
            border: 2px solid #e5e7eb;
        }

        .document-preview {
            max-width: 200px;
            max-height: 150px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid #e5e7eb;
        }

        /* Badge Styles */
        .badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-approved {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-available {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-unavailable {
            background: #f3f4f6;
            color: #374151;
        }

        @media (max-width: 991px) {
            .admin-sidebar {
                transform: translateX(-100%);
                max-width: 100%;
                width: 100%;
            }

            .admin-sidebar.open {
                transform: translateX(0);
            }

            .admin-main {
                margin-left: 0;
            }

            .admin-toggle {
                display: inline-flex;
            }

            .sidebar-close {
                display: inline-flex;
            }
        }

        @media (max-width: 768px) {
            .admin-topbar {
                padding: 1rem 1rem;
            }

            .admin-panel {
                padding: 1rem;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    <div class="admin-shell">
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-brand">
                <div class="brand-mark"><i class="fas fa-shield-alt"></i></div>
                <span>Admin Panel</span>
                <button class="sidebar-close" type="button" onclick="toggleSidebar()"><i class="fas fa-times"></i></button>
            </div>

            <div class="sidebar-user">
                <div class="avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                <div class="meta">
                    <h4>{{ Auth::user()->name }}</h4>
                    <p>Administrator</p>
                </div>
            </div>

            <nav class="sidebar-nav">
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-home"></i></span>
                    Dashboard
                </a>
                <a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-users"></i></span>
                    Manage Users
                </a>
                <a href="{{ route('admin.drivers') }}" class="{{ request()->routeIs('admin.drivers*') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-user-tie"></i></span>
                    Drivers
                </a>
                <a href="{{ route('admin.pending-drivers') }}" class="{{ request()->routeIs('admin.pending-drivers') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-user-clock"></i></span>
                    Pending Drivers
                    @if(isset($stats) && $stats['pending_drivers'] > 0)
                        <span class="badge">{{ $stats['pending_drivers'] }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.vehicles') }}" class="{{ request()->routeIs('admin.vehicles*') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-car"></i></span>
                    Vehicles
                </a>
                <a href="{{ route('admin.pending-vehicles') }}" class="{{ request()->routeIs('admin.pending-vehicles') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-car-side"></i></span>
                    Pending Vehicles
                    @if(isset($stats) && $stats['pending_vehicles'] > 0)
                        <span class="badge">{{ $stats['pending_vehicles'] }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.bookings') }}" class="{{ request()->routeIs('admin.bookings*') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-calendar-check"></i></span>
                    Bookings
                </a>
                <a href="{{ route('admin.pending-bookings') }}" class="{{ request()->routeIs('admin.pending-bookings') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-hourglass-half"></i></span>
                    Pending Bookings
                    @if(isset($stats) && $stats['pending_bookings'] > 0)
                        <span class="badge">{{ $stats['pending_bookings'] }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.service-requests.index') }}" class="{{ request()->routeIs('admin.service-requests.*') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-clipboard-check"></i></span>
                    Service Requests
                </a>
                <a href="{{ route('admin.assignments.index') }}" class="{{ request()->routeIs('admin.assignments.*') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-route"></i></span>
                    Assignments & Feedback
                </a>
                <a href="{{ route('admin.bookings', ['guest' => 1]) }}" class="{{ request()->routeIs('admin.bookings*') && request('guest') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-user-secret"></i></span>
                    Guest Bookings
                </a>
                <a href="{{ route('admin.pending-payments') }}" class="{{ request()->routeIs('admin.pending-payments') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-credit-card"></i></span>
                    Pending Payments
                    @if(isset($stats) && $stats['pending_payments'] > 0)
                        <span class="badge">{{ $stats['pending_payments'] }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.applications') }}" class="{{ request()->routeIs('admin.applications') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-file-alt"></i></span>
                    All Applications
                </a>
                <a href="{{ route('admin.recruitment-review') }}" class="{{ request()->routeIs('admin.recruitment-review') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-clipboard-list"></i></span>
                    Review Applications
                    @if(isset($stats) && $stats['pending_approvals'] > 0)
                        <span class="badge">{{ $stats['pending_approvals'] }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.recruitments') }}" class="{{ request()->routeIs('admin.recruitments*') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-briefcase"></i></span>
                    Recruitments
                </a>
                <a href="{{ route('admin.analytics') }}" class="{{ request()->routeIs('admin.analytics') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-chart-pie"></i></span>
                    Reports
                </a>
            </nav>

            <div class="sidebar-footer">
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit">
                        <span class="icon"><i class="fas fa-sign-out-alt"></i></span>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <div class="admin-main" id="adminMain">
            <div class="admin-topbar">
                <div class="topbar-left">
                    <button class="admin-toggle" type="button" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                    <div>
                        <h1>@yield('page-title', 'Admin Dashboard')</h1>
                        <p>@yield('page-description', 'Manage your platform efficiently')</p>
                    </div>
                </div>
                <div class="topbar-actions">
                    <button class="admin-toggle btn btn-outline-secondary d-lg-none" type="button" onclick="toggleSidebar()">
                        <i class="fas fa-sliders-h"></i>
                    </button>
                </div>
            </div>
            <main class="admin-panel">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "4000"
        };

        @if (session('success'))
            toastr.success(@json(session('success')));
        @endif
        @if (session('error'))
            toastr.error(@json(session('error')));
        @endif
        @if (session('warning'))
            toastr.warning(@json(session('warning')));
        @endif

        function toggleSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const main = document.getElementById('adminMain');
            sidebar.classList.toggle('open');
            main.classList.toggle('sidebar-hidden');
        }

        // Auto-hide sidebar on mobile when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('adminSidebar');
            const toggle = document.querySelector('.admin-toggle');
            const isMobile = window.innerWidth < 992;

            if (isMobile && !sidebar.contains(event.target) && !toggle.contains(event.target)) {
                sidebar.classList.remove('open');
                document.getElementById('adminMain').classList.remove('sidebar-hidden');
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('adminSidebar');
            const main = document.getElementById('adminMain');
            const isMobile = window.innerWidth < 992;

            if (!isMobile) {
                sidebar.classList.remove('open');
                main.classList.remove('sidebar-hidden');
            }
        });
    </script>

    @stack('scripts')
    @yield('scripts')
</body>
</html>