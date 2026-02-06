@extends('layouts.app')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('water.index') }}">Water</a></li>
                    <li class="breadcrumb-item active">Meters</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">Water Meters</h1>
        </div>
        <a href="{{ route('water.meters.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i> Add Meter
        </a>
    </div>
</div>

@if($meters->isEmpty())
    <div class="card app-card">
        <div class="card-body text-center py-5">
            <i class="fa-solid fa-gauge-high fa-3x text-muted mb-3"></i>
            <h5>No water meters found</h5>
            <p class="text-muted">Add water meters to start tracking usage.</p>
            <a href="{{ route('water.meters.create') }}" class="btn btn-primary">Add First Meter</a>
        </div>
    </div>
@else
    <div class="card app-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Meter Serial</th>
                            <th>Unit</th>
                            <th>Property</th>
                            <th>Status</th>
                            <th>Last Reading</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($meters as $meter)
                            <tr>
                                <td><code>{{ $meter->meter_serial }}</code></td>
                                <td>{{ $meter->unit->label ?? $meter->unit->number }}</td>
                                <td>{{ $meter->unit->property->name }}</td>
                                <td>
                                    <span class="badge bg-{{ $meter->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($meter->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($meter->readings->isNotEmpty())
                                        {{ number_format($meter->readings->first()->reading_value, 2) }} units
                                        <small class="text-muted d-block">{{ $meter->readings->first()->reading_date->format('M d, Y') }}</small>
                                    @else
                                        <span class="text-muted">No readings</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('water.readings.create', ['meter' => $meter->id]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-solid fa-plus"></i> Reading
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $meters->links() }}
    </div>
@endif
@endsection
