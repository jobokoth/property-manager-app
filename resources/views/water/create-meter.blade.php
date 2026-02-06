@extends('layouts.app')

@section('content')
<div class="page-header mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="{{ route('water.index') }}">Water</a></li>
            <li class="breadcrumb-item"><a href="{{ route('water.meters') }}">Meters</a></li>
            <li class="breadcrumb-item active">Add Meter</li>
        </ol>
    </nav>
    <h1 class="h3 mb-0">Add Water Meter</h1>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card app-card">
            <div class="card-body">
                <form action="{{ route('water.meters.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Unit</label>
                        <select name="unit_id" class="form-select @error('unit_id') is-invalid @enderror" required>
                            <option value="">Select a unit...</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->label ?? $unit->number }} - {{ $unit->property->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('unit_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if($units->isEmpty())
                            <small class="text-muted">All units already have water meters assigned.</small>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Meter Serial Number</label>
                        <input type="text" name="meter_serial" class="form-control @error('meter_serial') is-invalid @enderror"
                               value="{{ old('meter_serial') }}" placeholder="e.g., WM-12345" required>
                        @error('meter_serial')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-save me-1"></i> Save Meter
                        </button>
                        <a href="{{ route('water.meters') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
