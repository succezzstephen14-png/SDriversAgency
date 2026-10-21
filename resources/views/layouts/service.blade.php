<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Service Dashboard') - SUCCEZZ Drivers Agency</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { margin: 0; background: #f1f5f9; color: #1e293b; font-family: system-ui, sans-serif; }
        .service-shell { min-height: 100vh; }
        .service-sidebar { position: fixed; inset: 0 auto 0 0; width: 250px; padding: 1.5rem 1rem; color: #fff; background: #0f172a; z-index: 10; }
        .service-brand { font-weight: 800; font-size: 1.05rem; padding: .5rem .75rem 1.5rem; }
        .service-sidebar a { display: flex; gap: .75rem; align-items: center; color: #cbd5e1; text-decoration: none; padding: .8rem .75rem; border-radius: .65rem; }
        .service-sidebar a:hover, .service-sidebar a.active { color: #fff; background: #1e40af; }
        .service-sidebar form { margin-top: 2rem; }
        .service-main { margin-left: 250px; padding: 2rem; }
        .service-topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .service-card { border: 0; border-radius: 1rem; box-shadow: 0 8px 24px rgba(15, 23, 42, .06); }
        @media (max-width: 767px) {
            .service-sidebar { position: static; width: 100%; padding: .75rem; }
            .service-brand { padding: .25rem .5rem .75rem; }
            .service-sidebar nav { display: flex; flex-wrap: wrap; gap: .25rem; }
            .service-sidebar a { padding: .6rem; }
            .service-sidebar form { display: inline-block; margin: 0 0 0 .4rem; }
            .service-main { margin-left: 0; padding: 1rem; }
        }
    </style>
</head>
<body>
<div class="service-shell">
    <aside class="service-sidebar">
        <div class="service-brand"><i class="fas fa-road me-2"></i>Service Portal</div>
        <nav aria-label="Service portal navigation">
            <a href="{{ route('service.dashboard') }}" class="{{ request()->routeIs('service.dashboard') ? 'active' : '' }}"><i class="fas fa-gauge"></i>Dashboard</a>
            <a href="{{ route('service.requests.index') }}" class="{{ request()->routeIs('service.requests.*') ? 'active' : '' }}"><i class="fas fa-list-check"></i>My requests</a>
            <a href="{{ route('service.requests.create', 'driver') }}"><i class="fas fa-id-card"></i>Request a driver</a>
            <a href="{{ route('service.requests.create', 'vehicle') }}"><i class="fas fa-car"></i>Request a vehicle</a>
            <a href="{{ route('service.profile') }}" class="{{ request()->routeIs('service.profile') ? 'active' : '' }}"><i class="fas fa-user-gear"></i>Account settings</a>
        </nav>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-light btn-sm w-100"><i class="fas fa-right-from-bracket me-2"></i>Sign out</button>
        </form>
    </aside>
    <main class="service-main">
        <div class="service-topbar">
            <div>
                <h1 class="h3 mb-1">@yield('heading', 'Service dashboard')</h1>
                <div class="text-muted">Welcome, {{ Auth::user()->name }}</div>
            </div>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm">Visit website</a>
        </div>
        @foreach (['success', 'info', 'warning'] as $flash)
            @if (session($flash))
                <div class="alert alert-{{ $flash === 'success' ? 'success' : ($flash === 'warning' ? 'warning' : 'info') }}">{{ session($flash) }}</div>
            @endif
        @endforeach
        @if ($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        @yield('content')
    </main>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
