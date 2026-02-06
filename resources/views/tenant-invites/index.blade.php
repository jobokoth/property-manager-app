@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Tenant Invitations</h1>
        @can('invites.create')
            <a href="{{ route('tenant-invites.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus me-1"></i> Send Invitation
            </a>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Property</th>
                            <th>Unit</th>
                            <th>Rent</th>
                            <th>Status</th>
                            <th>Expires</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invites as $invite)
                            <tr>
                                <td>{{ $invite->name }}</td>
                                <td>{{ $invite->email }}</td>
                                <td>{{ $invite->property->name }}</td>
                                <td>{{ $invite->unit->label }}</td>
                                <td>KES {{ number_format($invite->rent_amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $invite->status === 'accepted' ? 'success' : ($invite->status === 'pending' ? 'warning' : ($invite->status === 'expired' ? 'secondary' : 'danger')) }}">
                                        {{ ucfirst($invite->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($invite->status === 'pending')
                                        {{ $invite->expires_at->format('M d, Y') }}
                                        @if($invite->isExpired())
                                            <span class="badge bg-danger">Expired</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('tenant-invites.show', $invite) }}" class="btn btn-sm btn-info">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    @if($invite->status === 'pending' && !$invite->isExpired())
                                        <form action="{{ route('tenant-invites.resend', $invite) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-secondary" title="Resend">
                                                <i class="fa-solid fa-paper-plane"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('tenant-invites.cancel', $invite) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Cancel this invitation?')" title="Cancel">
                                                <i class="fa-solid fa-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No invitations found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $invites->links() }}
        </div>
    </div>
</div>
@endsection
