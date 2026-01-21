@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Unit Details</h1>
                <div>
                    @can('properties.manage_units')
                        <a href="{{ route('units.edit', $unit) }}" class="btn btn-warning">Edit</a>
                    @endcan
                    <a href="{{ route('units.index') }}" class="btn btn-secondary">Back to Units</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>{{ $unit->label }} - {{ $unit->property->name }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Label:</strong> {{ $unit->label }}</p>
                            <p><strong>Property:</strong> {{ $unit->property->name }}</p>
                            <p><strong>Floor:</strong> {{ $unit->floor ?? 'N/A' }}</p>
                            <p><strong>Bedrooms:</strong> {{ $unit->bedrooms }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Rent Amount:</strong> KES {{ number_format($unit->rent_amount, 2) }}</p>
                            <p><strong>Water Rate Mode:</strong> {{ $unit->water_rate_mode == 'per_unit' ? 'Per Unit' : 'Per Meter' }}</p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-{{ $unit->status == 'available' ? 'success' : ($unit->status == 'occupied' ? 'primary' : ($unit->status == 'maintenance' ? 'warning' : 'info')) }}">
                                    {{ ucfirst($unit->status) }}
                                </span>
                            </p>
                            <p><strong>Created At:</strong> {{ $unit->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Unit Status</h5>
                </div>
                <div class="card-body text-center">
                    <div class="display-4 mb-3">
                        @if($unit->status == 'available')
                            <i class="fas fa-check-circle text-success"></i>
                        @elseif($unit->status == 'occupied')
                            <i class="fas fa-user text-primary"></i>
                        @elseif($unit->status == 'maintenance')
                            <i class="fas fa-tools text-warning"></i>
                        @else
                            <i class="fas fa-lock text-info"></i>
                        @endif
                    </div>
                    <h5 class="text-capitalize">{{ $unit->status }}</h5>
                    <p class="text-muted">
                        @if($unit->status == 'available')
                            This unit is available for occupancy
                        @elseif($unit->status == 'occupied')
                            This unit is currently occupied
                        @elseif($unit->status == 'maintenance')
                            This unit is under maintenance
                        @else
                            This unit is reserved
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tenancies Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Tenancies for this Unit</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Tenant</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Rent Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($unit->tenancies as $tenancy)
                                    <tr>
                                        <td>{{ $tenancy->tenant->name }}</td>
                                        <td>{{ $tenancy->start_date->format('M d, Y') }}</td>
                                        <td>{{ $tenancy->end_date ? $tenancy->end_date->format('M d, Y') : 'Ongoing' }}</td>
                                        <td>KES {{ number_format($tenancy->rent_amount, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $tenancy->status == 'active' ? 'success' : ($tenancy->status == 'terminated' ? 'danger' : 'info') }}">
                                                {{ ucfirst($tenancy->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('tenancies.show', $tenancy) }}" class="btn btn-sm btn-info">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No tenancies found for this unit</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection