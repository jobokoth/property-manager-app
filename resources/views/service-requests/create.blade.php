@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4>Create New Service Request</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('service-requests.store') }}" enctype="multipart/form-data">
                        @csrf

@if(Auth::user()->hasRole('tenant'))
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted">Property</label>
                                <p class="form-control-plaintext fw-medium">{{ $activeTenancy->unit->property->name }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted">Unit</label>
                                <p class="form-control-plaintext fw-medium">{{ $activeTenancy->unit->label }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted">Tenant</label>
                                <p class="form-control-plaintext fw-medium">{{ Auth::user()->name }}</p>
                            </div>
                        </div>
                        <hr class="mb-4">

                        <!-- Title Field (moved to top for tenants) -->
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required placeholder="Brief summary of the issue">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Category and Priority -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required>
                                        <option value="">Select Category</option>
                                        <option value="plumbing" {{ old('category') == 'plumbing' ? 'selected' : '' }}>Plumbing</option>
                                        <option value="electrical" {{ old('category') == 'electrical' ? 'selected' : '' }}>Electrical</option>
                                        <option value="carpentry" {{ old('category') == 'carpentry' ? 'selected' : '' }}>Carpentry</option>
                                        <option value="painting" {{ old('category') == 'painting' ? 'selected' : '' }}>Painting</option>
                                        <option value="cleaning" {{ old('category') == 'cleaning' ? 'selected' : '' }}>Cleaning</option>
                                        <option value="appliance" {{ old('category') == 'appliance' ? 'selected' : '' }}>Appliance</option>
                                        <option value="security" {{ old('category') == 'security' ? 'selected' : '' }}>Security</option>
                                        <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="priority" class="form-label">Priority</label>
                                    <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5" required placeholder="Please describe the issue in detail...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Media Uploads for Tenant -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="images" class="form-label">Photos (up to 10)</label>
                                    <input type="file" class="form-control @error('images') is-invalid @enderror" id="images" name="images[]" accept="image/*" multiple>
                                    @error('images')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @error('images.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="videos" class="form-label">Video (1 max)</label>
                                    <input type="file" class="form-control @error('videos') is-invalid @enderror" id="videos" name="videos[]" accept="video/mp4,video/quicktime">
                                    <small class="text-muted">Optional: Upload a single video of the issue</small>
                                    @error('videos')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @error('videos.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('service-requests.index') }}" class="btn btn-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Submit Request</button>
                        </div>
                        @else
                        <div class="row">
                            <div class="col-md-6">
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
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="unit_id" class="form-label">Unit</label>
                                    <select class="form-select @error('unit_id') is-invalid @enderror" id="unit_id" name="unit_id" required>
                                        <option value="">Select Unit</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }} data-property="{{ $unit->property_id }}">
                                                {{ $unit->label }} - {{ $unit->property->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('unit_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tenancy_id" class="form-label">Tenancy</label>
                                    <select class="form-select @error('tenancy_id') is-invalid @enderror" id="tenancy_id" name="tenancy_id" required>
                                        <option value="">Select Tenancy</option>
                                        @foreach($tenancies as $tenancy)
                                            <option value="{{ $tenancy->id }}" {{ old('tenancy_id') == $tenancy->id ? 'selected' : '' }} data-unit="{{ $tenancy->unit_id }}">
                                                {{ $tenancy->tenant->name }} - {{ $tenancy->unit->label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('tenancy_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tenant_user_id" class="form-label">Tenant</label>
                                    <select class="form-select @error('tenant_user_id') is-invalid @enderror" id="tenant_user_id" name="tenant_user_id" required>
                                        <option value="">Select Tenant</option>
                                        @foreach($tenants as $tenant)
                                            <option value="{{ $tenant->id }}" {{ old('tenant_user_id') == $tenant->id ? 'selected' : '' }}>
                                                {{ $tenant->name }} ({{ $tenant->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('tenant_user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <!-- Title Field (for non-tenants) -->
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required>
                                        <option value="">Select Category</option>
                                        <option value="plumbing" {{ old('category') == 'plumbing' ? 'selected' : '' }}>Plumbing</option>
                                        <option value="electrical" {{ old('category') == 'electrical' ? 'selected' : '' }}>Electrical</option>
                                        <option value="carpentry" {{ old('category') == 'carpentry' ? 'selected' : '' }}>Carpentry</option>
                                        <option value="painting" {{ old('category') == 'painting' ? 'selected' : '' }}>Painting</option>
                                        <option value="cleaning" {{ old('category') == 'cleaning' ? 'selected' : '' }}>Cleaning</option>
                                        <option value="appliance" {{ old('category') == 'appliance' ? 'selected' : '' }}>Appliance</option>
                                        <option value="security" {{ old('category') == 'security' ? 'selected' : '' }}>Security</option>
                                        <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="priority" class="form-label">Priority</label>
                                    <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5" required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="images" class="form-label">Photos (up to 10)</label>
                                    <input type="file" class="form-control @error('images') is-invalid @enderror" id="images" name="images[]" accept="image/*" multiple>
                                    @error('images')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @error('images.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="videos" class="form-label">Videos (up to 3)</label>
                                    <input type="file" class="form-control @error('videos') is-invalid @enderror" id="videos" name="videos[]" accept="video/mp4,video/quicktime" multiple>
                                    @error('videos')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @error('videos.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="open" {{ old('status') == 'open' ? 'selected' : '' }}>Open</option>
                                <option value="in_review" {{ old('status') == 'in_review' ? 'selected' : '' }}>In Review</option>
                                <option value="quoted" {{ old('status') == 'quoted' ? 'selected' : '' }}>Quoted</option>
                                <option value="scheduled" {{ old('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="closed" {{ old('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('service-requests.index') }}" class="btn btn-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Submit Request</button>
                        </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@unless(Auth::user()->hasRole('tenant'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    const propertySelect = document.getElementById('property_id');
    const unitSelect = document.getElementById('unit_id');
    const tenancySelect = document.getElementById('tenancy_id');
    
    // Update unit options based on selected property
    propertySelect.addEventListener('change', function() {
        const selectedPropertyId = this.value;
        
        // Show all units if no property selected, otherwise filter by property
        Array.from(unitSelect.options).forEach(option => {
            if (selectedPropertyId === '' || option.dataset.property === selectedPropertyId) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        });
        
        // Reset selection
        unitSelect.value = '';
    });
    
    // Update tenancy options based on selected unit
    unitSelect.addEventListener('change', function() {
        const selectedUnitId = this.value;
        
        // Show all tenancies if no unit selected, otherwise filter by unit
        Array.from(tenancySelect.options).forEach(option => {
            if (selectedUnitId === '' || option.dataset.unit === selectedUnitId) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        });
        
        // Reset selection
        tenancySelect.value = '';
    });
});
</script>
@endunless
@endsection