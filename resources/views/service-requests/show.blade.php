@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Service Request Details</h1>
                <div>
                    @can('requests.update_status')
                        <a href="{{ route('service-requests.edit', $serviceRequest) }}" class="btn btn-warning">Edit</a>
                    @endcan
                    <a href="{{ route('service-requests.index') }}" class="btn btn-secondary">Back to Requests</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>{{ $serviceRequest->title }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Category:</strong> 
                                <span class="badge bg-{{ $serviceRequest->category == 'plumbing' ? 'primary' : ($serviceRequest->category == 'electrical' ? 'warning' : ($serviceRequest->category == 'carpentry' ? 'success' : 'info')) }}">
                                    {{ ucfirst($serviceRequest->category) }}
                                </span>
                            </p>
                            <p><strong>Priority:</strong> 
                                <span class="badge bg-{{ $serviceRequest->priority == 'high' || $serviceRequest->priority == 'urgent' ? 'danger' : ($serviceRequest->priority == 'medium' ? 'warning' : 'success') }}">
                                    {{ ucfirst($serviceRequest->priority) }}
                                </span>
                            </p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-{{ $serviceRequest->status == 'open' ? 'primary' : ($serviceRequest->status == 'in_progress' ? 'warning' : ($serviceRequest->status == 'completed' ? 'success' : 'secondary')) }}">
                                    {{ ucfirst($serviceRequest->status) }}
                                </span>
                            </p>
                            <p><strong>Created At:</strong> {{ $serviceRequest->created_at->format('M d, Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Property:</strong> {{ $serviceRequest->property->name }}</p>
                            <p><strong>Unit:</strong> {{ $serviceRequest->unit->label }}</p>
                            <p><strong>Tenant:</strong> {{ $serviceRequest->tenant->name }}</p>
                            <p><strong>Updated At:</strong> {{ $serviceRequest->updated_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h5>Description</h5>
                    <p>{{ $serviceRequest->description }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Request Status</h5>
                </div>
                <div class="card-body text-center">
                    <div class="display-4 mb-3">
                        @if($serviceRequest->status == 'open')
                            <i class="fas fa-exclamation-circle text-primary"></i>
                        @elseif($serviceRequest->status == 'in_review')
                            <i class="fas fa-search text-info"></i>
                        @elseif($serviceRequest->status == 'quoted')
                            <i class="fas fa-file-invoice-dollar text-warning"></i>
                        @elseif($serviceRequest->status == 'scheduled')
                            <i class="fas fa-calendar-check text-info"></i>
                        @elseif($serviceRequest->status == 'in_progress')
                            <i class="fas fa-tools text-warning"></i>
                        @elseif($serviceRequest->status == 'completed')
                            <i class="fas fa-check-circle text-success"></i>
                        @else
                            <i class="fas fa-folder text-secondary"></i>
                        @endif
                    </div>
                    <h5 class="text-capitalize">{{ str_replace('_', ' ', $serviceRequest->status) }}</h5>
                    <p class="text-muted">
                        @if($serviceRequest->status == 'open')
                            This request is open and awaiting review
                        @elseif($serviceRequest->status == 'in_review')
                            This request is currently under review
                        @elseif($serviceRequest->status == 'quoted')
                            A quote has been provided for this request
                        @elseif($serviceRequest->status == 'scheduled')
                            This request has been scheduled for work
                        @elseif($serviceRequest->status == 'in_progress')
                            Work is currently in progress
                        @elseif($serviceRequest->status == 'completed')
                            This request has been completed
                        @else
                            This request has been closed
                        @endif
                    </p>
                </div>
            </div>

            <!-- Assigned Vendor Card -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5>Assigned Vendor</h5>
                </div>
                <div class="card-body">
                    @if($serviceRequest->assignedVendor)
                        <p><strong>{{ $serviceRequest->assignedVendor->name }}</strong></p>
                        <p class="text-muted mb-1">{{ $serviceRequest->assignedVendor->email }}</p>
                        @if($serviceRequest->assignedVendor->phone)
                            <p class="text-muted mb-1">{{ $serviceRequest->assignedVendor->phone }}</p>
                        @endif
                        <p class="small text-muted">Assigned: {{ $serviceRequest->assigned_at->format('M d, Y H:i') }}</p>
                        @if($serviceRequest->assignment_notes)
                            <p class="small"><strong>Notes:</strong> {{ $serviceRequest->assignment_notes }}</p>
                        @endif
                        @can('requests.assign_vendor')
                            <form action="{{ route('service-requests.remove-vendor', $serviceRequest) }}" method="POST" class="mt-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove vendor assignment?')">
                                    Remove Vendor
                                </button>
                            </form>
                        @endcan
                    @else
                        <p class="text-muted">No vendor assigned yet.</p>
                        @can('requests.assign_vendor')
                            @if(count($availableVendors) > 0)
                                <form action="{{ route('service-requests.assign-vendor', $serviceRequest) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <select name="vendor_id" class="form-select" required>
                                            <option value="">Select a vendor...</option>
                                            @foreach($availableVendors as $vendor)
                                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <textarea name="assignment_notes" class="form-control" rows="2" placeholder="Assignment notes (optional)"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm">Assign Vendor</button>
                                </form>
                            @else
                                <p class="text-muted small">No vendors available.</p>
                            @endif
                        @endcan
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Vendor Quote Submission (for assigned vendor) -->
    @if(auth()->user()->hasRole('vendor') && $serviceRequest->assigned_vendor_id === auth()->id())
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Submit Quote</h5>
                    </div>
                    <div class="card-body">
                        @if($serviceRequest->quotes->where('vendor_user_id', auth()->id())->where('status', 'pending')->count() > 0)
                            <div class="alert alert-info">You have a pending quote for this request.</div>
                        @else
                            <form action="{{ route('vendor.submit-quote', $serviceRequest) }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Quote Amount (KES)</label>
                                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Estimated Days</label>
                                        <input type="number" name="estimated_days" class="form-control" min="1">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="3" required placeholder="Describe the work to be done and breakdown of costs..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit Quote</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Quotes Section -->
    @if($serviceRequest->quotes->count() > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Quotes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Vendor</th>
                                        <th>Amount</th>
                                        <th>Est. Days</th>
                                        <th>Status</th>
                                        <th>Submitted</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($serviceRequest->quotes as $quote)
                                        <tr>
                                            <td>{{ $quote->vendor->name }}</td>
                                            <td>KES {{ number_format($quote->amount, 2) }}</td>
                                            <td>{{ $quote->estimated_days ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $quote->status == 'approved' ? 'success' : ($quote->status == 'rejected' ? 'danger' : 'warning') }}">
                                                    {{ ucfirst($quote->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $quote->created_at->format('M d, Y') }}</td>
                                            <td>
                                                @if($quote->status === 'pending' && auth()->user()->can('requests.assign_vendor'))
                                                    <form action="{{ route('service-requests.approve-quote', [$serviceRequest, $quote]) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                                    </form>
                                                    <form action="{{ route('service-requests.reject-quote', [$serviceRequest, $quote]) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="6" class="small text-muted">{{ $quote->description }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Vendor Invoice Submission (for assigned vendor after work completion) -->
    @if(auth()->user()->hasRole('vendor') && $serviceRequest->assigned_vendor_id === auth()->id() && $serviceRequest->quotes->where('status', 'approved')->count() > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Submit Invoice</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('vendor.submit-invoice', $serviceRequest) }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Invoice Amount (KES)</label>
                                    <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required
                                           value="{{ $serviceRequest->quotes->where('status', 'approved')->first()?->amount }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Based on Quote</label>
                                    <select name="quote_id" class="form-select">
                                        <option value="">Select quote (optional)</option>
                                        @foreach($serviceRequest->quotes->where('status', 'approved') as $quote)
                                            <option value="{{ $quote->id }}">Quote #{{ $quote->id }} - KES {{ number_format($quote->amount, 2) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3" required placeholder="Describe the work completed..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Invoice</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Invoices Section -->
    @if($serviceRequest->invoices->count() > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Invoices</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Vendor</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Submitted</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($serviceRequest->invoices as $invoice)
                                        <tr>
                                            <td>{{ $invoice->invoice_number }}</td>
                                            <td>{{ $invoice->vendor->name }}</td>
                                            <td>KES {{ number_format($invoice->amount, 2) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $invoice->status == 'paid' ? 'success' : ($invoice->status == 'approved' ? 'info' : 'warning') }}">
                                                    {{ ucfirst($invoice->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $invoice->created_at->format('M d, Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Media Attachments -->
    @if($serviceRequest->media->count() > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Media Attachments</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($serviceRequest->media as $media)
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100">
                                        @if($media->type == 'image')
                                            <img src="{{ $media->url }}" class="card-img-top" alt="Service Request Image" style="height: 200px; object-fit: cover;">
                                        @elseif($media->type == 'video')
                                            <video class="card-img-top" style="height: 200px; object-fit: cover;" controls preload="metadata">
                                                <source src="{{ $media->url }}" type="video/{{ $media->format }}">
                                                Your browser does not support the video tag.
                                            </video>
                                        @else
                                            <div class="card-img-top d-flex align-items-center justify-content-center" style="height: 200px; background-color: #f8f9fa;">
                                                <i class="fas fa-file fa-3x text-muted"></i>
                                            </div>
                                        @endif
                                        <div class="card-body">
                                            <h6 class="card-title">{{ $media->format }}</h6>
                                            <small class="text-muted">{{ $media->type }} • {{ number_format($media->bytes / 1024, 2) }} KB</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection