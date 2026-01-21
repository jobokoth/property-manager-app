@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Upload M-Pesa Message</h1>
                <a href="{{ route('payments.index') }}" class="btn btn-secondary">Back to Payments</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Submit M-Pesa Payment</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('payments.mpesa-upload.process') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="tenancy_id" class="form-label">Select Tenant</label>
                            <select name="tenancy_id" id="tenancy_id" class="form-select @error('tenancy_id') is-invalid @enderror" required>
                                <option value="">-- Select a tenancy --</option>
                                @foreach($tenancies as $tenancy)
                                    <option value="{{ $tenancy->id }}" {{ old('tenancy_id') == $tenancy->id ? 'selected' : '' }}>
                                        {{ $tenancy->tenant->name }} - {{ $tenancy->unit->property->name }} ({{ $tenancy->unit->label }})
                                    </option>
                                @endforeach
                            </select>
                            @error('tenancy_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="mpesa_message" class="form-label">M-Pesa Message</label>
                            <textarea name="mpesa_message" id="mpesa_message" rows="5"
                                      class="form-control @error('mpesa_message') is-invalid @enderror"
                                      required
                                      placeholder="Paste the M-Pesa confirmation message here...">{{ old('mpesa_message') }}</textarea>
                            @error('mpesa_message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Paste the full M-Pesa confirmation SMS message. The system will automatically extract the transaction ID, amount, and date.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-upload me-1"></i> Process Payment
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Message Format</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted">The M-Pesa message should follow this format:</p>
                    <div class="bg-light p-2 rounded small">
                        <code>ABC123XYZ Confirmed. Ksh5,000.00 sent to JOHN DOE on 17/1/26 at 12:38 PM. New M-PESA balance is Ksh1,234.00.</code>
                    </div>
                    <hr>
                    <p class="small text-muted mb-1"><strong>Extracted data:</strong></p>
                    <ul class="small text-muted">
                        <li>Transaction ID: ABC123XYZ</li>
                        <li>Amount: Ksh 5,000.00</li>
                        <li>Date: 17/1/26 at 12:38 PM</li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5>Allocation Order</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Payments are automatically allocated in this order:</p>
                    <ol class="small text-muted">
                        <li>Rent arrears (oldest first)</li>
                        <li>Current month rent</li>
                        <li>Water arrears (oldest first)</li>
                        <li>Current month water</li>
                        <li>Advance payment (future rent)</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
