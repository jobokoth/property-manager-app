@extends('layouts.app')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                    <li class="breadcrumb-item active">{{ $user->name }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">User Details</h1>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                <i class="fa-solid fa-edit me-1"></i> Edit
            </a>
            @if($user->id !== auth()->id())
                <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-{{ $user->status === 'active' ? 'warning' : 'success' }}">
                        @if($user->status === 'active')
                            <i class="fa-solid fa-ban me-1"></i> Disable
                        @else
                            <i class="fa-solid fa-check me-1"></i> Enable
                        @endif
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card app-card">
            <div class="card-body text-center">
                <div class="avatar-lg mx-auto mb-3 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                     style="width: 80px; height: 80px; font-size: 2rem;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <h4 class="mb-1">{{ $user->name }}</h4>
                <p class="text-muted mb-2">{{ $user->email }}</p>
                @foreach($user->roles as $role)
                    <span class="badge bg-{{ $role->name === 'super_admin' ? 'danger' : ($role->name === 'owner' ? 'primary' : 'secondary') }} fs-6">
                        {{ str_replace('_', ' ', ucfirst($role->name)) }}
                    </span>
                @endforeach
                <div class="mt-3">
                    <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'warning' }} fs-6">
                        {{ ucfirst($user->status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card app-card">
            <div class="card-header">
                <h5 class="mb-0">User Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td class="text-muted" style="width: 30%;">Full Name</td>
                        <td><strong>{{ $user->name }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Email Address</td>
                        <td>{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Phone Number</td>
                        <td>{{ $user->phone ?? 'Not provided' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Role</td>
                        <td>
                            @foreach($user->roles as $role)
                                {{ str_replace('_', ' ', ucfirst($role->name)) }}
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Account Status</td>
                        <td>
                            <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'warning' }}">
                                {{ ucfirst($user->status) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Created</td>
                        <td>{{ $user->created_at->format('F d, Y \a\t h:i A') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Last Updated</td>
                        <td>{{ $user->updated_at->format('F d, Y \a\t h:i A') }}</td>
                    </tr>
                    @if($user->last_login_at)
                    <tr>
                        <td class="text-muted">Last Login</td>
                        <td>{{ \Carbon\Carbon::parse($user->last_login_at)->format('F d, Y \a\t h:i A') }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        @if($user->roles->first()?->name !== 'super_admin')
        <div class="card app-card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Permissions</h5>
            </div>
            <div class="card-body">
                @php
                    $permissions = $user->getAllPermissions();
                @endphp
                @if($permissions->isEmpty())
                    <p class="text-muted mb-0">No direct permissions assigned.</p>
                @else
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($permissions as $permission)
                            <span class="badge bg-light text-dark">{{ $permission->name }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
