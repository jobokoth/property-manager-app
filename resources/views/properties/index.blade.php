@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Properties</h1>
                @can('properties.create')
                    <a href="{{ route('properties.create') }}" class="btn btn-primary">Add Property</a>
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
                                    <th>Name</th>
                                    <th>Location</th>
                                    <th>Owner</th>
                                    <th>Status</th>
                                    <th>Units</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($properties as $property)
                                    <tr>
                                        <td>{{ $property->id }}</td>
                                        <td>{{ $property->name }}</td>
                                        <td>{{ Str::limit($property->location, 30) }}</td>
                                        <td>{{ $property->owner->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $property->status == 'active' ? 'success' : ($property->status == 'inactive' ? 'secondary' : 'warning') }}">
                                                {{ ucfirst($property->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $property->units->count() }}</td>
                                        <td>
                                            <a href="{{ route('properties.show', $property) }}" class="btn btn-sm btn-info">View</a>
                                            @can('properties.update')
                                                <a href="{{ route('properties.edit', $property) }}" class="btn btn-sm btn-warning">Edit</a>
                                            @endcan
                                            @can('properties.delete')
                                                <form action="{{ route('properties.destroy', $property) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this property?')">Delete</button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No properties found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        
                        {{ $properties->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection