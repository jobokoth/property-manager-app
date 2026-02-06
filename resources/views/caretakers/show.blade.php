@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Caretaker Details</h1>
        <div>
            @can('properties.manage_caretakers')
                <a href="{{ route('caretakers.edit', $caretaker) }}" class="btn btn-warning">Edit</a>
            @endcan
            <a href="{{ route('caretakers.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Profile Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> {{ $caretaker->name }}</p>
                    <p><strong>Email:</strong> {{ $caretaker->email }}</p>
                    <p><strong>Phone:</strong> {{ $caretaker->phone ?? 'N/A' }}</p>
                    <p><strong>Status:</strong>
                        <span class="badge bg-{{ $caretaker->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($caretaker->status) }}
                        </span>
                    </p>
                    <p><strong>Member Since:</strong> {{ $caretaker->created_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Assigned Properties</h5>
                </div>
                <div class="card-body">
                    @if($caretaker->caretakerProperties->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($caretaker->caretakerProperties as $property)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $property->name }}</strong>
                                        <br><small class="text-muted">{{ $property->location }}</small>
                                    </div>
                                    <a href="{{ route('properties.show', $property) }}" class="btn btn-sm btn-outline-primary">
                                        View
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">No properties assigned.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Recent Tasks</h5>
                    @can('caretaker_tasks.create')
                        <a href="{{ route('caretaker-tasks.create') }}?caretaker_id={{ $caretaker->id }}" class="btn btn-sm btn-primary">
                            Assign Task
                        </a>
                    @endcan
                </div>
                <div class="card-body">
                    @if($caretaker->caretakerTasks->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Property</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Due Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($caretaker->caretakerTasks as $task)
                                        <tr>
                                            <td>{{ $task->title }}</td>
                                            <td>{{ $task->property->name }}</td>
                                            <td>
                                                <span class="badge bg-{{ $task->priority === 'urgent' ? 'danger' : ($task->priority === 'high' ? 'warning' : ($task->priority === 'medium' ? 'info' : 'secondary')) }}">
                                                    {{ ucfirst($task->priority) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'warning' : ($task->status === 'cancelled' ? 'secondary' : 'primary')) }}">
                                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                                </span>
                                            </td>
                                            <td>{{ $task->due_date ? $task->due_date->format('M d, Y') : 'N/A' }}</td>
                                            <td>
                                                <a href="{{ route('caretaker-tasks.show', $task) }}" class="btn btn-sm btn-info">View</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No tasks assigned yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
