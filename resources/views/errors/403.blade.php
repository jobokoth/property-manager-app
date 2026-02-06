@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Access Denied</h4>
                </div>
                <div class="card-body">
                    <p>You don't have permission to access this page.</p>
                    <a href="{{ url()->previous() ?: route('dashboard') }}" class="btn btn-primary">Go Back</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection