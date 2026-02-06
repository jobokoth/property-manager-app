@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Send Tenant Invitation</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('tenant-invites.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="property_id" class="form-label">Property</label>
                                <select class="form-select @error('property_id') is-invalid @enderror"
                                        id="property_id" name="property_id" required>
                                    <option value="">Select Property</option>
                                    @foreach($properties as $property)
                                        <option value="{{ $property->id }}" {{ old('property_id') == $property->id ? 'selected' : '' }}>
                                            {{ $property->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('property_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="unit_id" class="form-label">Unit</label>
                                <select class="form-select @error('unit_id') is-invalid @enderror"
                                        id="unit_id" name="unit_id" required>
                                    <option value="">Select Unit</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}"
                                                data-property="{{ $unit->property_id }}"
                                                data-rent="{{ $unit->rent_amount }}"
                                                {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->property->name }} - {{ $unit->label }} (KES {{ number_format($unit->rent_amount, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('unit_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5>Tenant Information</h5>

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
                                <label for="phone" class="form-label">Phone Number (Optional)</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone" value="{{ old('phone') }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">
                        <h5>Tenancy Details</h5>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="rent_amount" class="form-label">Monthly Rent (KES)</label>
                                <input type="number" class="form-control @error('rent_amount') is-invalid @enderror"
                                       id="rent_amount" name="rent_amount" value="{{ old('rent_amount') }}"
                                       step="0.01" min="0" required>
                                @error('rent_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="deposit_amount" class="form-label">Deposit Amount (KES)</label>
                                <input type="number" class="form-control @error('deposit_amount') is-invalid @enderror"
                                       id="deposit_amount" name="deposit_amount" value="{{ old('deposit_amount') }}"
                                       step="0.01" min="0">
                                @error('deposit_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="start_date" class="form-label">Tenancy Start Date</label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                       id="start_date" name="start_date" value="{{ old('start_date') }}"
                                       min="{{ date('Y-m-d') }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fa-solid fa-info-circle me-2"></i>
                            The tenant will receive an email with a magic link to complete their registration and accept the tenancy.
                            The invitation will expire in 7 days.
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('tenant-invites.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Send Invitation</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('property_id').addEventListener('change', function() {
        const propertyId = this.value;
        const unitSelect = document.getElementById('unit_id');
        const options = unitSelect.querySelectorAll('option');

        options.forEach(option => {
            if (option.value === '') {
                option.style.display = '';
            } else if (option.dataset.property === propertyId) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        });

        unitSelect.value = '';
    });

    document.getElementById('unit_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption && selectedOption.dataset.rent) {
            document.getElementById('rent_amount').value = selectedOption.dataset.rent;
        }
    });
</script>
@endpush
@endsection
