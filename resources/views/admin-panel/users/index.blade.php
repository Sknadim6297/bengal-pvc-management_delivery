@extends('admin-panel.layout.app')

@section('title', 'Registered Users | India PVC')

@section('content')
    <div class="content-card">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="welcome-title mb-1">Registered Users</h1>
                <p class="text-muted mb-0">Search and manage user accounts</p>
            </div>
        </div>

        @if (session('statusMessage'))
            <div class="alert alert-success" role="status">{{ session('statusMessage') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="GET" action="{{ route('admin.users.index') }}" class="admin-search-form mb-4">
            <label for="q" class="visually-hidden">Search users</label>
            <div class="admin-search-input-wrap">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input id="q" name="q" type="search" class="form-control" value="{{ $filters['q'] ?? '' }}" placeholder="Search ID, name, email, WhatsApp or district">
            </div>
            <button type="submit" class="admin-search-button"><i class="bi bi-search" aria-hidden="true"></i> Search</button>
            <a href="{{ route('admin.users.index') }}" class="admin-clear-button">Clear</a>
        </form>

        <div class="admin-table-scroll">
            <table class="table admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">WhatsApp</th>
                        <th scope="col">District</th>
                        <th scope="col">Role</th>
                        <th scope="col">Status</th>
                        <th scope="col">Registered</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $listedUser)
                        <tr>
                            <td>{{ $listedUser->id }}</td>
                            <td>{{ $listedUser->name }}</td>
                            <td>{{ $listedUser->email }}</td>
                            <td>{{ $listedUser->whatsapp_number ?? '—' }}</td>
                            <td>{{ $listedUser->district ?? '—' }}</td>
                            <td>{{ ucfirst($listedUser->role) }}</td>
                            <td><span class="admin-status admin-status-{{ $listedUser->status }}">{{ ucfirst($listedUser->status) }}</span></td>
                            <td>{{ $listedUser->created_at?->format('Y-m-d') }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.users.show', $listedUser->id) }}" class="admin-link-button">Details</a>
                                @unless ($listedUser->isAdmin())
                                    <form method="POST" action="{{ route('admin.users.status', $listedUser->id) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $listedUser->status === 'active' ? 'suspended' : 'active' }}">
                                        <button type="submit" class="admin-link-button admin-status-action">{{ $listedUser->status === 'active' ? 'Suspend' : 'Activate' }}</button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9"><div class="admin-empty-state"><i class="bi bi-person-lines-fill" aria-hidden="true"></i><span>No users found</span></div></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <span class="text-muted small">{{ count($users->items()) }} users on this page</span>
            <div class="d-flex gap-2">
                @if ($users->previousPageUrl())
                    <a href="{{ $users->previousPageUrl() }}" class="btn btn-sm btn-outline-secondary">Previous</a>
                @endif
                @if ($users->nextPageUrl())
                    <a href="{{ $users->nextPageUrl() }}" class="btn btn-sm btn-outline-secondary">Next</a>
                @endif
            </div>
        </div>
    </div>
@endsection
