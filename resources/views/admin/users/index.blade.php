@extends('layouts.app')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-1">User Management</h1>
            <p class="text-muted mb-0">Manage system users and their roles</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-user-plus me-1"></i> Add User
        </a>
    </div>
</div>

<div class="card app-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="avatar-sm me-2 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </span>
                                    <div>
                                        <strong>{{ $user->name }}</strong>
                                        @if($user->id === auth()->id())
                                            <span class="badge bg-info ms-1">You</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?? '-' }}</td>
                            <td>
                                @foreach($user->roles as $role)
                                    <span class="badge bg-{{ $role->name === 'super_admin' ? 'danger' : ($role->name === 'owner' ? 'primary' : 'secondary') }}">
                                        {{ str_replace('_', ' ', ucfirst($role->name)) }}
                                    </span>
                                @endforeach
                            </td>
                            <td>
                                <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'warning' }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.users.show', $user) }}">
                                                <i class="fa-solid fa-eye me-2"></i> View
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.users.edit', $user) }}">
                                                <i class="fa-solid fa-edit me-2"></i> Edit
                                            </a>
                                        </li>
                                        @if($user->id !== auth()->id())
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        @if($user->status === 'active')
                                                            <i class="fa-solid fa-ban me-2"></i> Disable
                                                        @else
                                                            <i class="fa-solid fa-check me-2"></i> Enable
                                                        @endif
                                                    </button>
                                                </form>
                                            </li>
                                            <li>
                                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fa-solid fa-trash me-2"></i> Delete
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $users->links() }}
</div>
@endsection
