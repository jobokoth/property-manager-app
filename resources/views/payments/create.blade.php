@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Record New Payment</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('payments.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="property_id" class="form-label">Property</label>
                            <select class="form-select @error('property_id') is-invalid @enderror" id="property_id" name="property_id" required>
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

                        <div class="mb-3">
                            <label for="tenancy_id" class="form-label">Tenancy (Optional)</label>
                            <select class="form-select @error('tenancy_id') is-invalid @enderror" id="tenancy_id" name="tenancy_id">
                                <option value="">Select Tenancy (Optional)</option>
                                @foreach($tenancies as $tenancy)
                                    <option value="{{ $tenancy->id }}" {{ old('tenancy_id') == $tenancy->id ? 'selected' : '' }}>
                                        {{ $tenancy->tenant->name }} - {{ $tenancy->unit->label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tenancy_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="payer_user_id" class="form-label">Payer (Optional)</label>
                            <select class="form-select @error('payer_user_id') is-invalid @enderror" id="payer_user_id" name="payer_user_id">
                                <option value="">Select Payer (Optional)</option>
                                @foreach($payers as $payer)
                                    <option value="{{ $payer->id }}" {{ old('payer_user_id') == $payer->id ? 'selected' : '' }}>
                                        {{ $payer->name }} ({{ $payer->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('payer_user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="source" class="form-label">Source</label>
                            <select class="form-select @error('source') is-invalid @enderror" id="source" name="source" required>
                                <option value="manual" {{ old('source') == 'manual' ? 'selected' : '' }}>Manual Entry</option>
                                <option value="mpesa" {{ old('source') == 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                            </select>
                            @error('source')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount (KES)</label>
                            <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount') }}" min="0.01" required>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="paid_at" class="form-label">Paid At</label>
                            <input type="datetime-local" class="form-control @error('paid_at') is-invalid @enderror" id="paid_at" name="paid_at" value="{{ old('paid_at', now()->format('Y-m-d\TH:i')) }}" required>
                            @error('paid_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="reference" class="form-label">Reference (Optional)</label>
                            <input type="text" class="form-control @error('reference') is-invalid @enderror" id="reference" name="reference" value="{{ old('reference') }}">
                            @error('reference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="confirmed" {{ old('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('payments.index') }}" class="btn btn-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection