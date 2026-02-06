@extends('layouts.app')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-1">Statements</h1>
            <p class="text-muted mb-0">View monthly account statements</p>
        </div>
        @can('statements.generate')
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateStatementModal">
                <i class="fa-solid fa-file-circle-plus me-1"></i> Generate Statement
            </button>
        @endcan
    </div>
</div>

<!-- Date Range Filter -->
<div class="card app-card mb-4">
    <div class="card-body">
        <form action="{{ route('statements.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">From</label>
                <input type="month" name="from" class="form-control" value="{{ request('from', now()->subMonths(11)->format('Y-m')) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">To</label>
                <input type="month" name="to" class="form-control" value="{{ request('to', now()->format('Y-m')) }}">
            </div>
            <div class="col-md-6">
                <div class="btn-group me-2" role="group">
                    <a href="{{ route('statements.index', ['from' => now()->format('Y-m'), 'to' => now()->format('Y-m')]) }}" class="btn btn-outline-secondary btn-sm">This Month</a>
                    <a href="{{ route('statements.index', ['from' => now()->subMonths(2)->format('Y-m'), 'to' => now()->format('Y-m')]) }}" class="btn btn-outline-secondary btn-sm">Last 3 Months</a>
                    <a href="{{ route('statements.index', ['from' => now()->subMonths(5)->format('Y-m'), 'to' => now()->format('Y-m')]) }}" class="btn btn-outline-secondary btn-sm">Last 6 Months</a>
                    <a href="{{ route('statements.index', ['from' => now()->startOfYear()->format('Y-m'), 'to' => now()->format('Y-m')]) }}" class="btn btn-outline-secondary btn-sm">This Year</a>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-filter me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

@if($statements->isEmpty())
    <div class="card app-card">
        <div class="card-body text-center py-5">
            <i class="fa-solid fa-file-invoice fa-3x text-muted mb-3"></i>
            <h5>No statements found</h5>
            <p class="text-muted">Statements will appear here once generated.</p>
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
                            @if(!auth()->user()->hasRole('tenant'))
                                <th>Tenant</th>
                                <th>Unit / Property</th>
                            @endif
                            <th>Opening Balance</th>
                            <th>Charges</th>
                            <th>Payments</th>
                            <th>Closing Balance</th>
                            <th>Generated</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($statements as $statement)
                            @php
                                $totals = $statement->totals_json ?? [];
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $statement->period_month)->format('F Y') }}</strong>
                                </td>
                                @if(!auth()->user()->hasRole('tenant'))
                                    <td>{{ $statement->tenancy->tenant->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $statement->tenancy->unit->label ?? $statement->tenancy->unit->number ?? 'N/A' }}</span>
                                        <small class="text-muted d-block">{{ $statement->tenancy->unit->property->name ?? 'N/A' }}</small>
                                    </td>
                                @endif
                                <td>KES {{ number_format($totals['opening_balance'] ?? 0, 2) }}</td>
                                <td>KES {{ number_format(($totals['rent_due'] ?? 0) + ($totals['water_due'] ?? 0), 2) }}</td>
                                <td class="text-success">KES {{ number_format($totals['total_paid'] ?? 0, 2) }}</td>
                                <td>
                                    @php
                                        $closingBalance = $totals['closing_balance'] ?? 0;
                                    @endphp
                                    <span class="{{ $closingBalance > 0 ? 'text-danger' : 'text-success' }}">
                                        KES {{ number_format($closingBalance, 2) }}
                                    </span>
                                </td>
                                <td>{{ $statement->generated_at?->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('statements.show', $statement) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-solid fa-eye"></i> View
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
        {{ $statements->links() }}
    </div>
@endif

@can('statements.generate')
<!-- Generate Statement Modal -->
<div class="modal fade" id="generateStatementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('statements.generate') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Generate Statement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tenancy</label>
                        <select name="tenancy_id" class="form-select" required>
                            <option value="">Select a tenancy...</option>
                            @php
                                $tenancies = \App\Models\Tenancy::with('tenant', 'unit.property')
                                    ->where('status', 'active')
                                    ->get();
                            @endphp
                            @foreach($tenancies as $tenancy)
                                <option value="{{ $tenancy->id }}">
                                    {{ $tenancy->tenant->name }} - {{ $tenancy->unit->label ?? $tenancy->unit->number }} ({{ $tenancy->unit->property->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Period</label>
                        <input type="month" name="period_month" class="form-control" value="{{ date('Y-m') }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Statement</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection
