@extends('layouts.app')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-1">Water Management</h1>
            <p class="text-muted mb-0">Track water meters, readings, and charges</p>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-md-3">
        <a href="{{ route('water.meters') }}" class="card app-card text-decoration-none">
            <div class="card-body text-center py-4">
                <i class="fa-solid fa-gauge-high fa-2x text-primary mb-2"></i>
                <h5 class="mb-1">Water Meters</h5>
                <p class="text-muted mb-0">Manage meters</p>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('water.readings') }}" class="card app-card text-decoration-none">
            <div class="card-body text-center py-4">
                <i class="fa-solid fa-tachometer-alt fa-2x text-info mb-2"></i>
                <h5 class="mb-1">Readings</h5>
                <p class="text-muted mb-0">Record readings</p>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('water.charges') }}" class="card app-card text-decoration-none">
            <div class="card-body text-center py-4">
                <i class="fa-solid fa-file-invoice-dollar fa-2x text-success mb-2"></i>
                <h5 class="mb-1">Charges</h5>
                <p class="text-muted mb-0">View charges</p>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('water.readings.create') }}" class="card app-card text-decoration-none bg-primary text-white">
            <div class="card-body text-center py-4">
                <i class="fa-solid fa-plus-circle fa-2x mb-2"></i>
                <h5 class="mb-1">New Reading</h5>
                <p class="mb-0 opacity-75">Record now</p>
            </div>
        </a>
    </div>
</div>

<!-- Recent Readings -->
<div class="card app-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa-solid fa-clock-rotate-left me-2"></i>Recent Readings</h5>
        <a href="{{ route('water.readings') }}" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    @if($recentReadings->isEmpty())
        <div class="card-body text-center py-5">
            <i class="fa-solid fa-droplet fa-3x text-muted mb-3"></i>
            <h5>No readings recorded yet</h5>
            <p class="text-muted">Start by adding water meters and recording readings.</p>
            <a href="{{ route('water.readings.create') }}" class="btn btn-primary">Record First Reading</a>
        </div>
    @else
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Unit</th>
                            <th>Property</th>
                            <th>Meter Serial</th>
                            <th>Reading</th>
                            <th>Recorded By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentReadings as $reading)
                            <tr>
                                <td>{{ $reading->reading_date->format('M d, Y') }}</td>
                                <td>{{ $reading->waterMeter->unit->label ?? $reading->waterMeter->unit->number }}</td>
                                <td>{{ $reading->waterMeter->unit->property->name }}</td>
                                <td><code>{{ $reading->waterMeter->meter_serial }}</code></td>
                                <td><strong>{{ number_format($reading->reading_value, 2) }}</strong> units</td>
                                <td>{{ $reading->capturedBy->name ?? 'System' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
