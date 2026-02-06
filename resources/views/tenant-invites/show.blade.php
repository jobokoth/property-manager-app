@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Invitation Details</h1>
        <div>
            @if($tenantInvite->status === 'pending' && !$tenantInvite->isExpired())
                <form action="{{ route('tenant-invites.resend', $tenantInvite) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-secondary">Resend Invitation</button>
                </form>
                <form action="{{ route('tenant-invites.cancel', $tenantInvite) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Cancel this invitation?')">Cancel</button>
                </form>
            @endif
            <a href="{{ route('tenant-invites.index') }}" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Invitation to {{ $tenantInvite->name }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Tenant Information</h6>
                            <p><strong>Name:</strong> {{ $tenantInvite->name }}</p>
                            <p><strong>Email:</strong> {{ $tenantInvite->email }}</p>
                            <p><strong>Phone:</strong> {{ $tenantInvite->phone ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Property Information</h6>
                            <p><strong>Property:</strong> {{ $tenantInvite->property->name }}</p>
                            <p><strong>Unit:</strong> {{ $tenantInvite->unit->label }}</p>
                            <p><strong>Location:</strong> {{ $tenantInvite->property->location }}</p>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h6>Tenancy Details</h6>
                            <p><strong>Monthly Rent:</strong> KES {{ number_format($tenantInvite->rent_amount, 2) }}</p>
                            <p><strong>Deposit:</strong> KES {{ number_format($tenantInvite->deposit_amount ?? 0, 2) }}</p>
                            <p><strong>Start Date:</strong> {{ $tenantInvite->start_date->format('F j, Y') }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Invitation Details</h6>
                            <p><strong>Invited By:</strong> {{ $tenantInvite->invitedBy->name }}</p>
                            <p><strong>Sent On:</strong> {{ $tenantInvite->created_at->format('F j, Y H:i') }}</p>
                            @if($tenantInvite->status === 'pending')
                                <p><strong>Expires:</strong> {{ $tenantInvite->expires_at->format('F j, Y H:i') }}</p>
                            @endif
                            @if($tenantInvite->accepted_at)
                                <p><strong>Accepted:</strong> {{ $tenantInvite->accepted_at->format('F j, Y H:i') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Status</h5>
                </div>
                <div class="card-body text-center">
                    <div class="display-4 mb-3">
                        @if($tenantInvite->status === 'pending')
                            @if($tenantInvite->isExpired())
                                <i class="fa-solid fa-clock text-secondary"></i>
                            @else
                                <i class="fa-solid fa-envelope-open text-warning"></i>
                            @endif
                        @elseif($tenantInvite->status === 'accepted')
                            <i class="fa-solid fa-check-circle text-success"></i>
                        @elseif($tenantInvite->status === 'cancelled')
                            <i class="fa-solid fa-ban text-danger"></i>
                        @else
                            <i class="fa-solid fa-clock text-secondary"></i>
                        @endif
                    </div>
                    <h5>
                        @if($tenantInvite->status === 'pending' && $tenantInvite->isExpired())
                            Expired
                        @else
                            {{ ucfirst($tenantInvite->status) }}
                        @endif
                    </h5>
                    <p class="text-muted">
                        @if($tenantInvite->status === 'pending')
                            @if($tenantInvite->isExpired())
                                This invitation has expired and can no longer be used.
                            @else
                                Waiting for the tenant to accept the invitation.
                            @endif
                        @elseif($tenantInvite->status === 'accepted')
                            The tenant has accepted this invitation and their account is active.
                        @elseif($tenantInvite->status === 'cancelled')
                            This invitation was cancelled.
                        @else
                            This invitation has expired.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
