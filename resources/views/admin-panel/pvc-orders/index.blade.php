@extends('admin-panel.layout.app')

@section('title', 'PVC Orders | India PVC Admin')

@section('content')
    <div class="content-card">
        <div class="mb-4">
            <h1 class="welcome-title mb-1">PVC Orders</h1>
            <p class="text-muted mb-0">Search and manage customer PVC Card Print orders</p>
        </div>

        <form method="GET" action="{{ route('admin.pvc-orders.index') }}" class="row g-3 align-items-end mb-4">
            <div class="col-12 col-xl-4">
                <label for="q" class="form-label">Search</label>
                <input id="q" name="q" type="search" class="form-control" value="{{ $filters['q'] ?? '' }}" placeholder="Order number, name, email or WhatsApp">
            </div>
            <div class="col-6 col-xl-2">
                <label for="status" class="form-label">Order status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">All statuses</option>
                    @foreach (\App\Models\Order::STATUSES as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-xl-2">
                <label for="payment_status" class="form-label">Payment</label>
                <select id="payment_status" name="payment_status" class="form-select">
                    <option value="">All payments</option>
                    @foreach (['pending', 'paid', 'failed', 'refunded'] as $paymentStatus)
                        <option value="{{ $paymentStatus }}" @selected(($filters['payment_status'] ?? '') === $paymentStatus)>{{ ucfirst($paymentStatus) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-xl-1">
                <label for="quality" class="form-label">Quality</label>
                <select id="quality" name="quality" class="form-select">
                    <option value="">All</option>
                    <option value="normal" @selected(($filters['quality'] ?? '') === 'normal')>Normal</option>
                    <option value="premium" @selected(($filters['quality'] ?? '') === 'premium')>Premium</option>
                </select>
            </div>
            <div class="col-6 col-xl-1">
                <label for="from" class="form-label">From</label>
                <input id="from" name="from" type="date" class="form-control" value="{{ $filters['from'] ?? '' }}">
            </div>
            <div class="col-6 col-xl-1">
                <label for="to" class="form-label">To</label>
                <input id="to" name="to" type="date" class="form-control" value="{{ $filters['to'] ?? '' }}">
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="admin-search-button"><i class="bi bi-search" aria-hidden="true"></i> Search</button>
                <a href="{{ route('admin.pvc-orders.index') }}" class="admin-clear-button">Clear</a>
            </div>
        </form>

        <div class="admin-table-scroll">
            <table class="table admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Order Number</th><th>Customer</th><th>WhatsApp</th><th>Email</th><th>Quantity</th><th>Quality</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th><th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->user?->name ?? 'Deleted user' }}</td>
                            <td>{{ $order->user?->whatsapp_number ?? '—' }}</td>
                            <td>{{ $order->user?->email ?? '—' }}</td>
                            <td>{{ $order->quantity }}</td>
                            <td>{{ $order->items->first()?->quality_name ?? '—' }}</td>
                            <td>₹{{ number_format((float) $order->total_amount, 2) }}</td>
                            <td>{{ ucfirst($order->payment_status) }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $order->status)) }}</td>
                            <td>{{ $order->created_at?->format('Y-m-d') }}</td>
                            <td><a href="{{ route('admin.pvc-orders.show', $order->id) }}" class="admin-link-button">Details</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="11"><div class="admin-empty-state"><i class="bi bi-inbox" aria-hidden="true"></i><span>No orders found</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $orders->links() }}</div>
    </div>
@endsection