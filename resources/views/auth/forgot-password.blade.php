@extends('layouts.app')

@section('title', 'Forgot Password')

@section('content')
<style>
    .auth-wrapper {
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    }

    .auth-panel {
        width: 100%;
        max-width: 460px;
        background: white;
        border-radius: 28px;
        box-shadow: 0 20px 60px rgba(15, 23, 42, 0.15);
        padding: 2.5rem 2.25rem;
        border: 1px solid rgba(15, 23, 42, 0.08);
    }

    .auth-panel-header {
        margin-bottom: 2rem;
    }

    .auth-panel-header .back-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border-radius: 14px;
        background: #f1f5f9;
        color: #64748b;
        border: none;
        cursor: pointer;
        transition: all 0.25s ease;
        margin-bottom: 1.75rem;
        font-size: 1.1rem;
    }

    .auth-panel-header .back-btn:hover {
        background: #e2e8f0;
        color: #475569;
    }

    .auth-panel h2 {
        font-size: 2rem;
        font-weight: 900;
        margin-bottom: 0.75rem;
        color: #0f172a;
        letter-spacing: -0.5px;
    }

    .auth-panel .subtitle {
        color: #64748b;
        font-size: 0.95rem;
        margin-bottom: 2.25rem;
        font-weight: 500;
    }

    .form-group {
        margin-bottom: 1.5rem;
        position: relative;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 0.65rem;
        color: #1e293b;
        font-size: 0.95rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        font-size: 0.85rem;
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

    .form-group input {
        width: 100%;
        padding: 1rem 1.2rem 1rem 3.5rem;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        font-size: 0.95rem;
        transition: all 0.25s ease;
        background: #f8fafc;
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

    .btn-auth {
        width: 100%;
        padding: 1.1rem 1.5rem;
        border: none;
        border-radius: 999px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
        color: white;
        letter-spacing: 0.3px;
        margin-bottom: 1.75rem;
    }

    .btn-auth:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 32px rgba(17, 24, 39, 0.25);
    }

    .btn-auth:active {
        transform: translateY(0);
    }

    .auth-footer {
        text-align: center;
        font-size: 0.9rem;
        color: #64748b;
        font-weight: 500;
    }

    .auth-footer a {
        color: #3b82f6;
        text-decoration: none;
        font-weight: 700;
        transition: color 0.2s ease;
    }

    .auth-footer a:hover {
        color: #2563eb;
    }
</style>

<div class="auth-wrapper">
    <div class="auth-panel">
        <div class="auth-panel-header">
            <a href="{{ route('login') }}" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h2>Forgot Password</h2>
            <p class="subtitle">Enter your email address and we'll send you a link to reset your password.</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif

        @if($errors->has('email'))
            <div class="alert alert-danger" role="alert">{{ $errors->first('email') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope"></i>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus placeholder="Enter your email">
                </div>
            </div>

            <button type="submit" class="btn-auth">Send Reset Link</button>
        </form>

        <div class="auth-footer">
            Remember your password? <a href="{{ route('login') }}">Log In</a>
        </div>
    </div>
</div>
@endsection