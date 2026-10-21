@extends('layouts.app')

@section('title', 'Create a Service Account')

@section('content')
<div class="container py-5" style="max-width: 620px;">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4 p-md-5">
            <a href="{{ route('home') }}" class="text-decoration-none"><i class="fas fa-arrow-left me-2"></i>Back to website</a>
            <h1 class="h3 mt-4">Create your service account</h1>
            <p class="text-muted">You are starting a {{ $requestType }} request. After verifying your email, you can submit requests for drivers and vehicles from your dashboard.</p>
            <form method="POST" action="{{ route('service.register.post') }}">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Full name</label>
                    <input id="name" name="name" class="form-control" value="{{ old('name') }}" required autocomplete="name">
                    @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input id="email" type="email" name="email" class="form-control" value="{{ old('email') }}" required autocomplete="email">
                    @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" type="password" name="password" class="form-control" required autocomplete="new-password">
                    @error('password')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                </div>
                <button class="btn btn-primary w-100" type="submit">Create account and verify email</button>
            </form>
            <p class="text-center mt-4 mb-0">Already registered? <a href="{{ route('service.login') }}">Sign in</a></p>
        </div>
    </div>
</div>
@endsection
