@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Assign New Task</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('caretaker-tasks.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
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

                        <div class="mb-3">
                            <label for="caretaker_user_id" class="form-label">Assign to Caretaker</label>
                            <select class="form-select @error('caretaker_user_id') is-invalid @enderror"
                                    id="caretaker_user_id" name="caretaker_user_id" required>
                                <option value="">Select Caretaker</option>
                                @foreach($caretakers as $caretaker)
                                    <option value="{{ $caretaker->id }}"
                                            {{ old('caretaker_user_id', request('caretaker_id')) == $caretaker->id ? 'selected' : '' }}
                                            data-properties="{{ $caretaker->caretakerProperties->pluck('id')->join(',') }}">
                                        {{ $caretaker->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('caretaker_user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label">Task Title</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                   id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="4">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">Priority</label>
                                <select class="form-select @error('priority') is-invalid @enderror"
                                        id="priority" name="priority" required>
                                    <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="date" class="form-control @error('due_date') is-invalid @enderror"
                                       id="due_date" name="due_date" value="{{ old('due_date') }}"
                                       min="{{ date('Y-m-d') }}">
                                @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('caretaker-tasks.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Task</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
