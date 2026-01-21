@extends('layouts.app')

@section('content')
@guest
<div class="row align-items-center g-4">
    <div class="col-lg-6">
        <div class="welcome-panel p-4 p-lg-5 reveal">
            <div class="welcome-logo">
                <span class="brand-mark"><i class="fa-solid fa-building"></i></span>
                <div>
                    <h2 class="h4 fw-semibold mb-1">Welcome to Property Manager</h2>
                    <p class="text-muted mb-0">A friendly home for your rentals.</p>
                </div>
            </div>
            <p class="lead text-muted">
                Keep your properties, tenants, and payments in sync. Track maintenance requests, automate reminders, and
                stay in control with a calm, organized workspace.
            </p>
            <div class="welcome-illustration mt-4">
                <svg viewBox="0 0 520 320" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Welcome illustration">
                    <defs>
                        <linearGradient id="pm-grad" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0%" stop-color="#0ea5e9"/>
                            <stop offset="100%" stop-color="#22c55e"/>
                        </linearGradient>
                    </defs>
                    <rect width="520" height="320" rx="24" fill="#ffffff" opacity="0.85"/>
                    <rect x="40" y="60" width="200" height="200" rx="18" fill="url(#pm-grad)" opacity="0.18"/>
                    <rect x="270" y="80" width="200" height="40" rx="12" fill="#e2e8f0"/>
                    <rect x="270" y="140" width="150" height="18" rx="9" fill="#e2e8f0"/>
                    <rect x="270" y="176" width="180" height="18" rx="9" fill="#e2e8f0"/>
                    <rect x="270" y="212" width="120" height="18" rx="9" fill="#e2e8f0"/>
                    <circle cx="140" cy="160" r="52" fill="url(#pm-grad)" opacity="0.7"/>
                    <rect x="102" y="120" width="76" height="80" rx="14" fill="#ffffff"/>
                    <rect x="118" y="138" width="44" height="44" rx="8" fill="#0f172a" opacity="0.12"/>
                </svg>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card app-card auth-card reveal delay-1">
            <div class="card-body">
                <h3 class="fw-semibold mb-2">Sign in to your workspace</h3>
                <p class="text-muted mb-4">Manage rentals, payments, and service requests in one place.</p>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">{{ __('Email Address') }}</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">{{ __('Password') }}</label>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                {{ __('Remember Me') }}
                            </label>
                        </div>
                        @if (Route::has('password.request'))
                            <a class="small text-decoration-none" href="{{ route('password.request') }}">
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            {{ __('Login') }}
                        </button>
                        @if (Route::has('register'))
                            <a class="btn btn-outline-secondary" href="{{ route('register') }}">Create an account</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@else
<div class="page-header">
    <div>
        <h1 class="page-title">Welcome back</h1>
        <p class="page-subtitle">Jump back into your workspace and keep your portfolio on track.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-primary" href="{{ route('dashboard') }}">Go to dashboard</a>
        <a class="btn btn-outline-secondary" href="{{ route('properties.index') }}">View properties</a>
    </div>
</div>
@endguest
@endsection
