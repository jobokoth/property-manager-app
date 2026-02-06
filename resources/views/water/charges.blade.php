@extends('layouts.app')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('water.index') }}">Water</a></li>
                    <li class="breadcrumb-item active">Charges</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">Water Charges</h1>
        </div>
        <a href="{{ route('water.charges.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i> Add Charge
        </a>
    </div>
</div>

@if($charges->isEmpty())
    <div class="card app-card">
        <div class="card-body text-center py-5">
            <i class="fa-solid fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
            <h5>No water charges found</h5>
            <p class="text-muted">Water charges will appear here once added.</p>
            <a href="{{ route('water.charges.create') }}" class="btn btn-primary">Add First Charge</a>
        </div>
    </div>
@else
    <div class="card app-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>Tenant</th>
                            <th>Unit / Property</th>
                            <th>Amount</th>
                            <th>Source</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($charges as $charge)
                            <tr>
                                <td>
                                    <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $charge->period_month)->format('F Y') }}</strong>
                                </td>
                                <td>{{ $charge->tenancy->tenant->name }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $charge->tenancy->unit->label ?? $charge->tenancy->unit->number }}</span>
                                    <small class="text-muted d-block">{{ $charge->tenancy->unit->property->name }}</small>
                                </td>
                                <td><strong>KES {{ number_format($charge->amount, 2) }}</strong></td>
                                <td>
                                    <span class="badge bg-{{ $charge->source === 'auto' ? 'info' : 'secondary' }}">
                                        {{ ucfirst($charge->source) }}
                                    </span>
                                </td>
                                <td>{{ Str::limit($charge->notes, 30) ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $charges->links() }}
    </div>
@endif
@endsection
