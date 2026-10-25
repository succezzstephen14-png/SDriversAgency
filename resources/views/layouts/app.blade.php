<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $siteSettings ? $siteSettings->site_title : 'Vehicle & Driver Recruitment System')</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-dark: {{ $siteSettings ? $siteSettings->background_color : '#ffffff' }};
            --bg-surface: rgba(0, 0, 0, 0.05);
            --bg-card: rgba(255, 255, 255, 1);
            --text-light: {{ $siteSettings ? $siteSettings->font_color : '#1f2937' }};
            --text-muted: #6b7280;
            --primary: {{ $siteSettings ? $siteSettings->primary_color : '#3b82f6' }};
            --primary-dark: {{ $siteSettings ? $siteSettings->primary_color : '#1d4ed8' }};
            --secondary: {{ $siteSettings ? $siteSettings->secondary_color : '#10b981' }};
            --border: rgba(0, 0, 0, 0.1);
            --shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: {{ $siteSettings ? $siteSettings->font_family : "'Poppins', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif" }};
            color: {{ $siteSettings ? $siteSettings->font_color : '#111827' }};
            background: {{ $siteSettings && $siteSettings->background_image ? 'url(' . asset($siteSettings->background_image) . ') no-repeat center center fixed' : ($siteSettings ? $siteSettings->background_color : '#ffffff') }};
            background-size: cover;
        }

        @if($siteSettings && $siteSettings->custom_css)
            {!! $siteSettings->custom_css !!}
        @endif

        a {
            text-decoration: none;
        }

        .navbar {
            padding: 1rem 1rem;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            width: 100%;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .navbar .container-max {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            padding: 0 5%;
            margin: 0;
        }

        .navbar-inner {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            flex-shrink: 0;
        }

        .navbar-toggler {
            margin-left: auto;
            flex-shrink: 0;
            border-color: transparent;
            color: #2563eb;
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='%232563eb' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
        }

        .navbar-brand {
            font-weight: 800;
            color: #1f2937 !important;
            letter-spacing: -0.03em;
            flex-shrink: 0;
            padding: 0;
            margin: 0;
            white-space: nowrap;
            display: flex;
            align-items: center;
        }

        .navbar-collapse {
            flex-grow: 1 !important;
            justify-content: flex-end;
            padding: 0 !important;
        }

        .navbar-nav {
            display: flex !important;
            gap: 1rem;
            align-items: center;
        }

        .navbar-nav .nav-link {
            color: rgba(31, 41, 55, 0.88) !important;
            font-weight: 500;
            transition: color 0.2s ease;
            padding: 0.25rem 0.75rem !important;
            white-space: nowrap;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: #3b82f6 !important;
        }

        .navbar .container-max {
            flex-wrap: wrap;
        }

        @media (max-width: 992px) {
            .navbar .container-max {
                padding: 0 1rem;
            }

            .navbar-brand {
                flex: 1 1 auto;
                min-width: 0;
            }

            .navbar-collapse {
                width: 100%;
                margin-top: 0.75rem;
            }

            .navbar-nav {
                flex-direction: column !important;
                align-items: stretch;
                gap: 0;
            }

            .navbar-nav .nav-item {
                width: 100%;
            }

            .navbar-nav .nav-link {
                padding: 0.75rem 1rem !important;
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 0.75rem 0.75rem;
            }

            .hero-wrapper {
                padding: 3rem 0 2rem;
            }

            .hero-panels .panel-left,
            .hero-panels .panel-right {
                padding: 1.75rem;
            }

            .hero-text {
                max-width: 100%;
            }
        }

        @media (max-width: 576px) {
            .navbar .container-max {
                padding: 0 0.75rem;
            }

            .hero-heading {
                font-size: 2.25rem;
            }

            .hero-text {
                font-size: 0.95rem;
            }

            .hero-buttons .btn {
                min-width: 140px;
            }
        }

        .hero-wrapper {
            position: relative;
            overflow: hidden;
            padding: 5rem 0 3rem;
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        }

        .hero-panels {
            backdrop-filter: blur(24px);
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 24px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .hero-panels .panel-left,
        .hero-panels .panel-right {
            padding: 2.5rem;
        }

        .hero-heading {
            font-size: clamp(2.5rem, 4vw, 4.25rem);
            line-height: 0.98;
            font-weight: 800;
            margin-bottom: 1rem;
            color: #1f2937;
        }

        .hero-text {
            color: #6b7280;
            max-width: 40rem;
            margin-bottom: 2rem;
            font-size: 1.05rem;
        }

        .hero-buttons .btn {
            min-width: 160px;
            border-radius: 999px;
        }

        .hero-buttons .btn-outline-light {
            border-color: rgba(59, 130, 246, 0.5);
            color: #3b82f6;
        }

        .hero-buttons .btn-outline-light:hover {
            color: white;
            background: #3b82f6;
        }

        .hero-stats {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }

        .hero-stat {
            padding: 1rem 1.25rem;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(0, 0, 0, 0.1);
            color: #1f2937;
        }

        .hero-stat strong {
            display: block;
            font-size: 1.75rem;
            margin-bottom: 0.35rem;
        }

        .hero-image-frame {
            position: relative;
            border-radius: 24px;
        }

        /* Vehicle image helpers */
        .vehicle-main-image {
            max-height: 360px;
            width: 100%;
            height: auto;
            object-fit: cover;
            border-radius: 8px;
        }

        .vehicle-thumbnail {
            width: 320px;
            height: 200px;
            object-fit: cover;
            border-radius: 6px;
            display: inline-block;
        }

        .vehicle-list-thumbnail {
            width: 320px;
            max-width: 60%;
            height: 420px;
            overflow: hidden;
            border-radius: 8px;
            display: block;
            margin: 0 auto;
            flex-shrink: 0;
        }

        .vehicle-list-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .hero-image-frame::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top left, rgba(59, 130, 246, 0.2), transparent 35%), radial-gradient(circle at bottom right, rgba(16, 185, 129, 0.15), transparent 30%);
            pointer-events: none;
        }

        .hero-image-frame img {
            width: 100%;
            max-width: 560px;
            object-fit: cover;
            position: relative;
            z-index: 1;
        }

        .section-card {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.12);
            border: 1px solid rgba(15, 23, 42, 0.08);
        }

        .section-feature {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
        }

        .feature-card {
            background: #f8fafc;
            border-radius: 20px;
            padding: 1.75rem;
            border: 1px solid #e2e8f0;
        }

        .feature-card h5 {
            margin-bottom: 0.75rem;
            font-weight: 700;
        }

        .feature-card p {
            color: #475569;
        }

        .listing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .listing-card {
            background: white;
            border-radius: 20px;
            padding: 1.75rem;
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.06);
        }

        .listing-card .badge {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            font-weight: 700;
            border-radius: 999px;
            padding: 0.45rem 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            margin-bottom: 1rem;
            font-size: 0.85rem;
        }

        .listing-card h5 {
            margin-bottom: 0.85rem;
            font-size: 1.15rem;
        }

        .listing-card p {
            color: #475569;
            margin-bottom: 0.45rem;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
        }

        section.section-primary .section-title {
            color: white;
        }

        .section-subtitle {
            font-size: 1.1rem;
            color: #6b7280;
            margin-bottom: 2rem;
            font-weight: 500;
        }

        section.section-primary .section-subtitle {
            color: rgba(255, 255, 255, 0.85);
        }

        /* Full-Width Sections - Enhanced Styling */
        section {
            margin: 0;
            padding: 0;
        }

        section.section-light {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
        }

        section.section-dark {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }

        section.section-primary {
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            color: white;
        }

        section.section-accent {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        }

        /* Ensure Alert Messages Work with New Layout */
        .container-max.alert-container {
            width: 100vw;
            position: relative;
            left: 50%;
            right: 50%;
            margin-left: -50vw;
            margin-right: -50vw;
            padding: 1rem 5%;
        }

        .footer {
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            color: #d1d5db;
            width: 100%;
            padding-bottom: 2rem;
            margin-top: 4rem;
        }

        .footer .container-max {
            padding: 4rem 5% 2rem 5%;
        }

        .footer h5 {
            color: #ffffff;
            font-weight: 600;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
        }

        .footer p {
            color: #9ca3af;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .footer a {
            color: #d1d5db;
            transition: color 0.3s ease;
            text-decoration: none;
        }

        .footer a:hover {
            color: #3b82f6;
        }

        .footer ul li {
            margin-bottom: 0.75rem;
        }

        .footer ul li a {
            color: #d1d5db;
            transition: all 0.3s ease;
            padding: 0.25rem 0;
            display: inline-block;
        }

        .footer ul li a:hover {
            color: #3b82f6;
            transform: translateX(4px);
        }

        .footer hr {
            border-color: rgba(255, 255, 255, 0.1);
            margin: 2rem 0;
        }

        .footer .text-muted {
            color: #9ca3af !important;
        }

        .footer-bottom-links a {
            color: #9ca3af;
            transition: color 0.3s ease;
            text-decoration: none;
        }

        .footer-bottom-links a:hover {
            color: #3b82f6;
        }

        .footer a {
            color: rgba(241, 245, 249, 0.75);
            transition: color 0.2s ease;
        }

        .footer a:hover {
            color: white;
        }

        .footer .social-links {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            border: 1px solid rgba(59, 130, 246, 0.2);
            transition: all 0.3s ease;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .footer .social-links:hover {
            background: #3b82f6;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .footer .social-links i {
            font-size: 1.1rem;
        }

        .social-links-container {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .container-max {
            width: 100%;
            padding: 0 5%;
            margin: 0 auto;
        }

        /* Full-Width Section Styles */
        section {
            width: 100%;
            position: relative;
            left: auto;
            right: auto;
            margin-left: 0;
            margin-right: 0;
        }

        .section-inner {
            width: 100%;
            padding: 60px 5%;
            margin: 0 auto;
        }

        /* Alternating Section Backgrounds */
        section.section-light {
            background: #ffffff;
        }

        section.section-dark {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }

        section.section-primary {
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            color: white;
        }

        section.section-accent {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        }

        @media (max-width: 991px) {
            .hero-panels {
                display: block;
            }

            .hero-panels .panel-left,
            .hero-panels .panel-right {
                padding: 2rem;
            }

            .hero-image-frame {
                min-height: 360px;
            }

            .section-inner {
                padding: 50px 5%;
            }
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 0.75rem 0;
            }

            .hero-heading {
                font-size: 2.6rem;
            }

            .section-card {
                padding: 1.75rem;
            }

            .section-inner {
                padding: 40px 5%;
            }
        }

        @media (max-width: 480px) {
            .section-inner {
                padding: 30px 5%;
            }
        }

        /* Footer Responsive Styles */
        @media (max-width: 768px) {
            .footer .container-max {
                padding: 3rem 5% 1.5rem 5%;
            }

            .footer h5 {
                font-size: 1rem;
                margin-bottom: 1rem;
            }

            .social-links-container {
                gap: 0.5rem;
            }

            .footer .social-links {
                width: 38px;
                height: 38px;
            }

            .footer .social-links i {
                font-size: 1rem;
            }

            .footer-bottom-links {
                text-align: center;
            }

            .footer-bottom-links a {
                margin: 0 0.5rem;
            }
        }

        @media (max-width: 480px) {
            .footer .container-max {
                padding: 2rem 5% 1rem 5%;
            }

            .footer {
                margin-top: 2rem;
                padding-bottom: 1rem;
            }

            .social-links-container {
                justify-content: center;
                gap: 0.75rem;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-max">
            @php
                $navItems = $headerItems->where('section', 'nav')->sortBy('order');
                $logoItem = $headerItems->firstWhere('section', 'logo');
            @endphp

            <a class="navbar-brand" href="{{ route('home') }}">
                @if($logoItem)
                    @if($logoItem->type === 'logo' && $logoItem->image_url)
                        <img src="{{ asset($logoItem->image_url) }}" alt="{{ $logoItem->title ?? 'Logo' }}" height="40" class="me-2">
                    @elseif($logoItem->icon)
                        <i class="{{ $logoItem->icon }} me-2"></i>
                    @else
                        <i class="fas fa-car-side me-2"></i>
                    @endif
                    {{ $logoItem->title ?? $siteSettings->site_title ?? 'SUCCEZZ Drivers Agency' }}
                @else
                    <i class="fas fa-car-side me-2"></i> {{ $siteSettings->site_title ?? 'SUCCEZZ Drivers Agency' }}
                @endif
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    @if($navItems->count())
                        @foreach($navItems as $item)
                            <li class="nav-item">
                                <a class="nav-link {{ url($item->link) === url()->current() ? 'active' : '' }}" href="{{ $item->link ?: '#' }}">{{ $item->title ?? $item->label }}</a>
                            </li>
                        @endforeach
                    @else
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#services">Services</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#contact">Contact</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('vehicles.*') ? 'active' : '' }}" href="{{ route('vehicles.index') }}">Vehicles</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('recruitments.*') ? 'active' : '' }}" href="{{ route('recruitments.index') }}">Recruitment</a></li>
                    @endif

                    <li class="nav-item dropdown">
                        <a class="btn btn-primary dropdown-toggle ms-lg-3 my-2 my-lg-0" href="#" id="serviceRequestMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Request a service
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="serviceRequestMenu">
                            <li><a class="dropdown-item" href="{{ route('service.register.driver') }}"><i class="fas fa-id-card me-2"></i>Request a driver</a></li>
                            <li><a class="dropdown-item" href="{{ route('service.register.vehicle') }}"><i class="fas fa-car me-2"></i>Request a vehicle</a></li>
                        </ul>
                    </li>

                    @auth
                        <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('bookings.my-bookings') }}">My Bookings</a></li>
                    @else
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login / Register</a></li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    @if ($errors->any())
        <div class="container-max mt-4">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> Please fix the following errors:
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    @if (session('success'))
        <div class="container-max mt-4">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    @if (session('info'))
        <div class="container-max mt-4">
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    @yield('content')

    <footer class="footer">
        @php
            $footerMain = $footerItems->where('section', 'main')->sortBy('order');
            $footerColumn1 = $footerItems->where('section', 'column1')->sortBy('order');
            $footerColumn2 = $footerItems->where('section', 'column2')->sortBy('order');
            $footerBottom = $footerItems->where('section', 'bottom')->sortBy('order');
        @endphp

        <div class="container-max">
            <div class="row gy-4">
                <div class="col-lg-4">
                    @if($footerMain->count())
                        @foreach($footerMain as $item)
                            @if($item->type === 'text')
                                <h5>{{ $item->title }}</h5>
                                <p>{{ $item->content }}</p>
                            @elseif(in_array($item->type, ['label', 'logo']))
                                <h5>{{ $item->title ?? $item->label }}</h5>
                                @if($item->content)
                                    <p>{{ $item->content }}</p>
                                @endif
                            @endif
                        @endforeach
                    @else
                        <h5>Vehicle & Driver Recruitment</h5>
                        <p>Secure matches for logistics teams, drivers, and vehicle owners — all in one polished portal.</p>
                    @endif
                </div>
                <div class="col-lg-4">
                    @if($footerColumn1->count())
                        @php $columnLabel = $footerColumn1->firstWhere('type', 'label') ?? $footerColumn1->first(); @endphp
                        <h5>{{ $columnLabel->title ?? $columnLabel->label }}</h5>
                        <ul class="list-unstyled">
                            @foreach($footerColumn1->where('type', 'link') as $item)
                                <li><a href="{{ $item->link }}">{{ $item->title }}</a></li>
                            @endforeach
                        </ul>
                    @else
                        <h5>Explore</h5>
                        <ul class="list-unstyled">
                            <li><a href="{{ route('home') }}">Home</a></li>
                            <li><a href="{{ route('vehicles.index') }}">Vehicles</a></li>
                            <li><a href="{{ route('recruitments.index') }}">Recruitments</a></li>
                        </ul>
                    @endif
                </div>
                <div class="col-lg-4">
                    @if($footerColumn2->count())
                        @php $columnLabel = $footerColumn2->firstWhere('type', 'label') ?? $footerColumn2->first(); @endphp
                        <h5>{{ $columnLabel->title ?? $columnLabel->label }}</h5>
                        <div class="social-links-container mb-3">
                            @foreach($footerColumn2->where('type', 'social') as $item)
                                <a href="{{ $item->link }}" class="social-links" title="{{ $item->title }}" target="_blank" rel="noopener noreferrer">
                                    <i class="{{ $item->icon }}"></i>
                                </a>
                            @endforeach
                        </div>
                        @foreach($footerColumn2->where('type', 'text') as $item)
                            <p>{{ $item->content }}</p>
                        @endforeach
                    @else
                        <h5>Connect</h5>
                        <div class="social-links-container mb-3">
                            <a href="https://www.facebook.com/share/1CQarEnFX4/?mibextid=wwXIfr" class="social-links" title="Follow us on Facebook" target="_blank" rel="noopener noreferrer">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="social-links" title="Follow us on Twitter" target="_blank" rel="noopener noreferrer">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="social-links" title="Connect on LinkedIn" target="_blank" rel="noopener noreferrer">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a href="https://www.instagram.com/succezzdriversagency?igsh=MWxnand6b3Zlcmc5OQ%3D%3D&utm_source=qr" class="social-links" title="Follow us on Instagram" target="_blank" rel="noopener noreferrer">
                                <i class="fab fa-instagram"></i>
                            </a>
                        </div>
                        <p class="mb-0">Ready to recruit or apply? Start with your profile today.</p>
                    @endif
                </div>
            </div>
            <hr class="my-4 border-white border-opacity-10">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 text-muted small">
                <span>{{ optional($footerBottom->firstWhere('type', 'text'))->content ?? $siteSettings?->footer_text ?? '© 2026 SUCCEZZ Drivers Agency. All rights reserved.' }}</span>
                <div class="footer-bottom-links">
                    @if($footerBottom->where('type', 'link')->count())
                        @foreach($footerBottom->where('type', 'link') as $item)
                            <a href="{{ $item->link }}">{{ $item->title }}</a>
                        @endforeach
                    @else
                        <a href="{{ route('privacy.policy') }}">Privacy Policy</a>
                        <a href="{{ route('terms.conditions') }}">Terms of Service</a>
                        <a href="{{ route('home') }}#contact">Support</a>
                    @endif
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var navbarCollapseEl = document.getElementById('navbarNav');
            var navbarToggleEl = document.querySelector('.navbar-toggler');
            var collapseInstance = null;

            if (navbarCollapseEl) {
                collapseInstance = bootstrap.Collapse.getOrCreateInstance(navbarCollapseEl, {
                    toggle: false
                });
            }

            function isMobileView() {
                return window.innerWidth <= 992;
            }

            if (navbarToggleEl && collapseInstance) {
                navbarToggleEl.addEventListener('mouseenter', function () {
                    if (isMobileView() && !navbarCollapseEl.classList.contains('show')) {
                        collapseInstance.show();
                    }
                });

                navbarCollapseEl.addEventListener('mouseleave', function () {
                    if (isMobileView() && navbarCollapseEl.classList.contains('show')) {
                        collapseInstance.hide();
                    }
                });

                navbarCollapseEl.querySelectorAll('.nav-link').forEach(function (link) {
                    link.addEventListener('click', function () {
                        if (isMobileView() && navbarCollapseEl.classList.contains('show')) {
                            collapseInstance.hide();
                        }
                    });
                });
            }
        });
    </script>
    <script>
        @auth
        (function(){
            if (!('geolocation' in navigator)) return;
            let watchId = null;
            const sendLocation = (pos) => {
                const lat = pos.coords.latitude;
                const lon = pos.coords.longitude;
                const trackingLocation = `${lat.toFixed(6)}, ${lon.toFixed(6)}`;

                fetch("{{ route('location.update') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ latitude: lat, longitude: lon, tracking_location: trackingLocation })
                }).catch(err => console.error('Location update failed', err));
            };

            // Start watching position, send immediately and then on change
            try {
                watchId = navigator.geolocation.watchPosition(sendLocation, (err) => console.warn('Geolocation error', err), { enableHighAccuracy: true, maximumAge: 15000, timeout: 10000 });

                // Also send current position once
                navigator.geolocation.getCurrentPosition(sendLocation, () => {}, { enableHighAccuracy: true });
            } catch (e) {
                console.warn('Geolocation not permitted', e);
            }

            // Stop watching when leaving page
            window.addEventListener('beforeunload', function () {
                if (watchId !== null && navigator.geolocation.clearWatch) navigator.geolocation.clearWatch(watchId);
            });
        })();
        @endauth
    </script>

    @yield('scripts')
</body>
</html>
