@extends('admin-panel.layout.app')

@section('title', 'Admin Dashboard | India PVC')

@section('content')
    <div class="content-card">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="welcome-title mb-1">Welcome, <span>{{ $user->name }}!</span></h1>
                <p class="text-muted mb-0">Administrative overview</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-primary">Manage users</a>
        </div>

        <div class="row g-3">
            <div class="col-md-6 col-xl-4">
                <div class="stat-card">
                    <span class="stat-label">Total Accounts</span>
                    <strong>{{ number_format((int) $statistics->total_accounts) }}</strong>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="stat-card">
                    <span class="stat-label">Registered Users</span>
                    <strong>{{ number_format((int) $statistics->registered_users) }}</strong>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="stat-card">
                    <span class="stat-label">Active Users</span>
                    <strong>{{ number_format((int) $statistics->active_users) }}</strong>
                </div>
            </div>
        </div>
    </div>
@endsection
