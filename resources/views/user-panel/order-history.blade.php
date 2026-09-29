@extends('user-panel.layout.app')

@section('content')


                    <div class="order-history-card">

                        <!-- Header -->
                        <div class="d-flex flex-column flex-md-row
                justify-content-between align-items-md-center
                gap-3 mb-4">

                            <div>
                                <h3 class="fw-bold mb-1">
                                    <i class="bi bi-clock-history text-primary me-2"></i>
                                    Order History
                                </h3>

                                <p class="text-muted mb-0">
                                    View your PVC Card Print orders and their status
                                </p>
                            </div>

                        </div>


                        <form method="GET" action="{{ route('user.order-history') }}" class="filter-box mb-4">
                            <div class="row g-3 align-items-end">
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold" for="fromDate">From</label>
                                    <input type="date" class="form-control" id="fromDate" name="from" value="{{ $filters['from'] ?? '' }}">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold" for="toDate">To</label>
                                    <input type="date" class="form-control" id="toDate" name="to" value="{{ $filters['to'] ?? '' }}">
                                </div>
                                <div class="col-12 col-md-4">
                                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-2"></i>View Data</button>
                                </div>
                            </div>
                        </form>

                        <!-- Order Table -->
                        <div class="table-responsive">

                            <table class="table order-table align-middle">

                                <thead>
                                    <tr>
                                        <th>Order Number</th>
                                        <th>Service</th>
                                        <th>Quantity</th>
                                        <th>Quality</th>
                                        <th>Total</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>View</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse ($orders as $order)
                                        <tr>
                                            <td>{{ $order->order_number }}</td>
                                            <td>{{ $order->service_type === 'pvc-card' ? 'PVC Card Print' : ucfirst(str_replace('-', ' ', $order->service_type)) }}</td>
                                            <td>{{ $order->quantity }}</td>
                                            <td>{{ $order->items->first()?->quality_name ?? '—' }}</td>
                                            <td>₹{{ number_format((float) $order->total_amount, 2) }}</td>
                                            <td>{{ ucfirst($order->payment_status) }}</td>
                                            <td>{{ ucfirst(str_replace('_', ' ', $order->status)) }}</td>
                                            <td>{{ $order->created_at?->format('Y-m-d') }}</td>
                                            <td><a href="{{ route('user.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-5">
                                                <div class="empty-order">
                                                    <i class="bi bi-calendar2-x"></i>
                                                    <h6 class="fw-bold mt-3">No Orders Found</h6>
                                                    <p class="text-muted mb-0">Your PVC Card Print orders will appear here.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>

                            </table>

                        </div>

                        <div class="mt-3">{{ $orders->links() }}</div>
                    </div>

                
@endsection
