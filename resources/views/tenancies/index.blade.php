@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Tenancies</h1>
                @can('properties.manage_tenants')
                    <a href="{{ route('tenancies.create') }}" class="btn btn-primary">Add Tenancy</a>
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
                                    <th>Tenant</th>
                                    <th>Unit</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Rent Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tenancies as $tenancy)
                                    <tr>
                                        <td>{{ $tenancy->id }}</td>
                                        <td>{{ $tenancy->tenant->name }}</td>
                                        <td>{{ $tenancy->unit->label }} ({{ $tenancy->unit->property->name }})</td>
                                        <td>{{ $tenancy->start_date->format('M d, Y') }}</td>
                                        <td>{{ $tenancy->end_date ? $tenancy->end_date->format('M d, Y') : 'Ongoing' }}</td>
                                        <td>KES {{ number_format($tenancy->rent_amount, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $tenancy->status == 'active' ? 'success' : ($tenancy->status == 'terminated' ? 'danger' : 'info') }}">
                                                {{ ucfirst($tenancy->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('tenancies.show', $tenancy) }}" class="btn btn-sm btn-info">View</a>
                                            @can('properties.manage_tenants')
                                                <a href="{{ route('tenancies.edit', $tenancy) }}" class="btn btn-sm btn-warning">Edit</a>
                                                <form action="{{ route('tenancies.destroy', $tenancy) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to terminate this tenancy?')">Terminate</button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No tenancies found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        
                        {{ $tenancies->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection