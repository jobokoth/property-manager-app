@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Vendor Dashboard</h1>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="mb-0">{{ $stats['total_assigned'] }}</h3>
                    <p class="text-muted mb-0">Assigned Jobs</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="mb-0 text-warning">{{ $stats['pending_quotes'] }}</h3>
                    <p class="text-muted mb-0">Pending Quotes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="mb-0 text-success">{{ $stats['approved_quotes'] }}</h3>
                    <p class="text-muted mb-0">Approved Quotes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="mb-0 text-info">{{ $stats['pending_payments'] }}</h3>
                    <p class="text-muted mb-0">Awaiting Confirmation</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Assigned Service Requests -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>My Assigned Service Requests</h5>
                </div>
                <div class="card-body">
                    @if($assignedRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Property</th>
                                        <th>Unit</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Quote Status</th>
                                        <th>Assigned</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assignedRequests as $request)
                                        <tr>
                                            <td>{{ $request->property->name }}</td>
                                            <td>{{ $request->unit->label }}</td>
                                            <td>{{ Str::limit($request->title, 30) }}</td>
                                            <td>
                                                <span class="badge bg-secondary">{{ ucfirst($request->category) }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $request->priority == 'high' || $request->priority == 'urgent' ? 'danger' : ($request->priority == 'medium' ? 'warning' : 'success') }}">
                                                    {{ ucfirst($request->priority) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $request->status == 'completed' ? 'success' : ($request->status == 'in_progress' ? 'warning' : 'primary') }}">
                                                    {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $myQuote = $request->quotes->where('vendor_user_id', auth()->id())->first();
                                                @endphp
                                                @if($myQuote)
                                                    <span class="badge bg-{{ $myQuote->status == 'approved' ? 'success' : ($myQuote->status == 'rejected' ? 'danger' : 'warning') }}">
                                                        {{ ucfirst($myQuote->status) }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">No Quote</span>
                                                @endif
                                            </td>
                                            <td>{{ $request->assigned_at->format('M d, Y') }}</td>
                                            <td>
                                                <a href="{{ route('service-requests.show', $request) }}" class="btn btn-sm btn-primary">
                                                    <i class="fa-solid fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-3">
                            {{ $assignedRequests->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fa-solid fa-clipboard-list fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No service requests assigned to you yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
