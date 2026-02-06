@extends('layouts.app')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-1">Sent Messages</h1>
            <p class="text-muted mb-0">View and track messages you've sent to tenants, caretakers, and vendors.</p>
        </div>
        <a href="{{ route('messages.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-paper-plane me-1"></i> Compose Message
        </a>
    </div>
</div>

@if($messages->isEmpty())
    <div class="card app-card">
        <div class="card-body text-center py-5">
            <i class="fa-solid fa-envelope fa-3x text-muted mb-3"></i>
            <h5>No messages sent yet</h5>
            <p class="text-muted">You haven't sent any messages. Click "Compose Message" to send your first message.</p>
            <a href="{{ route('messages.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-paper-plane me-1"></i> Compose Message
            </a>
        </div>
    </div>
@else
    <div class="card app-card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Property</th>
                        <th>Type</th>
                        <th class="text-center">Recipients</th>
                        <th class="text-center">Read</th>
                        <th>Sent</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($messages as $message)
                        <tr>
                            <td>
                                <a href="{{ route('messages.show', $message) }}" class="text-decoration-none fw-medium">
                                    {{ Str::limit($message->subject, 40) }}
                                </a>
                            </td>
                            <td>
                                <span class="text-muted">{{ $message->property->name ?? 'N/A' }}</span>
                            </td>
                            <td>
                                @if($message->audience_type === 'group')
                                    <span class="badge bg-info">Group</span>
                                @else
                                    <span class="badge bg-secondary">Individual</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ $message->deliveries_count }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $message->read_count > 0 ? 'bg-success' : 'bg-light text-dark' }}">
                                    {{ $message->read_count }} / {{ $message->deliveries_count }}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">{{ $message->created_at->diffForHumans() }}</small>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('messages.show', $message) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $messages->links() }}
    </div>
@endif
@endsection
