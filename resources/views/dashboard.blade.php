@extends('layouts.app')

@section('content')
@if(Auth::user()->hasRole('tenant'))
<div class="page-header">
    <div>
        <h1 class="page-title">Tenant Dashboard</h1>
        <p class="page-subtitle">Welcome back, {{ Auth::user()->name }}. Here is your account overview.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @can('requests.create')
            <a class="btn btn-outline-secondary" href="{{ route('service-requests.create') }}"><i class="fa-solid fa-screwdriver-wrench me-1"></i> New request</a>
        @endcan
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-3 col-md-6">
        <div class="card app-card stat-card p-4 reveal">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <p class="text-uppercase text-muted small fw-semibold mb-2">Active Tenancy</p>
                    <h6 class="fw-semibold mb-1">
                        {{ $activeTenancy?->unit?->property?->name ?? 'Not assigned' }}
                    </h6>
                    <p class="text-muted small mb-0">Unit {{ $activeTenancy?->unit?->label ?? 'N/A' }}</p>
                </div>
                <span class="stat-icon"><i class="fa-solid fa-home"></i></span>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card app-card stat-card p-4 reveal delay-1">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <p class="text-uppercase text-muted small fw-semibold mb-2">Service Requests</p>
                    <h2 class="fw-semibold mb-1">{{ $serviceRequestsCount ?? 0 }}</h2>
                    <p class="text-muted small mb-0">Submitted by you</p>
                </div>
                <span class="stat-icon"><i class="fa-solid fa-screwdriver-wrench"></i></span>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card app-card stat-card p-4 reveal delay-2">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <p class="text-uppercase text-muted small fw-semibold mb-2">Mpesa Messages</p>
                    <h2 class="fw-semibold mb-1">{{ $mpesaMessagesCount ?? 0 }}</h2>
                    <p class="text-muted small mb-0">Uploads on file</p>
                </div>
                <span class="stat-icon"><i class="fa-solid fa-receipt"></i></span>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card app-card stat-card p-4 reveal delay-3">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <p class="text-uppercase text-muted small fw-semibold mb-2">Statements</p>
                    <h2 class="fw-semibold mb-1">{{ $statementsCount ?? 0 }}</h2>
                    <p class="text-muted small mb-0">Available periods</p>
                </div>
                <span class="stat-icon"><i class="fa-solid fa-file-lines"></i></span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-12">
        <div class="card app-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0">Recent Service Requests</h6>
                @can('requests.view')
                    <a class="btn btn-outline-primary btn-sm" href="{{ route('service-requests.index') }}">View all</a>
                @endcan
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle app-table mb-0">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentServiceRequests ?? [] as $request)
                                <tr>
                                    <td class="fw-semibold">{{ $request->title }}</td>
                                    <td class="text-capitalize">{{ $request->priority }}</td>
                                    <td class="text-capitalize">{{ str_replace('_', ' ', $request->status) }}</td>
                                    <td>{{ $request->created_at?->format('M d, Y') ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No service requests found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@else
<div class="page-header">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Welcome back, {{ Auth::user()->name }}. Here is a quick pulse on the portfolio.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @can('properties.create')
            <a class="btn btn-primary" href="{{ route('properties.create') }}"><i class="fa-solid fa-plus me-1"></i> Add property</a>
        @endcan
        @can('requests.create')
            <a class="btn btn-outline-secondary" href="{{ route('service-requests.create') }}"><i class="fa-solid fa-screwdriver-wrench me-1"></i> New request</a>
        @endcan
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-3 col-md-6">
        <div class="card app-card stat-card p-4 reveal">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <p class="text-uppercase text-muted small fw-semibold mb-2">Properties</p>
                    <h2 class="fw-semibold mb-1">{{ $propertiesCount ?? \App\Models\Property::count() }}</h2>
                    <p class="text-muted small mb-0">Active portfolios</p>
                </div>
                <span class="stat-icon"><i class="fa-solid fa-building"></i></span>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card app-card stat-card p-4 reveal delay-1">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <p class="text-uppercase text-muted small fw-semibold mb-2">Units</p>
                    <h2 class="fw-semibold mb-1">{{ $unitsCount ?? \App\Models\Unit::count() }}</h2>
                    <p class="text-muted small mb-0">Across all buildings</p>
                </div>
                <span class="stat-icon"><i class="fa-solid fa-door-open"></i></span>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card app-card stat-card p-4 reveal delay-2">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <p class="text-uppercase text-muted small fw-semibold mb-2">Active Tenancies</p>
                    <h2 class="fw-semibold mb-1">{{ $tenanciesCount ?? \App\Models\Tenancy::where('status', 'active')->count() }}</h2>
                    <p class="text-muted small mb-0">Current leaseholders</p>
                </div>
                <span class="stat-icon"><i class="fa-solid fa-users"></i></span>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card app-card stat-card p-4 reveal delay-3">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <p class="text-uppercase text-muted small fw-semibold mb-2">Recent Payments</p>
                    <h2 class="fw-semibold mb-1">{{ $recentPaymentsCount ?? \App\Models\Payment::where('status', 'confirmed')->count() }}</h2>
                    <p class="text-muted small mb-0">Confirmed this month</p>
                </div>
                <span class="stat-icon"><i class="fa-solid fa-money-bill-wave"></i></span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-xl-6">
        <div class="card app-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0">Recent Properties</h6>
                <a class="btn btn-outline-primary btn-sm" href="{{ route('properties.index') }}">View all</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle app-table mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Location</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentProperties ?? \App\Models\Property::latest()->take(5)->get() as $property)
                                <tr>
                                    <td class="fw-semibold">{{ $property->name }}</td>
                                    <td>{{ Str::limit($property->location, 30) }}</td>
                                    <td>
                                        <span class="badge rounded-pill {{ $property->status == 'active' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                            {{ ucfirst($property->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No properties found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card app-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0">Recent Payments</h6>
                <a class="btn btn-outline-primary btn-sm" href="{{ route('payments.index') }}">View all</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle app-table mb-0">
                        <thead>
                            <tr>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Tenant</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPayments ?? \App\Models\Payment::with('tenancy.tenant')->where('status', 'confirmed')->latest()->take(5)->get() as $payment)
                                <tr>
                                    <td class="fw-semibold">KES {{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ $payment->paid_at->format('M d, Y') }}</td>
                                    <td>{{ $payment->tenancy->tenant->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge rounded-pill {{ $payment->status == 'confirmed' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No payments found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
