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

        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3 align-items-end mb-4">
            <div class="col-12 col-lg-4">
                <label for="q" class="form-label">Search</label>
                <input id="q" name="q" type="search" class="form-control" value="{{ $filters['q'] ?? '' }}" placeholder="Name/email prefix or WhatsApp number">
            </div>
            <div class="col-6 col-lg-2">
                <label for="role" class="form-label">Role</label>
                <select id="role" name="role" class="form-select">
                    <option value="">All roles</option>
                    <option value="user" @selected(($filters['role'] ?? '') === 'user')>User</option>
                    <option value="admin" @selected(($filters['role'] ?? '') === 'admin')>Admin</option>
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">All statuses</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                    <option value="suspended" @selected(($filters['status'] ?? '') === 'suspended')>Suspended</option>
                </select>
            </div>
            <div class="col-6 col-lg-1">
                <label for="from" class="form-label">From</label>
                <input id="from" name="from" type="date" class="form-control" value="{{ $filters['from'] ?? '' }}">
            </div>
            <div class="col-6 col-lg-1">
                <label for="to" class="form-label">To</label>
                <input id="to" name="to" type="date" class="form-control" value="{{ $filters['to'] ?? '' }}">
            </div>
            <div class="col-6 col-lg-1">
                <label for="sort" class="form-label">Sort</label>
                <select id="sort" name="sort" class="form-select">
                    <option value="id" @selected(($filters['sort'] ?? 'id') === 'id')>ID</option>
                    <option value="name" @selected(($filters['sort'] ?? '') === 'name')>Name</option>
                    <option value="email" @selected(($filters['sort'] ?? '') === 'email')>Email</option>
                    <option value="role" @selected(($filters['sort'] ?? '') === 'role')>Role</option>
                    <option value="status" @selected(($filters['sort'] ?? '') === 'status')>Status</option>
                    <option value="created_at" @selected(($filters['sort'] ?? '') === 'created_at')>Registered</option>
                </select>
            </div>
            <div class="col-6 col-lg-1">
                <label for="direction" class="form-label">Order</label>
                <select id="direction" name="direction" class="form-select">
                    <option value="desc" @selected(($filters['direction'] ?? 'desc') === 'desc')>Desc</option>
                    <option value="asc" @selected(($filters['direction'] ?? '') === 'asc')>Asc</option>
                </select>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Apply filters</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
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
                            <td>{{ ucfirst($listedUser->status) }}</td>
                            <td>{{ $listedUser->created_at?->format('Y-m-d') }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.users.show', $listedUser->id) }}" class="btn btn-sm btn-outline-primary">Details</a>
                                @unless ($listedUser->isAdmin())
                                    <form method="POST" action="{{ route('admin.users.status', $listedUser->id) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $listedUser->status === 'active' ? 'suspended' : 'active' }}">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">{{ $listedUser->status === 'active' ? 'Suspend' : 'Activate' }}</button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">No users match these filters.</td>
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
