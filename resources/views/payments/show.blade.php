@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Payment Details</h1>
                <div>
                    @can('payments.allocate')
                        <a href="{{ route('payments.edit', $payment) }}" class="btn btn-warning">Edit</a>
                    @endcan
                    <a href="{{ route('payments.index') }}" class="btn btn-secondary">Back to Payments</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Payment Information</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Amount:</strong> KES {{ number_format($payment->amount, 2) }}</p>
                            <p><strong>Property:</strong> {{ $payment->property->name }}</p>
                            <p><strong>Tenant:</strong> {{ $payment->tenancy->tenant->name ?? 'N/A' }}</p>
                            <p><strong>Payer:</strong> {{ $payment->payer->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Source:</strong> 
                                <span class="badge bg-{{ $payment->source == 'mpesa' ? 'success' : 'primary' }}">
                                    {{ ucfirst($payment->source) }}
                                </span>
                            </p>
                            <p><strong>Paid At:</strong> {{ $payment->paid_at->format('M d, Y H:i') }}</p>
                            <p><strong>Reference:</strong> {{ $payment->reference ?? 'N/A' }}</p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-{{ $payment->status == 'confirmed' ? 'success' : ($payment->status == 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($payment->status) }}
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
                    <h5>Payment Status</h5>
                </div>
                <div class="card-body text-center">
                    <div class="display-4 mb-3">
                        @if($payment->status == 'confirmed')
                            <i class="fas fa-check-circle text-success"></i>
                        @elseif($payment->status == 'pending')
                            <i class="fas fa-clock text-warning"></i>
                        @else
                            <i class="fas fa-times-circle text-danger"></i>
                        @endif
                    </div>
                    <h5 class="text-capitalize">{{ $payment->status }}</h5>
                    <p class="text-muted">
                        @if($payment->status == 'confirmed')
                            This payment has been confirmed and processed
                        @elseif($payment->status == 'pending')
                            This payment is awaiting confirmation
                        @else
                            This payment has been cancelled
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Allocations Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Payment Allocations</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Period</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payment->allocations as $allocation)
                                    <tr>
                                        <td>
                                            <span class="badge bg-{{ $allocation->allocation_type == 'rent' ? 'primary' : ($allocation->allocation_type == 'water' ? 'info' : 'secondary') }}">
                                                {{ ucfirst($allocation->allocation_type) }}
                                            </span>
                                        </td>
                                        <td>KES {{ number_format($allocation->amount, 2) }}</td>
                                        <td>{{ $allocation->period_month }}</td>
                                        <td>{{ $allocation->notes ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No allocations found for this payment</td>
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