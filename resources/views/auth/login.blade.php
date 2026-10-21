@extends('layouts.app')

@section('title', 'Login')

@section('content')
<style>
    .auth-screen {
        min-height: calc(100vh - 100px);
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 2rem;
        padding: 2rem 3rem;
        align-items: center;
    }

    .auth-visual {
        position: relative;
        border-radius: 32px;
        overflow: hidden;
        min-height: 640px;
        background: linear-gradient(180deg, #0f172a 0%, #111827 100%);
        box-shadow: 0 30px 90px rgba(15, 23, 42, 0.25);
    }

    .auth-visual::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top left, rgba(59, 130, 246, 0.24), transparent 28%),
                    radial-gradient(circle at bottom right, rgba(14, 165, 233, 0.18), transparent 32%);
        pointer-events: none;
    }

    .visual-copy {
        position: relative;
        z-index: 1;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 1.5rem;
        padding: 3rem;
        color: #f8fafc;
    }

    .brand {
        display: inline-flex;
        align-items: center;
        gap: 0.85rem;
        font-weight: 800;
        letter-spacing: -0.03em;
        font-size: 1.1rem;
    }

    .brand-mark {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.16);
        display: grid;
        place-items: center;
    }

    .visual-copy h1 {
        margin: 0;
        font-size: clamp(3rem, 4vw, 4.5rem);
        line-height: 1.02;
        max-width: 14rem;
    }

    .visual-copy p {
        max-width: 32rem;
        font-size: 1.05rem;
        line-height: 1.8;
        color: rgba(241, 245, 249, 0.88);
    }

    .feature-list {
        display: grid;
        gap: 1rem;
        margin-top: 1.5rem;
    }

    .feature-item {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-radius: 20px;
        padding: 1rem 1.25rem;
    }

    .feature-item span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.4rem;
        height: 2.4rem;
        border-radius: 14px;
        background: rgba(96, 165, 250, 0.18);
        color: #93c5fd;
        font-size: 1.1rem;
    }

    .feature-item p {
        margin: 0;
        color: #e2e8f0;
        font-size: 0.98rem;
        line-height: 1.6;
    }

    .auth-panel {
        width: 100%;
        max-width: 560px;
        position: relative;
        background: #f8fafc;
        border-radius: 32px;
        overflow: hidden;
        box-shadow: 0 30px 90px rgba(15, 23, 42, 0.16);
    }

    .auth-panel-content {
        position: relative;
        z-index: 1;
        padding: 2.5rem;
    }

    .auth-panel-header {
        margin-bottom: 2rem;
    }

    .back-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        border-radius: 16px;
        background: #f8fafc;
        color: #475569;
        border: none;
        cursor: pointer;
        transition: all 0.25s ease;
        margin-bottom: 1.75rem;
        font-size: 1.1rem;
    }

    .back-btn:hover {
        background: #e2e8f0;
        color: #1f2937;
    }

    .auth-panel h2 {
        font-size: 2.75rem;
        font-weight: 800;
        margin-bottom: 0.75rem;
        color: #0f172a;
        line-height: 1.05;
    }

    .subtitle {
        color: #64748b;
        font-size: 1rem;
        margin-bottom: 2rem;
        font-weight: 500;
        max-width: 34rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
        position: relative;
    }

    .form-group label {
        display: block;
        font-weight: 700;
        margin-bottom: 0.7rem;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-size: 0.8rem;
    }

    .input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-wrapper i {
        position: absolute;
        left: 1.25rem;
        color: #94a3b8;
        font-size: 1.1rem;
        pointer-events: none;
    }

    .password-toggle {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        border: none;
        background: transparent;
        color: #64748b;
        cursor: pointer;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.15rem;
    }

    .password-toggle:hover {
        color: #1d4ed8;
    }

    .form-group input {
        width: 100%;
        padding: 1rem 1.25rem 1rem 3.5rem;
        border: 1.5px solid #e2e8f0;
        border-radius: 18px;
        font-size: 0.95rem;
        transition: all 0.25s ease;
        background: #ffffff;
        color: #0f172a;
        font-weight: 500;
    }

    .form-group input:focus {
        outline: none;
        border-color: #3b82f6;
        background: white;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.08);
    }

    .form-group input::placeholder {
        color: #cbd5e1;
    }

    .auth-links {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.75rem;
    }

    .form-check {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .form-check input[type="checkbox"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
        border-radius: 8px;
        border: 1.5px solid #d1d5db;
        background: white;
        accent-color: #3b82f6;
    }

    .form-check label {
        margin: 0;
        cursor: pointer;
        color: #475569;
        font-size: 0.95rem;
        font-weight: 500;
    }

    .auth-links a {
        color: #2563eb;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.95rem;
    }

    .auth-links a:hover {
        color: #1d4ed8;
    }

    .btn-auth {
        width: 100%;
        padding: 1.15rem 1.5rem;
        border: none;
        border-radius: 999px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        background: linear-gradient(135deg, #0f172a 0%, #111827 100%);
        color: white;
        letter-spacing: 0.25px;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .btn-auth:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 18px 36px rgba(17, 24, 39, 0.22);
    }

    .btn-auth:disabled {
        opacity: 0.75;
        cursor: not-allowed;
        transform: none;
    }

    .btn-auth .spinner {
        display: inline-block;
        width: 18px;
        height: 18px;
        border: 2px solid rgba(255, 255, 255, 0.32);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 1s ease-in-out infinite;
        margin-right: 0.65rem;
        vertical-align: middle;
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .btn-auth.loading .spinner {
        opacity: 1;
    }

    .divider {
        display: flex;
        align-items: center;
        margin: 1.5rem 0;
        color: #cbd5e1;
    }

    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e2e8f0;
    }

    .divider span {
        padding: 0 0.85rem;
        font-size: 0.85rem;
        font-weight: 600;
        color: #94a3b8;
    }

    .social-buttons {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1.75rem;
    }

    .btn-social {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        padding: 1rem 1rem;
        border-radius: 18px;
        border: 1.5px solid #e2e8f0;
        background: white;
        color: #1f2937;
        font-weight: 700;
        transition: all 0.25s ease;
    }

    .btn-social:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
        transform: translateY(-1px);
    }

    .alert {
        padding: 1rem 1.25rem;
        border-radius: 18px;
        margin-bottom: 1.5rem;
        font-size: 0.95rem;
        font-weight: 500;
    }

    .alert-danger {
        background: rgba(254, 226, 226, 0.9);
        border: 1px solid rgba(239, 68, 68, 0.25);
        color: #b91c1c;
    }

    .invalid-feedback {
        display: block;
        color: #dc2626;
        font-size: 0.85rem;
        margin-top: 0.5rem;
        padding-left: 2.5rem;
    }

    .form-control.is-invalid {
        border-color: #dc2626;
        background: rgba(254, 226, 226, 0.14);
    }

    .auth-footer {
        text-align: center;
        color: #64748b;
        font-size: 0.95rem;
        font-weight: 500;
    }

    .auth-footer a {
        color: #2563eb;
        font-weight: 700;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    @media (max-width: 991px) {
        .auth-screen {
            grid-template-columns: 1fr;
            padding: 1.5rem;
        }

        .auth-visual {
            min-height: 340px;
            border-radius: 28px;
        }

        .auth-panel {
            padding: 2.25rem;
        }
    }

    @media (max-width: 640px) {
        .auth-screen {
            padding: 1rem;
            gap: 1rem;
        }

        .auth-panel {
            border-radius: 24px;
        }

        .auth-panel h2 {
            font-size: 2.25rem;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const loginForm = document.querySelector('form');
        const loginBtn = document.getElementById('login-btn');
        const toggleButtons = document.querySelectorAll('.password-toggle');

        toggleButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const targetId = button.getAttribute('data-target');
                const targetInput = document.getElementById(targetId);
                if (!targetInput) return;

                const isPassword = targetInput.type === 'password';
                targetInput.type = isPassword ? 'text' : 'password';
                button.innerHTML = isPassword ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
                button.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
            });
        });

        if (loginForm && loginBtn) {
            loginForm.addEventListener('submit', function() {
                loginBtn.classList.add('loading');
                loginBtn.disabled = true;
                loginBtn.innerHTML = '<span class="spinner"></span>Logging in...';
            });
        }

        if (document.querySelector('.alert-danger') || document.querySelector('.invalid-feedback')) {
            loginBtn.classList.remove('loading');
            loginBtn.disabled = false;
            loginBtn.innerHTML = 'Log In';
        }
    });
