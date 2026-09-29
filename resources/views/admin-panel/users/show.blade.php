@extends('admin-panel.layout.app')

@section('title', 'User Details | India PVC')

@section('content')
    <div class="content-card">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="welcome-title mb-1">User Details</h1>
                <p class="text-muted mb-0">Account #{{ $user->id }}</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Back to users</a>
        </div>

        <dl class="row mb-0">
            <dt class="col-sm-3">Name</dt>
            <dd class="col-sm-9">{{ $user->name }}</dd>
            <dt class="col-sm-3">Email</dt>
            <dd class="col-sm-9">{{ $user->email }}</dd>
            <dt class="col-sm-3">WhatsApp</dt>
            <dd class="col-sm-9">{{ $user->whatsapp_number ?? '—' }}</dd>
            <dt class="col-sm-3">District</dt>
            <dd class="col-sm-9">{{ $user->district ?? '—' }}</dd>
            <dt class="col-sm-3">Role</dt>
            <dd class="col-sm-9">{{ ucfirst($user->role) }}</dd>
            <dt class="col-sm-3">Status</dt>
            <dd class="col-sm-9">{{ ucfirst($user->status) }}</dd>
            <dt class="col-sm-3">Email verified</dt>
            <dd class="col-sm-9">{{ $user->email_verified_at?->format('Y-m-d H:i') ?? 'Not verified' }}</dd>
            <dt class="col-sm-3">Registered</dt>
            <dd class="col-sm-9">{{ $user->created_at?->format('Y-m-d H:i') }}</dd>
            <dt class="col-sm-3">Last updated</dt>
            <dd class="col-sm-9">{{ $user->updated_at?->format('Y-m-d H:i') }}</dd>
        </dl>

        @unless ($user->isAdmin())
            <form method="POST" action="{{ route('admin.users.status', $user->id) }}" class="mt-4">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="{{ $user->status === 'active' ? 'suspended' : 'active' }}">
                <button type="submit" class="btn btn-outline-primary">{{ $user->status === 'active' ? 'Suspend account' : 'Activate account' }}</button>
            </form>
        @endunless
    </div>
@endsection
