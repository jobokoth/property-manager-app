@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Tenancy Details</h1>
                <div>
                    @can('properties.manage_tenants')
                        <a href="{{ route('tenancies.edit', $tenancy) }}" class="btn btn-warning">Edit</a>
                    @endcan
                    <a href="{{ route('tenancies.index') }}" class="btn btn-secondary">Back to Tenancies</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Tenancy Information</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Tenant:</strong> {{ $tenancy->tenant->name }}</p>
                            <p><strong>Email:</strong> {{ $tenancy->tenant->email }}</p>
                            <p><strong>Phone:</strong> {{ $tenancy->tenant->phone ?? 'N/A' }}</p>
                            <p><strong>Unit:</strong> {{ $tenancy->unit->label }}</p>
                            <p><strong>Property:</strong> {{ $tenancy->unit->property->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Start Date:</strong> {{ $tenancy->start_date->format('M d, Y') }}</p>
                            <p><strong>End Date:</strong> {{ $tenancy->end_date ? $tenancy->end_date->format('M d, Y') : 'Ongoing' }}</p>
                            <p><strong>Rent Amount:</strong> KES {{ number_format($tenancy->rent_amount, 2) }}</p>
                            <p><strong>Deposit Amount:</strong> KES {{ number_format($tenancy->deposit_amount, 2) }}</p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-{{ $tenancy->status == 'active' ? 'success' : ($tenancy->status == 'terminated' ? 'danger' : 'info') }}">
                                    {{ ucfirst($tenancy->status) }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Tenancy Status</h5>
                </div>
                <div class="card-body text-center">
                    <div class="display-4 mb-3">
                        @if($tenancy->status == 'active')
                            <i class="fas fa-user-check text-success"></i>
                        @elseif($tenancy->status == 'terminated')
                            <i class="fas fa-user-times text-danger"></i>
                        @else
                            <i class="fas fa-user-clock text-info"></i>
                        @endif
                    </div>
                    <h5 class="text-capitalize">{{ $tenancy->status }}</h5>
                    <p class="text-muted">
                        @if($tenancy->status == 'active')
                            This tenancy is currently active
                        @elseif($tenancy->status == 'terminated')
                            This tenancy has been terminated
                        @else
                            This tenancy has expired
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Information -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Unit Details</h5>
                </div>
                <div class="card-body">
                    <p><strong>Label:</strong> {{ $tenancy->unit->label }}</p>
                    <p><strong>Floor:</strong> {{ $tenancy->unit->floor ?? 'N/A' }}</p>
                    <p><strong>Bedrooms:</strong> {{ $tenancy->unit->bedrooms }}</p>
                    <p><strong>Status:</strong> 
                        <span class="badge bg-{{ $tenancy->unit->status == 'available' ? 'success' : ($tenancy->unit->status == 'occupied' ? 'primary' : ($tenancy->unit->status == 'maintenance' ? 'warning' : 'info')) }}">
                            {{ ucfirst($tenancy->unit->status) }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Property Details</h5>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> {{ $tenancy->unit->property->name }}</p>
                    <p><strong>Location:</strong> {{ $tenancy->unit->property->location }}</p>
                    <p><strong>Status:</strong> 
                        <span class="badge bg-{{ $tenancy->unit->property->status == 'active' ? 'success' : ($tenancy->unit->property->status == 'inactive' ? 'secondary' : 'warning') }}">
                            {{ ucfirst($tenancy->unit->property->status) }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Financial Summary</h5>
                </div>
                <div class="card-body">
                    <p><strong>Monthly Rent:</strong> KES {{ number_format($tenancy->rent_amount, 2) }}</p>
                    <p><strong>Deposit:</strong> KES {{ number_format($tenancy->deposit_amount, 2) }}</p>
                    <p><strong>Total Expected:</strong> KES {{ number_format($tenancy->rent_amount + $tenancy->deposit_amount, 2) }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection