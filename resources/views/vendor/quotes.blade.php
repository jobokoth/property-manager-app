@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>My Quotes</h1>
                <a href="{{ route('vendor.dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($quotes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Property</th>
                                        <th>Service Request</th>
                                        <th>Amount</th>
                                        <th>Est. Days</th>
                                        <th>Status</th>
                                        <th>Submitted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($quotes as $quote)
                                        <tr>
                                            <td>#{{ $quote->id }}</td>
                                            <td>{{ $quote->serviceRequest->property->name }}</td>
                                            <td>{{ Str::limit($quote->serviceRequest->title, 30) }}</td>
                                            <td>KES {{ number_format($quote->amount, 2) }}</td>
                                            <td>{{ $quote->estimated_days ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $quote->status == 'approved' ? 'success' : ($quote->status == 'rejected' ? 'danger' : 'warning') }}">
                                                    {{ ucfirst($quote->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $quote->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <a href="{{ route('service-requests.show', $quote->serviceRequest) }}" class="btn btn-sm btn-info">View Request</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-3">
                            {{ $quotes->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fa-solid fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                            <p class="text-muted">You haven't submitted any quotes yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
