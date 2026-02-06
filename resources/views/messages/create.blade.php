@extends('layouts.app')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-1">Compose Message</h1>
            <p class="text-muted mb-0">Send a message to tenants, caretakers, or vendors.</p>
        </div>
        <a href="{{ route('messages.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Messages
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card app-card">
            <div class="card-body">
                <form action="{{ route('messages.store') }}" method="POST" id="messageForm">
                    @csrf

                    {{-- Property Selection --}}
                    <div class="mb-4">
                        <label for="property_id" class="form-label">Property <span class="text-danger">*</span></label>
                        <select class="form-select @error('property_id') is-invalid @enderror"
                                id="property_id" name="property_id" required>
                            <option value="">Select a property</option>
                            @foreach($properties as $property)
                                <option value="{{ $property->id }}"
                                        data-tenants="{{ json_encode($property->tenancies->map(fn($t) => ['id' => $t->tenant->id ?? null, 'name' => $t->tenant->name ?? 'Unknown'])->filter(fn($t) => $t['id'])->values()) }}"
                                        data-caretakers="{{ json_encode($property->caretakers->map(fn($c) => ['id' => $c->id, 'name' => $c->name])) }}"
                                        data-vendors="{{ json_encode($property->vendors->map(fn($v) => ['id' => $v->id, 'name' => $v->name])) }}"
                                        {{ old('property_id') == $property->id ? 'selected' : '' }}>
                                    {{ $property->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('property_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Audience Type Selection --}}
                    <div class="mb-4">
                        <label class="form-label">Audience Type <span class="text-danger">*</span></label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="audience_type" id="audience_individual"
                                       value="individual" {{ old('audience_type', 'individual') === 'individual' ? 'checked' : '' }}>
                                <label class="form-check-label" for="audience_individual">
                                    <i class="fa-solid fa-user me-1"></i> Individual Recipients
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="audience_type" id="audience_group"
                                       value="group" {{ old('audience_type') === 'group' ? 'checked' : '' }}>
                                <label class="form-check-label" for="audience_group">
                                    <i class="fa-solid fa-users me-1"></i> Group Message
                                </label>
                            </div>
                        </div>
                        @error('audience_type')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Individual Recipients Section --}}
                    <div class="mb-4" id="individualSection">
                        <label for="recipients" class="form-label">Select Recipients <span class="text-danger">*</span></label>
                        <select class="form-select @error('recipients') is-invalid @enderror @error('recipients.*') is-invalid @enderror"
                                id="recipients" name="recipients[]" multiple size="8">
                            {{-- Options will be populated by JavaScript based on selected property --}}
                        </select>
                        <div class="form-text">Hold Ctrl (Cmd on Mac) to select multiple recipients.</div>
                        @error('recipients')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @error('recipients.*')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Group Selection Section --}}
                    <div class="mb-4 d-none" id="groupSection">
                        <label class="form-label">Select Groups <span class="text-danger">*</span></label>
                        <div class="border rounded p-3 bg-light">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="groups[]" id="group_tenants" value="tenants"
                                       {{ is_array(old('groups')) && in_array('tenants', old('groups')) ? 'checked' : '' }}>
                                <label class="form-check-label" for="group_tenants">
                                    <i class="fa-solid fa-house-user me-1 text-primary"></i> All Tenants
                                    <span class="badge bg-secondary ms-1" id="tenantCount">0</span>
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="groups[]" id="group_caretakers" value="caretakers"
                                       {{ is_array(old('groups')) && in_array('caretakers', old('groups')) ? 'checked' : '' }}>
                                <label class="form-check-label" for="group_caretakers">
                                    <i class="fa-solid fa-user-tie me-1 text-success"></i> All Caretakers
                                    <span class="badge bg-secondary ms-1" id="caretakerCount">0</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="groups[]" id="group_vendors" value="vendors"
                                       {{ is_array(old('groups')) && in_array('vendors', old('groups')) ? 'checked' : '' }}>
                                <label class="form-check-label" for="group_vendors">
                                    <i class="fa-solid fa-truck me-1 text-warning"></i> All Vendors
                                    <span class="badge bg-secondary ms-1" id="vendorCount">0</span>
                                </label>
                            </div>
                        </div>
                        @error('groups')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-4">

                    {{-- Message Content --}}
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('subject') is-invalid @enderror"
                               id="subject" name="subject" value="{{ old('subject') }}"
                               placeholder="Enter message subject" required maxlength="255">
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="body" class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('body') is-invalid @enderror"
                                  id="body" name="body" rows="6"
                                  placeholder="Enter your message here..." required>{{ old('body') }}</textarea>
                        @error('body')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Email Notification Option --}}
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="send_email" id="send_email" value="1"
                                   {{ old('send_email', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="send_email">
                                <i class="fa-solid fa-envelope me-1"></i> Send email notification to recipients
                            </label>
                        </div>
                        <div class="form-text">Recipients will receive an email copy of this message.</div>
                    </div>

                    {{-- Form Actions --}}
                    <div class="d-flex justify-content-between pt-3 border-top">
                        <a href="{{ route('messages.index') }}" class="btn btn-secondary">
                            <i class="fa-solid fa-times me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-paper-plane me-1"></i> Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const propertySelect = document.getElementById('property_id');
    const audienceIndividual = document.getElementById('audience_individual');
    const audienceGroup = document.getElementById('audience_group');
    const individualSection = document.getElementById('individualSection');
    const groupSection = document.getElementById('groupSection');
    const recipientsSelect = document.getElementById('recipients');
    const tenantCountBadge = document.getElementById('tenantCount');
    const caretakerCountBadge = document.getElementById('caretakerCount');
    const vendorCountBadge = document.getElementById('vendorCount');

    // Toggle audience sections
    function toggleAudienceSections() {
        if (audienceIndividual.checked) {
            individualSection.classList.remove('d-none');
            groupSection.classList.add('d-none');
        } else {
            individualSection.classList.add('d-none');
            groupSection.classList.remove('d-none');
        }
    }

    audienceIndividual.addEventListener('change', toggleAudienceSections);
    audienceGroup.addEventListener('change', toggleAudienceSections);

    // Update recipients when property changes
    function updateRecipients() {
        const selectedOption = propertySelect.options[propertySelect.selectedIndex];

        // Clear current options
        recipientsSelect.innerHTML = '';

        if (!selectedOption.value) {
            tenantCountBadge.textContent = '0';
            caretakerCountBadge.textContent = '0';
            vendorCountBadge.textContent = '0';
            return;
        }

        const tenants = JSON.parse(selectedOption.dataset.tenants || '[]');
        const caretakers = JSON.parse(selectedOption.dataset.caretakers || '[]');
        const vendors = JSON.parse(selectedOption.dataset.vendors || '[]');

        // Update group counts
        tenantCountBadge.textContent = tenants.length;
        caretakerCountBadge.textContent = caretakers.length;
        vendorCountBadge.textContent = vendors.length;

        // Add tenants optgroup
        if (tenants.length > 0) {
            const tenantsGroup = document.createElement('optgroup');
            tenantsGroup.label = 'Tenants';
            tenants.forEach(tenant => {
                const option = document.createElement('option');
                option.value = tenant.id;
                option.textContent = tenant.name;
                tenantsGroup.appendChild(option);
            });
            recipientsSelect.appendChild(tenantsGroup);
        }

        // Add caretakers optgroup
        if (caretakers.length > 0) {
            const caretakersGroup = document.createElement('optgroup');
            caretakersGroup.label = 'Caretakers';
            caretakers.forEach(caretaker => {
                const option = document.createElement('option');
                option.value = caretaker.id;
                option.textContent = caretaker.name;
                caretakersGroup.appendChild(option);
            });
            recipientsSelect.appendChild(caretakersGroup);
        }

        // Add vendors optgroup
        if (vendors.length > 0) {
            const vendorsGroup = document.createElement('optgroup');
            vendorsGroup.label = 'Vendors';
            vendors.forEach(vendor => {
                const option = document.createElement('option');
                option.value = vendor.id;
                option.textContent = vendor.name;
                vendorsGroup.appendChild(option);
            });
            recipientsSelect.appendChild(vendorsGroup);
        }
    }

    propertySelect.addEventListener('change', updateRecipients);

    // Initialize on page load
    toggleAudienceSections();
    updateRecipients();
});
</script>
@endpush
@endsection
