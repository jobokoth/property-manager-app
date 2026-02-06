@extends('layouts.app')

@section('content')
<div class="page-header mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="{{ route('water.index') }}">Water</a></li>
            <li class="breadcrumb-item"><a href="{{ route('water.charges') }}">Charges</a></li>
            <li class="breadcrumb-item active">Add Charge</li>
        </ol>
    </nav>
    <h1 class="h3 mb-0">Add Water Charge</h1>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card app-card">
            <div class="card-body">
                <form action="{{ route('water.charges.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Tenancy</label>
                        <select name="tenancy_id" class="form-select @error('tenancy_id') is-invalid @enderror" required>
                            <option value="">Select a tenancy...</option>
                            @foreach($tenancies as $tenancy)
                                <option value="{{ $tenancy->id }}" {{ old('tenancy_id') == $tenancy->id ? 'selected' : '' }}>
                                    {{ $tenancy->tenant->name }} - {{ $tenancy->unit->label ?? $tenancy->unit->number }} ({{ $tenancy->unit->property->name }})
                                </option>
                            @endforeach
                        </select>
                        @error('tenancy_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Period</label>
                        <input type="month" name="period_month" class="form-control @error('period_month') is-invalid @enderror"
                               value="{{ old('period_month', date('Y-m')) }}" required>
                        @error('period_month')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Amount (KES)</label>
                        <input type="number" name="amount" step="0.01" min="0"
                               class="form-control @error('amount') is-invalid @enderror"
                               value="{{ old('amount') }}" placeholder="e.g., 500.00" required>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes (optional)</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                  rows="2" placeholder="Any notes...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-save me-1"></i> Save Charge
                        </button>
                        <a href="{{ route('water.charges') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
