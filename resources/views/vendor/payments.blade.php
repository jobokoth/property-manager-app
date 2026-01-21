@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>My Payments</h1>
                <a href="{{ route('vendor.dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($payments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Property</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Reference</th>
                                        <th>Status</th>
                                        <th>Paid At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $payment)
                                        <tr>
                                            <td>{{ $payment->invoice->invoice_number }}</td>
                                            <td>{{ $payment->invoice->serviceRequest->property->name }}</td>
                                            <td>KES {{ number_format($payment->amount, 2) }}</td>
                                            <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                            <td>{{ $payment->reference ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $payment->status == 'confirmed' ? 'success' : ($payment->status == 'paid' ? 'info' : 'warning') }}">
                                                    {{ ucfirst($payment->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $payment->paid_at ? $payment->paid_at->format('M d, Y') : 'N/A' }}</td>
                                            <td>
                                                @if($payment->status === 'paid' && !$payment->confirmed_at)
                                                    <form action="{{ route('vendor.confirm-payment', $payment) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Confirm you have received this payment?')">
                                                            Confirm Receipt
                                                        </button>
                                                    </form>
                                                @elseif($payment->status === 'confirmed')
                                                    <span class="text-success"><i class="fa-solid fa-check-circle"></i> Confirmed</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-3">
                            {{ $payments->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fa-solid fa-money-bill-wave fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No payments recorded yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
