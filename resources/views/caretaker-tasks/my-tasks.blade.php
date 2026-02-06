@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>My Tasks</h1>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3>{{ $tasks->where('status', 'pending')->count() }}</h3>
                    <p class="mb-0">Pending</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h3>{{ $tasks->where('status', 'in_progress')->count() }}</h3>
                    <p class="mb-0">In Progress</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3>{{ $tasks->where('status', 'completed')->count() }}</h3>
                    <p class="mb-0">Completed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h3>{{ $tasks->filter(fn($t) => $t->isOverdue())->count() }}</h3>
                    <p class="mb-0">Overdue</p>
                </div>
            </div>
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
                                <td>
                                    <span class="badge bg-{{ $task->priority === 'urgent' ? 'danger' : ($task->priority === 'high' ? 'warning' : ($task->priority === 'medium' ? 'info' : 'secondary')) }}">
                                        {{ ucfirst($task->priority) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'warning' : 'primary') }}">
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
                                    <a href="{{ route('caretaker-tasks.show', $task) }}" class="btn btn-sm btn-info">View</a>
                                    @if($task->status !== 'completed')
                                        <form action="{{ route('caretaker-tasks.complete', $task) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Mark as completed?')">
                                                Complete
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No tasks assigned to you.</td>
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
