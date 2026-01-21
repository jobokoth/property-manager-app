@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Units</h1>
                @can('properties.manage_units')
                    <a href="{{ route('units.create') }}" class="btn btn-primary">Add Unit</a>
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
                                    <th>Label</th>
                                    <th>Property</th>
                                    <th>Floor</th>
                                    <th>Bedrooms</th>
                                    <th>Rent Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($units as $unit)
                                    <tr>
                                        <td>{{ $unit->id }}</td>
                                        <td>{{ $unit->label }}</td>
                                        <td>{{ $unit->property->name }}</td>
                                        <td>{{ $unit->floor ?? 'N/A' }}</td>
                                        <td>{{ $unit->bedrooms }}</td>
                                        <td>KES {{ number_format($unit->rent_amount, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $unit->status == 'available' ? 'success' : ($unit->status == 'occupied' ? 'primary' : ($unit->status == 'maintenance' ? 'warning' : 'info')) }}">
                                                {{ ucfirst($unit->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('units.show', $unit) }}" class="btn btn-sm btn-info">View</a>
                                            @can('properties.manage_units')
                                                <a href="{{ route('units.edit', $unit) }}" class="btn btn-sm btn-warning">Edit</a>
                                                <form action="{{ route('units.destroy', $unit) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this unit?')">Delete</button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No units found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        
                        {{ $units->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection