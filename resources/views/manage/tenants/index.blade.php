@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Tenants</h1>
        @can('tenants.create')
            <a href="{{ route('manage.tenants.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus me-1"></i> Add Tenant
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
                            <th>Active Tenancies</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tenants as $tenant)
                            <tr>
                                <td>{{ $tenant->name }}</td>
                                <td>{{ $tenant->email }}</td>
                                <td>{{ $tenant->phone ?? 'N/A' }}</td>
                                <td>
                                    @foreach($tenant->tenancies->where('status', 'active') as $tenancy)
                                        <span class="badge bg-info">
                                            {{ $tenancy->unit->property->name ?? 'N/A' }} - {{ $tenancy->unit->label ?? 'N/A' }}
                                        </span>
                                    @endforeach
                                    @if($tenant->tenancies->where('status', 'active')->count() === 0)
                                        <span class="text-muted">No active tenancy</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $tenant->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($tenant->status) }}
                                    </span>
                                </td>
                                <td>
                                    @can('tenants.update')
                                        <a href="{{ route('manage.tenants.edit', $tenant) }}" class="btn btn-sm btn-warning">
                                            <i class="fa-solid fa-edit"></i>
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No tenants found for your properties.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $tenants->links() }}
        </div>
    </div>
</div>
@endsection
