@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Upload M-Pesa Payment Message</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('mpesa-messages.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="tenancy_id" class="form-label">Select Tenancy</label>
                            <select class="form-select @error('tenancy_id') is-invalid @enderror" id="tenancy_id" name="tenancy_id" required>
                                <option value="">Select a tenancy...</option>
                                @foreach($tenancies as $tenancy)
                                    <option value="{{ $tenancy->id }}" {{ old('tenancy_id') == $tenancy->id ? 'selected' : '' }}>
                                        {{ $tenancy->tenant->name }} (Unit: {{ $tenancy->unit->label }} - {{ $tenancy->unit->property->name }})
                                    </option>
                                @endforeach
                            </select>
                            @error('tenancy_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="raw_text" class="form-label">M-Pesa Message</label>
                            <textarea class="form-control @error('raw_text') is-invalid @enderror" id="raw_text" name="raw_text" rows="5" required placeholder="Paste the M-Pesa confirmation message here. e.g., UAHRS3Y810 Confirmed. Ksh10500.00 sent to Property on 17/1/26 at 12:38 PM.">{{ old('raw_text') }}</textarea>
                            @error('raw_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Upload and Process Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
