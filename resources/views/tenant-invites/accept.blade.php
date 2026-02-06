<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Accept Invitation - {{ config('app.name', 'Property Manager') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/flatly/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="text-center mb-4">
                    <h1 class="h3">
                        <i class="fa-solid fa-building me-2"></i>
                        {{ config('app.name', 'Property Manager') }}
                    </h1>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fa-solid fa-envelope-open me-2"></i>
                            Accept Tenancy Invitation
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h5>Hello {{ $invite->name }}!</h5>
                            <p class="mb-0">You've been invited to become a tenant at <strong>{{ $invite->property->name }}</strong>.</p>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6><i class="fa-solid fa-building me-2"></i>Property Details</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Property:</strong> {{ $invite->property->name }}</li>
                                    <li><strong>Unit:</strong> {{ $invite->unit->label }}</li>
                                    <li><strong>Location:</strong> {{ $invite->property->location }}</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fa-solid fa-file-contract me-2"></i>Tenancy Details</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Monthly Rent:</strong> KES {{ number_format($invite->rent_amount, 2) }}</li>
                                    @if($invite->deposit_amount)
                                        <li><strong>Deposit:</strong> KES {{ number_format($invite->deposit_amount, 2) }}</li>
                                    @endif
                                    <li><strong>Start Date:</strong> {{ $invite->start_date->format('F j, Y') }}</li>
                                </ul>
                            </div>
                        </div>

                        <hr>

                        <h5>Create Your Account</h5>
                        <p class="text-muted">Set a password to complete your registration and accept the tenancy.</p>

                        <form action="{{ route('tenant-invites.accept.process', $invite) }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" value="{{ $invite->email }}" readonly>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                           id="password" name="password" required minlength="8">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Minimum 8 characters</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control"
                                           id="password_confirmation" name="password_confirmation" required>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fa-solid fa-check me-2"></i>
                                    Accept Invitation & Create Account
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center text-muted">
                        <small>This invitation expires on {{ $invite->expires_at->format('F j, Y') }}</small>
                    </div>
                </div>

                <div class="text-center mt-3 text-muted">
                    <small>Already have an account? <a href="{{ route('login') }}">Login here</a></small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
