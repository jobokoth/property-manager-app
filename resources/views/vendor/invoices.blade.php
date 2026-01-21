@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>My Invoices</h1>
                <a href="{{ route('vendor.dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($invoices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Property</th>
                                        <th>Service Request</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Submitted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoices as $invoice)
                                        <tr>
                                            <td>{{ $invoice->invoice_number }}</td>
                                            <td>{{ $invoice->serviceRequest->property->name }}</td>
                                            <td>{{ Str::limit($invoice->serviceRequest->title, 30) }}</td>
                                            <td>KES {{ number_format($invoice->amount, 2) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $invoice->status == 'paid' ? 'success' : ($invoice->status == 'approved' ? 'info' : ($invoice->status == 'rejected' ? 'danger' : 'warning')) }}">
                                                    {{ ucfirst($invoice->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $invoice->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <a href="{{ route('service-requests.show', $invoice->serviceRequest) }}" class="btn btn-sm btn-info">View Request</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-3">
                            {{ $invoices->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fa-solid fa-file-invoice fa-3x text-muted mb-3"></i>
                            <p class="text-muted">You haven't submitted any invoices yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
