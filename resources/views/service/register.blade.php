@extends('layouts.app')

@section('title', 'Create a Service Account')

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
        background: #e2e8f0;
        color: #475569;
        margin-bottom: 1.75rem;
        font-size: 1.1rem;
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
    }

    .form-group {
        margin-bottom: 1.25rem;
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

    .input-wrapper > i {
        position: absolute;
        left: 1.25rem;
        color: #94a3b8;
        font-size: 1.1rem;
        pointer-events: none;
        z-index: 1;
    }

    .form-group input {
        width: 100%;
        padding: 1rem 3.5rem 1rem 3.5rem;
        border: 1.5px solid #e2e8f0;
        border-radius: 18px;
        font-size: 0.95rem;
        transition: all 0.25s ease;
        background: #fff;
        color: #0f172a;
        font-weight: 500;
    }

    .form-group input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.08);
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
        padding: 0.5rem;
        z-index: 2;
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
        color: #fff;
        letter-spacing: 0.25px;
        margin-top: 0.5rem;
    }

    .btn-auth:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 36px rgba(17, 24, 39, 0.22);
    }

    .auth-footer {
        text-align: center;
        color: #64748b;
        font-size: 0.95rem;
        font-weight: 500;
        padding: 0 2.5rem 2.5rem;
    }

    .auth-footer a {
        color: #2563eb;
        font-weight: 700;
    }

    .auth-errors {
        padding: 1rem 1.25rem;
        border-radius: 18px;
        margin-bottom: 1.5rem;
        background: rgba(254, 226, 226, 0.9);
        border: 1px solid rgba(239, 68, 68, 0.25);
        color: #b91c1c;
    }

    .field-error {
        color: #dc2626;
        font-size: 0.85rem;
        margin-top: 0.5rem;
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
    }

    @media (max-width: 640px) {
        .auth-screen {
            padding: 1rem;
            gap: 1rem;
        }

        .auth-panel {
            border-radius: 24px;
        }

        .auth-panel-content {
            padding: 1.5rem;
        }

        .auth-panel h2 {
            font-size: 2.25rem;
        }

        .auth-footer {
            padding: 0 1.5rem 1.5rem;
        }
    }
</style>

<script>
    document.addEventListener('click', function (event) {
        const button = event.target.closest('.password-toggle');
        if (!button) return;

        const input = document.getElementById(button.dataset.target);
        if (!input) return;

        const showPassword = input.type === 'password';
        input.type = showPassword ? 'text' : 'password';
        button.innerHTML = showPassword ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
        button.setAttribute('aria-label', showPassword ? 'Hide password' : 'Show password');
        button.setAttribute('aria-pressed', showPassword ? 'true' : 'false');
    });
</script>

<div class="auth-screen">
    <div class="auth-visual">
        <div class="visual-copy">
            <div class="brand">
                <div class="brand-mark"><i class="fas fa-car-side"></i></div>
                <span>SUCCEZZ Drivers Agency</span>
            </div>
            <h1>Request the right service for you</h1>
            <p>Create an account to request a {{ $requestType }}, get updates from our team, and manage your service request in one place.</p>
        </div>
    </div>

    <div class="auth-panel">
        <div class="auth-panel-content">
            <div class="auth-panel-header">
                <a href="{{ route('home') }}" class="back-btn" aria-label="Back to website">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h2>Create account</h2>
                <p class="subtitle">You are starting a {{ $requestType }} request. Verify your email to continue to the service dashboard.</p>
            </div>

            <form method="POST" action="{{ route('service.register.post') }}">
                @csrf
                <input type="hidden" name="request_type" value="{{ $requestType }}">

                @if($errors->any())
                    <div class="auth-errors" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="form-group">
                    <label for="name">Full name</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input id="name" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="Enter your full name">
                    </div>
                    @error('name')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="email">Email address</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="Enter your email">
                    </div>
                    @error('email')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Create a password">
                        <button type="button" class="password-toggle" data-target="password" aria-label="Show password" aria-pressed="false">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Confirm your password">
                        <button type="button" class="password-toggle" data-target="password_confirmation" aria-label="Show password" aria-pressed="false">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button class="btn-auth" type="submit">Create account and verify email</button>
            </form>
        </div>

        <div class="auth-footer">
            Already registered? <a href="{{ route('service.login', ['requestType' => $requestType]) }}">Sign in</a>
        </div>
    </div>
</div>
@endsection