</script>

<div class="auth-screen">
    <div class="auth-visual">
        <div class="visual-copy">
            <div class="brand">
                <div class="brand-mark"><i class="fas fa-car-side"></i></div>
                <span>SUCCEZZ Drivers Agency</span>
            </div>
            <h1>Drive Your Future with SUCCEZZ</h1>
            <p>Secure your transport operations with professional drivers, premium vehicles, and seamless booking control.</p>
        </div>
    </div>

    <div class="auth-panel">
        <div class="auth-panel-content">
            <div class="auth-panel-header">
            <a href="{{ route('home') }}" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h2>{{ $title ?? 'Welcome Back' }}</h2>
            <p class="subtitle">{{ $subtitle ?? 'Sign in to continue to your account and manage bookings, applications, and drivers in one place.' }}</p>
        </div>

        <form method="POST" action="{{ $actionRoute ?? route('login.post') }}">
            @csrf
            @if(($loginType ?? 'user') === 'service_customer' && in_array($requestType ?? null, ['driver', 'vehicle'], true))
                <input type="hidden" name="request_type" value="{{ $requestType }}">
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope"></i>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="Enter your email">
                </div>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="Enter your password">
                    <button type="button" class="password-toggle" data-target="password" aria-label="Show password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="auth-links">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <a href="{{ route('password.request') }}">Forgot Password?</a>
            </div>

            <button type="submit" class="btn-auth" id="login-btn">
                <span class="spinner"></span>
                Log In
            </button>
        </form>
        </div>

        @if(($loginType ?? 'user') !== 'service_customer')
            <div class="divider">
                <span>Or continue with</span>
            </div>

            <div class="social-buttons">
                <a href="{{ route('auth.google') }}" class="btn-social">
                    <i class="fab fa-google" style="color: #ea4335;"></i>
                    Continue with Google
                </a>
                <a href="{{ route('auth.apple') }}" class="btn-social">
                    <i class="fab fa-apple" style="color: #000;"></i>
                    Continue with Apple
                </a>
            </div>
        @endif

        <div class="auth-footer">
            @if(($loginType ?? 'user') === 'service_customer')
                New to service requests? <a href="{{ route('service.register') }}">Create an account</a>
            @else
                Don't have an account? <a href="{{ route('register') }}">Sign up</a>
            @endif
        </div>
        </div>
    </div>
</div>
@endsection
