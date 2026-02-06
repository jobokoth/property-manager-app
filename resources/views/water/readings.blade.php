@extends('layouts.app')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('water.index') }}">Water</a></li>
                    <li class="breadcrumb-item active">Readings</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">Water Readings</h1>
        </div>
        <a href="{{ route('water.readings.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i> Record Reading
        </a>
    </div>
</div>

@if($readings->isEmpty())
    <div class="card app-card">
        <div class="card-body text-center py-5">
            <i class="fa-solid fa-tachometer-alt fa-3x text-muted mb-3"></i>
            <h5>No readings recorded yet</h5>
            <p class="text-muted">Start recording water meter readings.</p>
            <a href="{{ route('water.readings.create') }}" class="btn btn-primary">Record First Reading</a>
        </div>
    </div>
@else
    <div class="card app-card">
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
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($readings as $reading)
                            <tr>
                                <td>{{ $reading->reading_date->format('M d, Y') }}</td>
                                <td>{{ $reading->waterMeter->unit->label ?? $reading->waterMeter->unit->number }}</td>
                                <td>{{ $reading->waterMeter->unit->property->name }}</td>
                                <td><code>{{ $reading->waterMeter->meter_serial }}</code></td>
                                <td><strong>{{ number_format($reading->reading_value, 2) }}</strong> units</td>
                                <td>{{ $reading->capturedBy->name ?? 'System' }}</td>
                                <td>{{ Str::limit($reading->notes, 30) ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $readings->links() }}
    </div>
@endif
@endsection
