@extends('admin-panel.layout.app')

@section('title', 'Admin Dashboard | India PVC')

@section('content')
    <div class="content-card admin-dashboard">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
            <div>
                <h1 class="welcome-title mb-1">Admin Dashboard</h1>
                <p class="text-muted mb-0">Welcome, {{ $user->name }}</p>
            </div>
        </div>

        <div class="row g-3 mb-4">
            @foreach ([
                ['Total Users', $statistics->total_users, 'bi-people-fill', 'users'],
                ['Active Users', $statistics->active_users, 'bi-person-check-fill', 'active'],
                ['Suspended Users', $statistics->suspended_users, 'bi-person-fill-slash', 'suspended'],
                ['Total PVC Orders', $statistics->total_pvc_orders, 'bi-credit-card-2-front-fill', 'orders'],
                ['Processing Orders', $statistics->processing_orders, 'bi-gear-wide-connected', 'processing'],
                ['Delivered Orders', $statistics->delivered_orders, 'bi-check-circle-fill', 'delivered'],
                ['Failed Orders', $statistics->failed_orders, 'bi-exclamation-triangle-fill', 'failed'],
                ['Total Photo Orders', $statistics->total_photo_orders, 'bi-image-fill', 'photos'],
            ] as [$label, $value, $icon, $tone])
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stat-card admin-stat-card admin-stat-{{ $tone }}">
                        <div>
                            <div class="stat-title">{{ $label }}</div>
                            <div class="stat-number">{{ number_format((int) $value) }}</div>
                        </div>
                        <div class="stat-icon"><i class="bi {{ $icon }}" aria-hidden="true"></i></div>
                    </div>
                </div>
            @endforeach
        </div>

        <section class="admin-data-section" aria-labelledby="recent-orders-heading">
            <div class="admin-section-heading">
                <div>
                    <h2 id="recent-orders-heading">Recent Orders</h2>
                    <p class="text-muted mb-0">Latest PVC and photo orders</p>
                </div>
            </div>
            <div class="admin-table-scroll">
                <table class="table admin-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Order ID</th>
                            <th scope="col">Customer</th>
                            <th scope="col">Order Type</th>
                            <th scope="col">Status</th>
                            <th scope="col">Date</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6">
                                <div class="admin-empty-state">
                                    <i class="bi bi-inbox" aria-hidden="true"></i>
                                    <span>No orders found</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-data-section" aria-labelledby="recent-users-heading">
            <div class="admin-section-heading">
                <div>
                    <h2 id="recent-users-heading">Recent Users</h2>
                    <p class="text-muted mb-0">Latest registered customer accounts</p>
                </div>
                <a href="{{ route('admin.users.index') }}" class="admin-link-button">
                    <i class="bi bi-people" aria-hidden="true"></i> All users
                </a>
            </div>
            <div class="admin-table-scroll">
                <table class="table admin-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">User ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">WhatsApp</th>
                            <th scope="col">Status</th>
                            <th scope="col">Registered</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentUsers as $recentUser)
                            <tr>
                                <td>{{ $recentUser->id }}</td>
                                <td>{{ $recentUser->name }}</td>
                                <td>{{ $recentUser->email }}</td>
                                <td>{{ $recentUser->whatsapp_number ?? '—' }}</td>
                                <td><span class="admin-status admin-status-{{ $recentUser->status }}">{{ ucfirst($recentUser->status) }}</span></td>
                                <td>{{ $recentUser->created_at?->format('Y-m-d') }}</td>
                                <td>
                                    <a href="{{ route('admin.users.show', $recentUser->id) }}" class="admin-link-button">Details</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="admin-empty-state">
                                        <i class="bi bi-person-lines-fill" aria-hidden="true"></i>
                                        <span>No users found</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection