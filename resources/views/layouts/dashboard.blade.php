<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard - Vehicle & Driver Recruitment')</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --dashboard-bg: #f8fafc;
            --dashboard-surface: #ffffff;
            --dashboard-surface-strong: #0f172a;
            --dashboard-text: #334155;
            --dashboard-muted: #64748b;
            --dashboard-primary: #0ea5e9;
            --dashboard-primary-dark: #0284c7;
            --dashboard-primary-soft: #e0f2fe;
            --dashboard-accent: #8b5cf6;
            --dashboard-border: rgba(148, 163, 184, 0.12);
            --dashboard-shadow: 0 20px 60px rgba(15, 23, 42, 0.08);
            --dashboard-shadow-sm: 0 4px 12px rgba(15, 23, 42, 0.06);
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            min-height: 100%;
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: var(--dashboard-text);
            background: var(--dashboard-bg);
        }

        body {
            overflow-x: hidden;
        }

        .dashboard-shell {
            display: flex;
            min-height: 100vh;
        }

        .dashboard-sidebar {
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

        .dashboard-sidebar.open {
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
            background: linear-gradient(135deg, #0ea5e9, #06b6d4);
            box-shadow: 0 16px 40px rgba(14, 165, 233, 0.3);
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
        }

        .sidebar-user .avatar {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: rgba(37, 99, 235, 0.18);
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
            background: rgba(14, 165, 233, 0.18);
            color: white;
        }

        .sidebar-nav a.active {
            box-shadow: inset 3px 0 0 #0ea5e9;
        }

        .sidebar-nav .badge {
            margin-left: auto;
            font-size: 0.75rem;
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
        }

        .dashboard-main {
            margin-left: 280px;
            flex: 1;
            min-height: 100vh;
            transition: margin-left 0.28s ease;
            padding: 0 0 2rem;
        }

        .dashboard-main.sidebar-hidden {
            margin-left: 0;
        }

        .dashboard-topbar {
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

        .dashboard-topbar .topbar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .dashboard-topbar h1 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 800;
        }

        .dashboard-topbar p {
            margin: 0;
            color: #64748b;
            font-size: 0.95rem;
        }

        .dashboard-toggle {
            display: none;
            border: none;
            background: rgba(37, 99, 235, 0.12);
            color: #2563eb;
            border-radius: 14px;
            width: 46px;
            height: 46px;
            font-size: 1.1rem;
            cursor: pointer;
        }

        .sidebar-backdrop {
            display: none;
        }

        .dashboard-panel {
            padding: 1.5rem 1.75rem 0;
            max-width: 1320px;
            margin: 0 auto;
        }

        .dashboard-row {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: repeat(12, minmax(0, 1fr));
        }

        .dashboard-card {
            background: var(--dashboard-surface);
            border-radius: 24px;
            border: 1px solid rgba(148, 163, 184, 0.16);
            box-shadow: var(--dashboard-shadow);
            padding: 1.6rem;
        }

        .dashboard-card h2,
        .dashboard-card h3,
        .dashboard-card h4,
        .dashboard-card h5 {
            margin: 0 0 0.8rem;
            color: var(--dashboard-surface-strong);
        }

        .dashboard-grid {
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
            background: radial-gradient(circle, rgba(14, 165, 233, 0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .stat-card:hover {
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.12);
            transform: translateY(-2px);
            border-color: rgba(14, 165, 233, 0.25);
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
            background: linear-gradient(135deg, #e0f2fe 0%, #cffafe 100%);
            border-radius: 28px;
            padding: 2.5rem;
            display: grid;
            gap: 1.5rem;
            grid-template-columns: minmax(0, 1fr);
            box-shadow: 0 24px 80px rgba(15, 23, 42, 0.08);
            border: 1px solid rgba(14, 165, 233, 0.2);
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
            background: radial-gradient(circle, rgba(6, 182, 212, 0.15) 0%, transparent 70%);
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
            color: #2563eb;
        }

        .page-section {
            margin-top: 0.75rem;
        }

        @media (max-width: 991px) {
            .dashboard-sidebar {
                transform: translateX(-100%);
                width: min(320px, 88vw);
                height: 100vh;
                height: 100dvh;
            }

            .dashboard-sidebar.open {
                transform: translateX(0);
            }

            .sidebar-backdrop.open {
                display: block;
                position: fixed;
                inset: 0;
                z-index: 1100;
                border: 0;
                background: rgba(15, 23, 42, 0.5);
            }

            body.sidebar-open {
                overflow: hidden;
            }

            .dashboard-main {
                margin-left: 0;
            }

            .dashboard-toggle {
                display: inline-flex;
            }

            .sidebar-close {
                display: inline-flex;
            }

            .dashboard-topbar .topbar-left {
                flex: 1;
                min-width: 0;
                flex-wrap: nowrap;
            }

            .dashboard-topbar .topbar-left > div {
                min-width: 0;
            }

            .dashboard-topbar p {
                font-size: 0.8rem;
                line-height: 1.35;
            }
        }

        @media (max-width: 768px) {
            .dashboard-topbar {
                padding: 1rem 1rem;
            }

            .dashboard-panel {
                padding: 1rem;
            }
        }

        @media (max-width: 480px) {
            .dashboard-topbar {
                gap: 0.65rem;
                padding: 0.75rem;
            }

            .dashboard-topbar .topbar-left {
                gap: 0.65rem;
            }

            .dashboard-topbar h1 {
                font-size: 1.05rem;
            }

            .dashboard-toggle {
                width: 42px;
                height: 42px;
                flex: 0 0 42px;
            }

            .dashboard-card {
                padding: 1rem;
                border-radius: 18px;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    <div class="dashboard-shell">
        <aside class="dashboard-sidebar" id="dashboardSidebar">
            <div class="sidebar-brand">
                <div class="brand-mark"><i class="fas fa-car-side"></i></div>
                <span>SUCCEZZ Dashboard</span>
                <button class="sidebar-close" type="button" onclick="toggleSidebar()"><i class="fas fa-times"></i></button>
            </div>

            <div class="sidebar-user">
                <div class="avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                <div class="meta">
                    <h4>{{ Auth::user()->name }}</h4>
                    <p>{{ ucfirst(Auth::user()->role ?: 'User') }}</p>
                </div>
            </div>

            <nav class="sidebar-nav">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-home"></i></span>
                    Dashboard
                </a>
                <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-user-circle"></i></span>
                    Complete Profile
                </a>
                <a href="{{ route('drivers.create') }}" class="{{ request()->routeIs('drivers.create') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-user-tie"></i></span>
                    Create Driver
                </a>
                <a href="{{ route('vehicles.create') }}" class="{{ request()->routeIs('vehicles.create') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-car-side"></i></span>
                    Create Vehicle
                </a>
                <a href="{{ route('vehicles.my-vehicles') }}" class="{{ request()->routeIs('vehicles.my-vehicles') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-list"></i></span>
                    My Vehicles
                </a>
                <a href="{{ route('vehicles.pending-vehicles') }}" class="{{ request()->routeIs('vehicles.pending-vehicles') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-clock"></i></span>
                    Pending Vehicles
                </a>
                <a href="{{ route('bookings.my-bookings') }}" class="{{ request()->routeIs('bookings.my-bookings') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-calendar-check"></i></span>
                    My Bookings
                </a>
                <a href="{{ route('profile') }}" class="{{ request()->routeIs('profile') ? 'active' : '' }}">
                    <span class="icon"><i class="fas fa-cog"></i></span>
                    Settings
                </a>
            </nav>

            <div class="sidebar-footer">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit">
                        <span class="icon"><i class="fas fa-sign-out-alt"></i></span>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <button class="sidebar-backdrop" id="sidebarBackdrop" type="button" aria-label="Close dashboard navigation" onclick="setSidebarOpen(false)"></button>

        <div class="dashboard-main" id="dashboardMain">
            <div class="dashboard-topbar">
                <div class="topbar-left">
                    <button class="dashboard-toggle" id="dashboardToggle" type="button" aria-label="Open dashboard navigation" aria-controls="dashboardSidebar" aria-expanded="false" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                    <div>
                        <h1>Dashboard</h1>
                        <p>Access your profile, bookings, applications and quick actions from one place.</p>
                    </div>
                </div>
            </div>
            <main class="dashboard-panel">
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        const sidebar = document.getElementById('dashboardSidebar');
        const sidebarBackdrop = document.getElementById('sidebarBackdrop');
        const dashboardToggle = document.getElementById('dashboardToggle');

        function toggleSidebar() {
            setSidebarOpen(!sidebar.classList.contains('open'));
        }

        function setSidebarOpen(isOpen) {
            sidebar.classList.toggle('open', isOpen);
            sidebarBackdrop.classList.toggle('open', isOpen);
            dashboardToggle.setAttribute('aria-expanded', String(isOpen));
            dashboardToggle.setAttribute('aria-label', isOpen ? 'Close dashboard navigation' : 'Open dashboard navigation');
            document.body.classList.toggle('sidebar-open', isOpen && window.innerWidth <= 991);
        }

        document.addEventListener('click', function (event) {
            if (!sidebar.contains(event.target) && !event.target.closest('.dashboard-toggle') && window.innerWidth <= 991) {
                setSidebarOpen(false);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && sidebar.classList.contains('open')) {
                setSidebarOpen(false);
            }
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth > 991) {
                setSidebarOpen(false);
            }
        });
    </script>
    @yield('scripts')
</body>
</html>
