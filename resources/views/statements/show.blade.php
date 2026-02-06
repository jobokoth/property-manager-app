@extends('layouts.app')

@section('content')
@php
    $totals = $statement->totals_json ?? [];
    $period = \Carbon\Carbon::createFromFormat('Y-m', $statement->period_month);
@endphp

<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('statements.index') }}">Statements</a></li>
                    <li class="breadcrumb-item active">{{ $period->format('F Y') }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">Account Statement</h1>
        </div>
        <button class="btn btn-outline-primary" onclick="window.print()">
            <i class="fa-solid fa-print me-1"></i> Print
        </button>
    </div>
</div>

<div class="card app-card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h5 class="text-muted mb-3">Statement Details</h5>
                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted" style="width: 40%;">Statement Period:</td>
                        <td><strong>{{ $period->format('F Y') }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Generated:</td>
                        <td>{{ $statement->generated_at?->format('M d, Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status:</td>
                        <td><span class="badge bg-success">{{ ucfirst($statement->status) }}</span></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h5 class="text-muted mb-3">Tenant Information</h5>
                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted" style="width: 40%;">Tenant:</td>
                        <td><strong>{{ $totals['tenant_name'] ?? $statement->tenancy->tenant->name }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Unit:</td>
                        <td>{{ $totals['unit'] ?? ($statement->tenancy->unit->label ?? $statement->tenancy->unit->number) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Property:</td>
                        <td>{{ $totals['property'] ?? $statement->tenancy->unit->property->name }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card app-card bg-light">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Opening Balance</h6>
                <h4 class="mb-0">KES {{ number_format($totals['opening_balance'] ?? 0, 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card app-card bg-light">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Total Charges</h6>
                <h4 class="mb-0">KES {{ number_format(($totals['rent_due'] ?? 0) + ($totals['water_due'] ?? 0), 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card app-card bg-light">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Total Paid</h6>
                <h4 class="mb-0 text-success">KES {{ number_format($totals['total_paid'] ?? 0, 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card app-card {{ ($totals['closing_balance'] ?? 0) > 0 ? 'bg-danger text-white' : 'bg-success text-white' }}">
            <div class="card-body text-center">
                <h6 class="mb-2">Closing Balance</h6>
                <h4 class="mb-0">KES {{ number_format($totals['closing_balance'] ?? 0, 2) }}</h4>
            </div>
        </div>
    </div>
</div>

<!-- Account Transactions (Ledger Format) -->
<div class="card app-card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fa-solid fa-list-check me-2"></i>Account Transactions</h5>
    </div>
    <div class="card-body p-0">
        @php
            $runningBalance = $totals['opening_balance'] ?? 0;
            $totalDebits = 0;
            $totalCredits = 0;
        @endphp
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th class="text-end">Debit</th>
                    <th class="text-end">Credit</th>
                    <th class="text-end">Balance</th>
                </tr>
            </thead>
            <tbody>
                <!-- Opening Balance -->
                <tr class="table-light">
                    <td>{{ $period->startOfMonth()->format('M d, Y') }}</td>
                    <td><strong>Opening Balance (Brought Forward)</strong></td>
                    <td class="text-end">-</td>
                    <td class="text-end">-</td>
                    <td class="text-end {{ $runningBalance > 0 ? 'text-danger' : 'text-success' }}">
                        <strong>KES {{ number_format($runningBalance, 2) }}</strong>
                    </td>
                </tr>

                <!-- Rent Invoice -->
                @if(($totals['rent_due'] ?? 0) > 0)
                @php
                    $runningBalance += $totals['rent_due'];
                    $totalDebits += $totals['rent_due'];
                @endphp
                <tr>
                    <td>{{ $period->startOfMonth()->format('M d, Y') }}</td>
                    <td>Invoice: Rent for {{ $period->format('F Y') }}</td>
                    <td class="text-end text-danger">KES {{ number_format($totals['rent_due'], 2) }}</td>
                    <td class="text-end">-</td>
                    <td class="text-end {{ $runningBalance > 0 ? 'text-danger' : 'text-success' }}">
                        KES {{ number_format($runningBalance, 2) }}
                    </td>
                </tr>
                @endif

                <!-- Water Invoice -->
                @if(($totals['water_due'] ?? 0) > 0)
                @php
                    $runningBalance += $totals['water_due'];
                    $totalDebits += $totals['water_due'];
                @endphp
                <tr>
                    <td>{{ $period->startOfMonth()->format('M d, Y') }}</td>
                    <td>Invoice: Water Charges</td>
                    <td class="text-end text-danger">KES {{ number_format($totals['water_due'], 2) }}</td>
                    <td class="text-end">-</td>
                    <td class="text-end {{ $runningBalance > 0 ? 'text-danger' : 'text-success' }}">
                        KES {{ number_format($runningBalance, 2) }}
                    </td>
                </tr>
                @endif

                <!-- Payments as Credits -->
                @foreach($totals['payments'] ?? [] as $payment)
                @php
                    $runningBalance -= $payment['amount'];
                    $totalCredits += $payment['amount'];
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($payment['date'])->format('M d, Y') }}</td>
                    <td>
                        Payment: <code>{{ $payment['reference'] }}</code>
                        <span class="badge bg-secondary ms-1">{{ ucfirst($payment['source']) }}</span>
                    </td>
                    <td class="text-end">-</td>
                    <td class="text-end text-success">KES {{ number_format($payment['amount'], 2) }}</td>
                    <td class="text-end {{ $runningBalance > 0 ? 'text-danger' : 'text-success' }}">
                        KES {{ number_format($runningBalance, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <th colspan="2">Closing Balance</th>
                    <th class="text-end text-danger">KES {{ number_format($totalDebits, 2) }}</th>
                    <th class="text-end text-success">KES {{ number_format($totalCredits, 2) }}</th>
                    <th class="text-end {{ ($totals['closing_balance'] ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                        <strong>KES {{ number_format($totals['closing_balance'] ?? 0, 2) }}</strong>
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- Payment Allocations Detail -->
@if(!empty($totals['payments']))
<div class="card app-card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fa-solid fa-money-bill-wave me-2"></i>Payment Allocations Detail</h5>
    </div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Reference</th>
                    <th>Source</th>
                    <th class="text-end">Amount</th>
                    <th>Allocation</th>
                </tr>
            </thead>
            <tbody>
                @foreach($totals['payments'] as $payment)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($payment['date'])->format('M d, Y') }}</td>
                    <td><code>{{ $payment['reference'] }}</code></td>
                    <td><span class="badge bg-secondary">{{ ucfirst($payment['source']) }}</span></td>
                    <td class="text-end text-success">KES {{ number_format($payment['amount'], 2) }}</td>
                    <td>
                        @foreach($payment['allocations'] ?? [] as $alloc)
                            <small class="d-block">
                                {{ ucfirst(str_replace('_', ' ', $alloc['type'])) }}: KES {{ number_format($alloc['amount'], 2) }}
                            </small>
                        @endforeach
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="mt-4 text-center text-muted">
    <small>This statement was automatically generated by the Property Manager system.</small>
</div>

@push('scripts')
<style>
    @media print {
        .app-navbar, .app-sidebar, .btn, .breadcrumb { display: none !important; }
        .app-main { margin: 0 !important; padding: 0 !important; }
        .card { border: 1px solid #ddd !important; }
    }
</style>
@endpush
@endsection
