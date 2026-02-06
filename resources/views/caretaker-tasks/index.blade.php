@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Caretaker Tasks</h1>
        @can('caretaker_tasks.create')
            <a href="{{ route('caretaker-tasks.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus me-1"></i> Assign Task
            </a>
        @endcan
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('caretaker-tasks.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="priority" class="form-select">
                        <option value="">All Priorities</option>
                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-secondary">Filter</button>
                    <a href="{{ route('caretaker-tasks.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Property</th>
                            <th>Caretaker</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                            <tr class="{{ $task->isOverdue() ? 'table-danger' : '' }}">
                                <td>{{ $task->title }}</td>
                                <td>{{ $task->property->name }}</td>
                                <td>{{ $task->caretaker->name }}</td>
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
                                <td>
                                    @if($task->due_date)
                                        {{ $task->due_date->format('M d, Y') }}
                                        @if($task->isOverdue())
                                            <span class="badge bg-danger">Overdue</span>
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('caretaker-tasks.show', $task) }}" class="btn btn-sm btn-info">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    @can('caretaker_tasks.update')
                                        <a href="{{ route('caretaker-tasks.edit', $task) }}" class="btn btn-sm btn-warning">
                                            <i class="fa-solid fa-edit"></i>
                                        </a>
                                    @endcan
                                    @if($task->status !== 'completed')
                                        <form action="{{ route('caretaker-tasks.complete', $task) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Mark this task as completed?')">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No tasks found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $tasks->links() }}
        </div>
    </div>
</div>
@endsection
