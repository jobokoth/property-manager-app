@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Add New Vendor</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('manage.vendors.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <div class="row">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                           id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                           id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone" value="{{ old('phone') }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                       id="password" name="password" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control"
                                       id="password_confirmation" name="password_confirmation" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Assign to Properties <span class="text-danger">*</span></label>
                            @error('property_ids')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                            @if($properties->isEmpty())
                                <div class="alert alert-warning">
                                    <i class="fa-solid fa-exclamation-triangle me-2"></i>
                                    No properties available. You must have at least one property to assign a vendor.
                                </div>
                            @else
                                <div class="row">
                                    @foreach($properties as $property)
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                       name="property_ids[]" value="{{ $property->id }}"
                                                       id="property_{{ $property->id }}"
                                                       {{ in_array($property->id, old('property_ids', [])) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="property_{{ $property->id }}">
                                                    {{ $property->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted">Select at least one property</small>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('manage.vendors.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary" {{ $properties->isEmpty() ? 'disabled' : '' }}>Create Vendor</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
