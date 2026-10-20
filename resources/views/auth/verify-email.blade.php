@extends('layouts.app')

@section('title', 'Verify Your Email')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-4 p-md-5 text-center">
                    <i class="fas fa-envelope-circle-check text-primary mb-3" style="font-size: 2.5rem;"></i>
                    <h1 class="h3 mb-3">Verify your email address</h1>
                    <p class="text-muted">
                        We sent a verification link to <strong>{{ Auth::user()->email }}</strong>.
                        Open that email and follow the link to access your account.
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <div class="alert alert-success" role="status">
                            A new verification link has been sent to your email address.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">Resend verification email</button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}" class="mt-3">
                        @csrf
                        <button type="submit" class="btn btn-link">Sign out</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
