@extends('layouts.app')

@section('content')
<div class="page-header mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>
    <h1 class="h3 mb-0">Edit User</h1>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card app-card">
            <div class="card-body">
                <form action="{{ route('admin.users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <div class="row">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" id="first_name" name="first_name"
                                       class="form-control @error('first_name') is-invalid @enderror"
                                       value="{{ old('first_name', $user->first_name) }}" required>
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" id="last_name" name="last_name"
                                       class="form-control @error('last_name') is-invalid @enderror"
                                       value="{{ old('last_name', $user->last_name) }}" required>
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone (optional)</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone', $user->phone) }}" placeholder="+254...">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                            <option value="">Select a role...</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}"
                                        {{ old('role', $user->roles->first()?->name) === $role->name ? 'selected' : '' }}>
                                    {{ str_replace('_', ' ', ucfirst($role->name)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-4">

                    <p class="text-muted mb-3">Leave password fields empty to keep the current password.</p>

                    <div class="mb-3">
                        <label class="form-label">New Password (optional)</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-save me-1"></i> Update User
                        </button>
                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        @if($user->id !== auth()->id())
        <div class="card app-card mt-4 border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">Danger Zone</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Permanently delete this user account. This action cannot be undone.</p>
                <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                      onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fa-solid fa-trash me-1"></i> Delete User
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
