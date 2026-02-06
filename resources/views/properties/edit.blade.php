@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Edit Property</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('properties.update', $property) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Property Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $property->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <textarea class="form-control @error('location') is-invalid @enderror" id="location" name="location" rows="3" required>{{ old('location', $property->location) }}</textarea>
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if(auth()->user()->hasRole('super_admin'))
                            <div class="mb-3">
                                <label for="owner_user_id" class="form-label">Owner</label>
                                <select class="form-select @error('owner_user_id') is-invalid @enderror" id="owner_user_id" name="owner_user_id">
                                    <option value="">Select Owner (Optional)</option>
                                    @foreach($owners ?? [] as $owner)
                                        <option value="{{ $owner->id }}" {{ old('owner_user_id', $property->owner_user_id) == $owner->id ? 'selected' : '' }}>
                                            {{ $owner->name }} ({{ $owner->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('owner_user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @else
                            @if($property->owner)
                                <div class="mb-3">
                                    <label class="form-label">Owner</label>
                                    <input type="text" class="form-control" value="{{ $property->owner->name }}" disabled>
                                </div>
                            @endif
                        @endif

                        <div class="mb-3">
                            <label for="property_manager_id" class="form-label">Property Manager</label>
                            <select class="form-select @error('property_manager_id') is-invalid @enderror" id="property_manager_id" name="property_manager_id">
                                <option value="">Select Property Manager (Optional)</option>
                                @foreach($propertyManagers ?? [] as $manager)
                                    <option value="{{ $manager->id }}" {{ old('property_manager_id', $currentManagerId ?? null) == $manager->id ? 'selected' : '' }}>
                                        {{ $manager->name }} ({{ $manager->email }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Assign a property manager to handle day-to-day operations</small>
                            @error('property_manager_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="active" {{ old('status', $property->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $property->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="maintenance" {{ old('status', $property->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('properties.index') }}" class="btn btn-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Property</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection