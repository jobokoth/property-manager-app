@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <div class="card app-card auth-card reveal">
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="brand-mark mx-auto mb-3">
                        <i class="fa-solid fa-building"></i>
                    </div>
                    <h1 class="h4 fw-semibold">Create your account</h1>
                    <p class="text-muted mb-0">Set up a workspace and start managing your portfolio.</p>
                </div>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">{{ __('Name') }}</label>
                        <div class="row">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="first_name" class="form-label">First Name</label>
                                <input id="first_name" type="text" class="form-control @error('first_name') is-invalid @enderror"
                                       name="first_name" value="{{ old('first_name') }}" required autocomplete="given-name" autofocus>
                                @error('first_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input id="last_name" type="text" class="form-control @error('last_name') is-invalid @enderror"
                                       name="last_name" value="{{ old('last_name') }}" required autocomplete="family-name">
                                @error('last_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">{{ __('Email Address') }}</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">{{ __('Password') }}</label>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password-confirm" class="form-label">{{ __('Confirm Password') }}</label>
                        <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            {{ __('Register') }}
                        </button>
                        @if (Route::has('login'))
                            <a class="btn btn-outline-secondary" href="{{ route('login') }}">Already have an account? Sign in</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
