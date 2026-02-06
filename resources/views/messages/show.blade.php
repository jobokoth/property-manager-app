@extends('layouts.app')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-1">Message Details</h1>
            <p class="text-muted mb-0">View message content and delivery status.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('messages.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-paper-plane me-1"></i> Compose New
            </a>
            <a href="{{ route('messages.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Messages
            </a>
        </div>
    </div>
</div>

<div class="row">
    {{-- Message Content --}}
    <div class="col-lg-8">
        <div class="card app-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fa-solid fa-envelope me-2"></i>
                    {{ $message->subject }}
                </h5>
                @if($message->audience_type === 'group')
                    <span class="badge bg-info">Group Message</span>
                @else
                    <span class="badge bg-secondary">Individual Message</span>
                @endif
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex gap-4 text-muted small mb-3">
                        <span>
                            <i class="fa-solid fa-building me-1"></i>
                            {{ $message->property->name ?? 'N/A' }}
                        </span>
                        <span>
                            <i class="fa-solid fa-calendar me-1"></i>
                            {{ $message->created_at->format('M d, Y \a\t g:i A') }}
                        </span>
                    </div>
                </div>
                <div class="border rounded p-3 bg-light">
                    <div class="message-body" style="white-space: pre-wrap;">{{ $message->body }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Delivery Summary --}}
    <div class="col-lg-4">
        <div class="card app-card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fa-solid fa-chart-pie me-2"></i>
                    Delivery Summary
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="border rounded p-2">
                            <h3 class="mb-0 text-primary">{{ $deliveryStats['total'] }}</h3>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded p-2">
                            <h3 class="mb-0 text-success">{{ $deliveryStats['read'] }}</h3>
                            <small class="text-muted">Read</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded p-2">
                            <h3 class="mb-0 text-warning">{{ $deliveryStats['unread'] }}</h3>
                            <small class="text-muted">Unread</small>
                        </div>
                    </div>
                </div>
                @if($deliveryStats['total'] > 0)
                    <div class="progress mt-3" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar"
                             style="width: {{ ($deliveryStats['read'] / $deliveryStats['total']) * 100 }}%"
                             aria-valuenow="{{ $deliveryStats['read'] }}"
                             aria-valuemin="0"
                             aria-valuemax="{{ $deliveryStats['total'] }}">
                        </div>
                    </div>
                    <div class="text-center mt-2">
                        <small class="text-muted">
                            {{ round(($deliveryStats['read'] / $deliveryStats['total']) * 100) }}% read
                        </small>
                    </div>
                @endif
            </div>
        </div>

        {{-- Audience Info --}}
        @if($message->audience_type === 'group' && isset($message->audience_payload['groups']))
            <div class="card app-card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fa-solid fa-users me-2"></i>
                        Target Groups
                    </h6>
                </div>
                <div class="card-body">
                    @foreach($message->audience_payload['groups'] as $group)
                        <span class="badge bg-secondary me-1 mb-1">
                            @if($group === 'tenants')
                                <i class="fa-solid fa-house-user me-1"></i> All Tenants
                            @elseif($group === 'caretakers')
                                <i class="fa-solid fa-user-tie me-1"></i> All Caretakers
                            @elseif($group === 'vendors')
                                <i class="fa-solid fa-truck me-1"></i> All Vendors
                            @else
                                {{ ucfirst($group) }}
                            @endif
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Recipients Table --}}
<div class="card app-card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fa-solid fa-users me-2"></i>
            Recipients ({{ $deliveryStats['total'] }})
        </h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Recipient</th>
                    <th>Email</th>
                    <th class="text-center">Status</th>
                    <th>Sent At</th>
                    <th>Read At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($message->deliveries as $delivery)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="avatar-sm bg-light rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                    <i class="fa-solid fa-user text-muted"></i>
                                </span>
                                <span>{{ $delivery->recipient->name ?? 'Unknown User' }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="text-muted">{{ $delivery->recipient->email ?? 'N/A' }}</span>
                        </td>
                        <td class="text-center">
                            @if($delivery->status === 'read')
                                <span class="badge bg-success">
                                    <i class="fa-solid fa-check-double me-1"></i> Read
                                </span>
                            @elseif($delivery->status === 'sent')
                                <span class="badge bg-warning text-dark">
                                    <i class="fa-solid fa-check me-1"></i> Delivered
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    {{ ucfirst($delivery->status) }}
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($delivery->sent_at)
                                <small class="text-muted">{{ $delivery->sent_at->format('M d, Y g:i A') }}</small>
                            @else
                                <small class="text-muted">-</small>
                            @endif
                        </td>
                        <td>
                            @if($delivery->read_at)
                                <small class="text-muted">{{ $delivery->read_at->format('M d, Y g:i A') }}</small>
                            @else
                                <small class="text-muted">-</small>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            No recipients found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
