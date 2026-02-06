@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Vendors</h1>
        @can('vendors.create')
            <a href="{{ route('manage.vendors.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus me-1"></i> Add Vendor
            </a>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Assigned Properties</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendors as $vendor)
                            <tr>
                                <td>{{ $vendor->name }}</td>
                                <td>{{ $vendor->email }}</td>
                                <td>{{ $vendor->phone ?? 'N/A' }}</td>
                                <td>
                                    @foreach($vendor->properties as $property)
                                        <span class="badge bg-secondary">{{ $property->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <span class="badge bg-{{ $vendor->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($vendor->status) }}
                                    </span>
                                </td>
                                <td>
                                    @can('vendors.update')
                                        <a href="{{ route('manage.vendors.edit', $vendor) }}" class="btn btn-sm btn-warning">
                                            <i class="fa-solid fa-edit"></i>
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No vendors found for your properties.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $vendors->links() }}
        </div>
    </div>
</div>
@endsection
