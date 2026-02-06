@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Task Details</h1>
        <div>
            @can('caretaker_tasks.update')
                <a href="{{ route('caretaker-tasks.edit', $caretakerTask) }}" class="btn btn-warning">Edit</a>
            @endcan
            @if($caretakerTask->status !== 'completed')
                <form action="{{ route('caretaker-tasks.complete', $caretakerTask) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('Mark this task as completed?')">
                        Mark Complete
                    </button>
                </form>
            @endif
            <a href="{{ route('caretaker-tasks.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>{{ $caretakerTask->title }}</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Property:</strong> {{ $caretakerTask->property->name }}</p>
                            <p><strong>Assigned To:</strong> {{ $caretakerTask->caretaker->name }}</p>
                            <p><strong>Assigned By:</strong> {{ $caretakerTask->assignedBy->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Priority:</strong>
                                <span class="badge bg-{{ $caretakerTask->priority === 'urgent' ? 'danger' : ($caretakerTask->priority === 'high' ? 'warning' : ($caretakerTask->priority === 'medium' ? 'info' : 'secondary')) }}">
                                    {{ ucfirst($caretakerTask->priority) }}
                                </span>
                            </p>
                            <p><strong>Status:</strong>
                                <span class="badge bg-{{ $caretakerTask->status === 'completed' ? 'success' : ($caretakerTask->status === 'in_progress' ? 'warning' : ($caretakerTask->status === 'cancelled' ? 'secondary' : 'primary')) }}">
                                    {{ ucfirst(str_replace('_', ' ', $caretakerTask->status)) }}
                                </span>
                            </p>
                            <p><strong>Due Date:</strong>
                                @if($caretakerTask->due_date)
                                    {{ $caretakerTask->due_date->format('M d, Y') }}
                                    @if($caretakerTask->isOverdue())
                                        <span class="badge bg-danger">Overdue</span>
                                    @endif
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                    </div>

                    <hr>

                    <h5>Description</h5>
                    <p>{{ $caretakerTask->description ?? 'No description provided.' }}</p>

                    @if($caretakerTask->notes)
                        <hr>
                        <h5>Notes</h5>
                        <p>{{ $caretakerTask->notes }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Timeline</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <small class="text-muted">Created</small>
                            <br>{{ $caretakerTask->created_at->format('M d, Y H:i') }}
                        </li>
                        <li class="mb-2">
                            <small class="text-muted">Last Updated</small>
                            <br>{{ $caretakerTask->updated_at->format('M d, Y H:i') }}
                        </li>
                        @if($caretakerTask->completed_at)
                            <li class="mb-2">
                                <small class="text-muted">Completed</small>
                                <br>{{ $caretakerTask->completed_at->format('M d, Y H:i') }}
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            @if(auth()->user()->hasRole('caretaker') && $caretakerTask->status !== 'completed')
                <div class="card mt-3">
                    <div class="card-header">
                        <h5>Update Status</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('caretaker-tasks.update', $caretakerTask) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="pending" {{ $caretakerTask->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="in_progress" {{ $caretakerTask->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ $caretakerTask->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3">{{ $caretakerTask->notes }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Update</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
