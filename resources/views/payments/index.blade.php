@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Payments</h1>
                @can('payments.ingest_mpesa')
                    <a href="{{ route('payments.create') }}" class="btn btn-primary">Record Payment</a>
                @endcan
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Amount</th>
                                    <th>Property</th>
                                    <th>Tenant</th>
                                    <th>Paid At</th>
                                    <th>Source</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $payment)
                                    <tr>
                                        <td>{{ $payment->id }}</td>
                                        <td>KES {{ number_format($payment->amount, 2) }}</td>
                                        <td>{{ $payment->property->name }}</td>
                                        <td>{{ $payment->tenancy->tenant->name ?? 'N/A' }}</td>
                                        <td>{{ $payment->paid_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $payment->source == 'mpesa' ? 'success' : 'primary' }}">
                                                {{ ucfirst($payment->source) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $payment->status == 'confirmed' ? 'success' : ($payment->status == 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($payment->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('payments.show', $payment) }}" class="btn btn-sm btn-info">View</a>
                                            @can('payments.allocate')
                                                <a href="{{ route('payments.edit', $payment) }}" class="btn btn-sm btn-warning">Edit</a>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No payments found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        
                        {{ $payments->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection