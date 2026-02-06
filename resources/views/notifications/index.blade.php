@extends('layouts.app')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-1">Notifications</h1>
            <p class="text-muted mb-0">
                @if($unreadCount > 0)
                    You have {{ $unreadCount }} unread notification{{ $unreadCount > 1 ? 's' : '' }}
                @else
                    All caught up!
                @endif
            </p>
        </div>
        @if($unreadCount > 0)
            <form action="{{ route('notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-primary">
                    <i class="fa-solid fa-check-double me-1"></i> Mark All as Read
                </button>
            </form>
        @endif
    </div>
</div>

@if($notifications->isEmpty())
    <div class="card app-card">
        <div class="card-body text-center py-5">
            <i class="fa-solid fa-bell-slash fa-3x text-muted mb-3"></i>
            <h5>No notifications</h5>
            <p class="text-muted">You don't have any notifications yet.</p>
        </div>
    </div>
@else
    <div class="card app-card">
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @foreach($notifications as $notification)
                    <div class="list-group-item {{ $notification->status !== 'read' ? 'bg-light' : '' }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-1">
                                    @if($notification->status !== 'read')
                                        <span class="badge bg-primary me-2">New</span>
                                    @endif
                                    <h6 class="mb-0">{{ $notification->message->subject ?? 'Notification' }}</h6>
                                </div>
                                <p class="mb-1 text-muted">{{ $notification->message->body ?? '' }}</p>
                                <small class="text-muted">
                                    <i class="fa-regular fa-clock me-1"></i>
                                    {{ $notification->created_at->diffForHumans() }}
                                    @if($notification->read_at)
                                        <span class="ms-2">
                                            <i class="fa-solid fa-check me-1"></i>
                                            Read {{ $notification->read_at->diffForHumans() }}
                                        </span>
                                    @endif
                                </small>
                            </div>
                            @if($notification->status !== 'read')
                                <form action="{{ route('notifications.read', $notification) }}" method="POST" class="ms-3">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Mark as read">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $notifications->links() }}
    </div>
@endif
@endsection
