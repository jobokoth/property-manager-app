@extends('layouts.app')

@section('content')
<div class="page-header mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="{{ route('water.index') }}">Water</a></li>
            <li class="breadcrumb-item"><a href="{{ route('water.readings') }}">Readings</a></li>
            <li class="breadcrumb-item active">Record Reading</li>
        </ol>
    </nav>
    <h1 class="h3 mb-0">Record Water Reading</h1>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card app-card">
            <div class="card-body">
                <form action="{{ route('water.readings.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Water Meter</label>
                        <select name="water_meter_id" class="form-select @error('water_meter_id') is-invalid @enderror" required>
                            <option value="">Select a meter...</option>
                            @foreach($meters as $meter)
                                <option value="{{ $meter->id }}"
                                        {{ (old('water_meter_id') == $meter->id || request('meter') == $meter->id) ? 'selected' : '' }}
                                        data-last-reading="{{ $meter->readings->first()?->reading_value ?? 0 }}">
                                    {{ $meter->meter_serial }} - {{ $meter->unit->label ?? $meter->unit->number }} ({{ $meter->unit->property->name }})
                                    @if($meter->readings->isNotEmpty())
                                        [Last: {{ number_format($meter->readings->first()->reading_value, 2) }}]
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('water_meter_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reading Date</label>
                        <input type="date" name="reading_date" class="form-control @error('reading_date') is-invalid @enderror"
                               value="{{ old('reading_date', date('Y-m-d')) }}" required>
                        @error('reading_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reading Value (units)</label>
                        <input type="number" name="reading_value" step="0.01" min="0"
                               class="form-control @error('reading_value') is-invalid @enderror"
                               value="{{ old('reading_value') }}" placeholder="e.g., 1234.56" required>
                        @error('reading_value')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Reading must be greater than or equal to the previous reading.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes (optional)</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                                  rows="2" placeholder="Any observations...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-save me-1"></i> Save Reading
                        </button>
                        <a href="{{ route('water.readings') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
