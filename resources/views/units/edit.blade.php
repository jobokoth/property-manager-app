@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Edit Unit</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('units.update', $unit) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="property_id" class="form-label">Property</label>
                            <select class="form-select @error('property_id') is-invalid @enderror" id="property_id" name="property_id" required>
                                <option value="">Select Property</option>
                                @foreach($properties as $property)
                                    <option value="{{ $property->id }}" {{ old('property_id', $unit->property_id) == $property->id ? 'selected' : '' }}>
                                        {{ $property->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('property_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="label" class="form-label">Unit Label</label>
                            <input type="text" class="form-control @error('label') is-invalid @enderror" id="label" name="label" value="{{ old('label', $unit->label) }}" required>
                            @error('label')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="floor" class="form-label">Floor</label>
                            <input type="number" class="form-control @error('floor') is-invalid @enderror" id="floor" name="floor" value="{{ old('floor', $unit->floor) }}">
                            @error('floor')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="bedrooms" class="form-label">Number of Bedrooms</label>
                            <input type="number" class="form-control @error('bedrooms') is-invalid @enderror" id="bedrooms" name="bedrooms" value="{{ old('bedrooms', $unit->bedrooms) }}" min="0" required>
                            @error('bedrooms')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="rent_amount" class="form-label">Rent Amount (KES)</label>
                            <input type="number" step="0.01" class="form-control @error('rent_amount') is-invalid @enderror" id="rent_amount" name="rent_amount" value="{{ old('rent_amount', $unit->rent_amount) }}" min="0" required>
                            @error('rent_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="water_rate_mode" class="form-label">Water Rate Mode</label>
                            <select class="form-select @error('water_rate_mode') is-invalid @enderror" id="water_rate_mode" name="water_rate_mode" required>
                                <option value="per_unit" {{ old('water_rate_mode', $unit->water_rate_mode) == 'per_unit' ? 'selected' : '' }}>Per Unit</option>
                                <option value="per_meter" {{ old('water_rate_mode', $unit->water_rate_mode) == 'per_meter' ? 'selected' : '' }}>Per Meter</option>
                            </select>
                            @error('water_rate_mode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="available" {{ old('status', $unit->status) == 'available' ? 'selected' : '' }}>Available</option>
                                <option value="occupied" {{ old('status', $unit->status) == 'occupied' ? 'selected' : '' }}>Occupied</option>
                                <option value="maintenance" {{ old('status', $unit->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="reserved" {{ old('status', $unit->status) == 'reserved' ? 'selected' : '' }}>Reserved</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('units.index') }}" class="btn btn-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Unit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection