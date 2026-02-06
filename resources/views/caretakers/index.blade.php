@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Caretakers</h1>
        @can('properties.manage_caretakers')
            <a href="{{ route('caretakers.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus me-1"></i> Add Caretaker
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
                            <th>Phone</th>
                            <th>Properties</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($caretakers as $caretaker)
                            <tr>
                                <td>{{ $caretaker->name }}</td>
                                <td>{{ $caretaker->email }}</td>
                                <td>{{ $caretaker->phone ?? 'N/A' }}</td>
                                <td>
                                    @foreach($caretaker->caretakerProperties as $property)
                                        <span class="badge bg-secondary">{{ $property->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <span class="badge bg-{{ $caretaker->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($caretaker->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('caretakers.show', $caretaker) }}" class="btn btn-sm btn-info">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    @can('properties.manage_caretakers')
                                        <a href="{{ route('caretakers.edit', $caretaker) }}" class="btn btn-sm btn-warning">
                                            <i class="fa-solid fa-edit"></i>
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No caretakers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $caretakers->links() }}
        </div>
    </div>
</div>
@endsection
