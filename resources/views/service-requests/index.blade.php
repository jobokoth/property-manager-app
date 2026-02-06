@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>
                    @if(auth()->user()->hasRole('tenant') && $unitLabel)
                        Service Requests for {{ $unitLabel }}
                    @else
                        Service Requests
                    @endif
                </h1>
                @can('requests.create')
                    <a href="{{ route('service-requests.create') }}" class="btn btn-primary">New Request</a>
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
                                    <th>Title</th>
                                    <th>Category</th>
                                    @unless(auth()->user()->hasRole('tenant'))
                                        <th>Tenant</th>
                                        <th>Unit</th>
                                    @endunless
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($serviceRequests as $request)
                                    <tr>
                                        <td>{{ $request->id }}</td>
                                        <td>{{ Str::limit($request->title, 30) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $request->category == 'plumbing' ? 'primary' : ($request->category == 'electrical' ? 'warning' : ($request->category == 'carpentry' ? 'success' : 'info')) }}">
                                                {{ ucfirst($request->category) }}
                                            </span>
                                        </td>
                                        @unless(auth()->user()->hasRole('tenant'))
                                            <td>{{ $request->tenant->name }}</td>
                                            <td>{{ $request->unit->label }}</td>
                                        @endunless
                                        <td>
                                            <span class="badge bg-{{ $request->priority == 'high' || $request->priority == 'urgent' ? 'danger' : ($request->priority == 'medium' ? 'warning' : 'success') }}">
                                                {{ ucfirst($request->priority) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $request->status == 'open' ? 'primary' : ($request->status == 'in_progress' ? 'warning' : ($request->status == 'completed' ? 'success' : 'secondary')) }}">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('service-requests.show', $request) }}" class="btn btn-sm btn-info">View</a>
                                            @can('requests.update_status')
                                                <a href="{{ route('service-requests.edit', $request) }}" class="btn btn-sm btn-warning">Edit</a>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ auth()->user()->hasRole('tenant') ? '7' : '9' }}" class="text-center">No service requests found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        
                        {{ $serviceRequests->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection