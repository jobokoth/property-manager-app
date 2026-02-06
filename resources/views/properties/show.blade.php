@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Property Details</h1>
                <div>
                    @can('properties.update')
                        <a href="{{ route('properties.edit', $property) }}" class="btn btn-warning">Edit</a>
                    @endcan
                    <a href="{{ route('properties.index') }}" class="btn btn-secondary">Back to Properties</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>{{ $property->name }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> {{ $property->name }}</p>
                            <p><strong>Location:</strong> {{ $property->location }}</p>
                            <p><strong>Status:</strong> 
                                <span class="badge bg-{{ $property->status == 'active' ? 'success' : ($property->status == 'inactive' ? 'secondary' : 'warning') }}">
                                    {{ ucfirst($property->status) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Owner:</strong> {{ $property->owner->name ?? 'N/A' }}</p>
                            <p><strong>Owner Email:</strong> {{ $property->owner->email ?? 'N/A' }}</p>
                            <p><strong>Created At:</strong> {{ $property->created_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Quick Stats</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <h3>{{ $property->units->count() }}</h3>
                            <p class="text-muted">Units</p>
                        </div>
                        <div class="col-6 mb-3">
                            <h3>{{ $property->tenancies->where('status', 'active')->count() }}</h3>
                            <p class="text-muted">Tenants</p>
                        </div>
                        <div class="col-6 mb-3">
                            <h3>{{ $property->tenancies->count() }}</h3>
                            <p class="text-muted">Tenancies</p>
                        </div>
                        <div class="col-6 mb-3">
                            <h3>KES {{ number_format($property->units->sum('rent_amount'), 2) }}</h3>
                            <p class="text-muted">Monthly Rent</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Property Managers Section -->
    @if(auth()->user()->hasRole(['owner', 'super_admin']) || $property->owner_user_id === auth()->id())
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Property Managers</h5>
                </div>
                <div class="card-body">
                    @if($property->managers->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($property->managers as $manager)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $manager->name }}</strong>
                                        <br><small class="text-muted">{{ $manager->email }}</small>
                                        @if($manager->pivot->is_primary)
                                            <span class="badge bg-primary ms-2">Primary</span>
                                        @endif
                                    </div>
                                    <form action="{{ route('properties.remove-manager', [$property, $manager]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this manager?')">
                                            <i class="fa-solid fa-times"></i>
                                        </button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted mb-3">No property managers assigned.</p>
                    @endif

                    @if(count($availableManagers) > 0)
                        <form action="{{ route('properties.assign-manager', $property) }}" method="POST" class="mt-3">
                            @csrf
                            <div class="input-group">
                                <select name="user_id" class="form-select" required>
                                    <option value="">Select a manager...</option>
                                    @foreach($availableManagers as $manager)
                                        <option value="{{ $manager->id }}">{{ $manager->name }} ({{ $manager->email }})</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-primary">Assign</button>
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="is_primary" id="isPrimary" value="1">
                                <label class="form-check-label" for="isPrimary">Set as primary manager</label>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Assigned Vendors</h5>
                </div>
                <div class="card-body">
                    @if($property->vendors->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($property->vendors as $vendor)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $vendor->name }}</strong>
                                        <br><small class="text-muted">{{ $vendor->email }}</small>
                                    </div>
                                    <form action="{{ route('properties.remove-vendor', [$property, $vendor]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this vendor?')">
                                            <i class="fa-solid fa-times"></i>
                                        </button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted mb-3">No vendors assigned to this property.</p>
                    @endif

                    @if(count($availableVendors) > 0)
                        <form action="{{ route('properties.assign-vendor', $property) }}" method="POST" class="mt-3">
                            @csrf
                            <div class="input-group">
                                <select name="user_id" class="form-select" required>
                                    <option value="">Select a vendor...</option>
                                    @foreach($availableVendors as $vendor)
                                        <option value="{{ $vendor->id }}">{{ $vendor->name }} ({{ $vendor->email }})</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-primary">Assign</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Caretakers Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Caretakers</h5>
                    <a href="{{ route('caretakers.create') }}" class="btn btn-sm btn-primary">Add Caretaker</a>
                </div>
                <div class="card-body">
                    @if($property->caretakers->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($property->caretakers as $caretaker)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $caretaker->name }}</strong>
                                        <br><small class="text-muted">{{ $caretaker->email }}</small>
                                        @if($caretaker->phone)
                                            <br><small class="text-muted">{{ $caretaker->phone }}</small>
                                        @endif
                                    </div>
                                    <div>
                                        <a href="{{ route('caretakers.show', $caretaker) }}" class="btn btn-sm btn-info">View</a>
                                        <a href="{{ route('caretaker-tasks.create') }}?caretaker_id={{ $caretaker->id }}&property_id={{ $property->id }}" class="btn btn-sm btn-success">Assign Task</a>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted mb-0">No caretakers assigned to this property.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Units Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Units in this Property</h5>
                    @can('properties.manage_units')
                        <a href="{{ route('units.create') }}?property_id={{ $property->id }}" class="btn btn-primary btn-sm">Add Unit</a>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Label</th>
                                    <th>Floor</th>
                                    <th>Bedrooms</th>
                                    <th>Rent Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($property->units as $unit)
                                    <tr>
                                        <td>{{ $unit->label }}</td>
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
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No units found for this property</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection