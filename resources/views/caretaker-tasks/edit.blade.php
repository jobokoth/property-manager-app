@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Edit Task</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('caretaker-tasks.update', $caretakerTask) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="property_id" class="form-label">Property</label>
                            <select class="form-select @error('property_id') is-invalid @enderror"
                                    id="property_id" name="property_id" required>
                                <option value="">Select Property</option>
                                @foreach($properties as $property)
                                    <option value="{{ $property->id }}" {{ old('property_id', $caretakerTask->property_id) == $property->id ? 'selected' : '' }}>
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
                                    <option value="{{ $caretaker->id }}" {{ old('caretaker_user_id', $caretakerTask->caretaker_user_id) == $caretaker->id ? 'selected' : '' }}>
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
                                   id="title" name="title" value="{{ old('title', $caretakerTask->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="4">{{ old('description', $caretakerTask->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="priority" class="form-label">Priority</label>
                                <select class="form-select @error('priority') is-invalid @enderror"
                                        id="priority" name="priority" required>
                                    <option value="low" {{ old('priority', $caretakerTask->priority) === 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority', $caretakerTask->priority) === 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('priority', $caretakerTask->priority) === 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ old('priority', $caretakerTask->priority) === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select @error('status') is-invalid @enderror"
                                        id="status" name="status" required>
                                    <option value="pending" {{ old('status', $caretakerTask->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="in_progress" {{ old('status', $caretakerTask->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ old('status', $caretakerTask->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ old('status', $caretakerTask->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="date" class="form-control @error('due_date') is-invalid @enderror"
                                       id="due_date" name="due_date"
                                       value="{{ old('due_date', $caretakerTask->due_date?->format('Y-m-d')) }}">
                                @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes" name="notes" rows="3">{{ old('notes', $caretakerTask->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('caretaker-tasks.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Task</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
