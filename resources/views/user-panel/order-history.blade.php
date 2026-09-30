@extends('user-panel.layout.app')

@section('content')
    @php
        $fromValue = $filters['from'] ?? old('from', '');
        $toValue = $filters['to'] ?? old('to', '');
        $filterError = $errors->first();
        $dateFiltersActive = (is_string($fromValue) && $fromValue !== '') || (is_string($toValue) && $toValue !== '');
    @endphp

    <div class="order-history-page">
        <header class="order-history-header mb-3">
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-clock-history text-primary me-2" aria-hidden="true"></i>Order History</h1>
            <p class="text-muted mb-0">View and track your PVC card print orders.</p>
        </header>

        @if ($filterError)
            <div class="toast-container position-fixed top-0 end-0 p-3" aria-live="polite" aria-atomic="true">
                <div class="toast align-items-center text-bg-danger border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                    <div class="d-flex">
                        <div class="toast-body">{{ $filterError }}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        @endif

        <section class="support-box order-history-filter mb-3">
            <form method="GET" action="{{ route('user.order-history') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-semibold mb-1" for="fromDate">From Date</label>
                    <input type="date" class="form-control" id="fromDate" name="from" value="{{ is_string($fromValue) ? $fromValue : '' }}">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-semibold mb-1" for="toDate">To Date</label>
                    <input type="date" class="form-control" id="toDate" name="to" value="{{ is_string($toValue) ? $toValue : '' }}">
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <button type="submit" class="btn btn-primary w-100 order-history-filter-button">
                        <i class="bi bi-funnel me-1" aria-hidden="true"></i>Filter
                    </button>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <a href="{{ route('user.order-history') }}" class="btn btn-outline-secondary w-100 order-history-filter-button">
                        <i class="bi bi-arrow-counterclockwise me-1" aria-hidden="true"></i>Reset
                    </a>
                </div>
            </form>
        </section>

        @if ($orders->isEmpty())
            <section class="support-box order-history-empty text-center">
                <i class="bi {{ $dateFiltersActive ? 'bi-calendar2-x' : 'bi-receipt' }}" aria-hidden="true"></i>
                <h2 class="h5 fw-bold mt-2 mb-1">No orders found</h2>
                <p class="text-muted small mb-0">
                    {{ $dateFiltersActive ? 'No orders found for the selected date range.' : 'Your PVC card print orders will appear here.' }}
                </p>
                @if ($dateFiltersActive)
                    <a href="{{ route('user.order-history') }}" class="btn btn-outline-primary btn-sm mt-3">
                        <i class="bi bi-arrow-counterclockwise me-1" aria-hidden="true"></i>Clear filters
                    </a>
                @endif
            </section>
        @else
            <section class="support-box order-history-table-panel p-0 mb-3 d-none d-xl-block">
                <div class="table-responsive">
                    <table class="table order-history-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th scope="col">Order</th>
                                <th scope="col">Service</th>
                                <th scope="col">Qty</th>
                                <th scope="col">Quality</th>
                                <th scope="col">Total</th>
                                <th scope="col">Payment</th>
                                <th scope="col">Status</th>
                                <th scope="col">Date</th>
                                <th scope="col" class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                @php
                                    $paymentStatus = strtolower((string) $order->payment_status);
                                @endphp
                                <tr>
                                    <td><a class="order-history-number" href="{{ route('user.orders.show', $order->id) }}">{{ $order->order_number }}</a></td>
                                    <td>{{ $order->service_type === 'pvc-card' ? 'PVC Card Print' : ucfirst(str_replace('-', ' ', $order->service_type)) }}</td>
                                    <td>{{ $order->quantity }}</td>
                                    <td>{{ $order->items->first()?->quality_name ?? '—' }}</td>
                                    <td class="fw-semibold">₹{{ number_format((float) $order->total_amount, 2) }}</td>
                                    <td>@include('user-panel.partials.payment-status-badge', ['status' => $paymentStatus])</td>
                                    <td>@include('user-panel.partials.order-status-badge', ['status' => $order->status])</td>
                                    <td class="text-nowrap">{{ $order->created_at?->format('d M Y') }}</td>
                                    <td>
                                        <div class="d-flex justify-content-end gap-1">
                                            <a href="{{ route('user.track-help', ['order_id' => $order->order_number]) }}" class="btn btn-sm btn-outline-secondary" aria-label="Track order {{ $order->order_number }}" title="Track order">
                                                <i class="bi bi-truck" aria-hidden="true"></i>
                                            </a>
                                            <a href="{{ route('user.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary" aria-label="View order {{ $order->order_number }}" title="View order">
                                                <i class="bi bi-eye" aria-hidden="true"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="order-history-mobile-list d-xl-none d-flex flex-column gap-2 mb-3">
                @foreach ($orders as $order)
                    @php
                        $paymentStatus = strtolower((string) $order->payment_status);
                    @endphp
                    <article class="support-box order-history-mobile-card">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="min-w-0">
                                <a class="order-history-number text-break" href="{{ route('user.orders.show', $order->id) }}">{{ $order->order_number }}</a>
                                <div class="small text-muted mt-1">{{ $order->service_type === 'pvc-card' ? 'PVC Card Print' : ucfirst(str_replace('-', ' ', $order->service_type)) }}</div>
                            </div>
                            @include('user-panel.partials.order-status-badge', ['status' => $order->status, 'class' => 'flex-shrink-0'])
                        </div>

                        <div class="order-history-mobile-meta row g-2 mt-2">
                            <div class="col-6">
                                <div class="small text-muted">Payment</div>
                                @include('user-panel.partials.payment-status-badge', ['status' => $paymentStatus, 'class' => 'mt-1'])
                            </div>
                            <div class="col-6">
                                <div class="small text-muted">Quantity</div>
                                <div class="fw-semibold">{{ $order->quantity }}</div>
                            </div>
                            <div class="col-6">
                                <div class="small text-muted">Quality</div>
                                <div class="fw-semibold">{{ $order->items->first()?->quality_name ?? '—' }}</div>
                            </div>
                            <div class="col-6">
                                <div class="small text-muted">Date</div>
                                <div class="fw-semibold">{{ $order->created_at?->format('d M Y') }}</div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center gap-2 mt-3 pt-3 border-top">
                            <span class="fw-bold">₹{{ number_format((float) $order->total_amount, 2) }}</span>
                            <div class="d-flex gap-2">
                                <a href="{{ route('user.track-help', ['order_id' => $order->order_number]) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-truck me-1" aria-hidden="true"></i>Track
                                </a>
                                <a href="{{ route('user.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1" aria-hidden="true"></i>View
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($orders->hasPages())
                <div class="order-history-pagination">{{ $orders->links('pagination::bootstrap-5') }}</div>
            @endif
        @endif
    </div>

    <style>
        .order-history-page .support-box {
            padding: 18px;
            border-radius: 14px;
        }
        .order-history-page .order-history-filter .form-control,
        .order-history-page .order-history-filter-button {
            min-height: 44px;
            border-radius: 9px;
        }
        .order-history-page .order-history-number {
            color: #3730a3;
            font-size: .875rem;
            font-weight: 700;
            text-decoration: none;
            overflow-wrap: anywhere;
        }
        .order-history-page .order-history-number:hover {
            color: #5146e5;
            text-decoration: underline;
        }
        .order-history-page .order-history-table-panel {
            overflow: hidden;
        }
        .order-history-page .order-history-table {
            font-size: .82rem;
        }
        .order-history-page .order-history-table thead th {
            padding: 12px 10px;
            background: #f7f8fc;
            color: #41486a;
            font-size: .72rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .order-history-page .order-history-table tbody td {
            padding: 12px 10px;
            border-color: #edf0f4;
        }
        .order-history-page .order-history-table tbody tr:hover {
            background: #fafbff;
        }
        .order-history-page .order-history-table .badge,
        .order-history-page .order-history-mobile-card .badge {
            font-size: .68rem;
            font-weight: 600;
        }
        .order-history-page .order-history-table .btn {
            width: 32px;
            height: 32px;
            display: inline-grid;
            place-items: center;
            padding: 0;
            border-radius: 8px;
        }
        .order-history-page .order-history-mobile-card {
            padding: 16px;
            transition: box-shadow .18s ease;
        }
        .order-history-page .order-history-mobile-card:hover {
            transform: none;
            box-shadow: 0 8px 25px rgba(0, 0, 0, .08);
        }
        .order-history-page .order-history-mobile-meta {
            font-size: .84rem;
        }
        .order-history-page .order-history-empty {
            padding: 28px 18px;
            text-align: center;
        }
        .order-history-page .order-history-empty > i {
            color: #5146e5;
            font-size: 1.8rem;
        }
        .order-history-page .order-history-pagination .pagination {
            margin-bottom: 0;
            flex-wrap: wrap;
        }
        @media (max-width: 575.98px) {
            .order-history-page .support-box {
                padding: 15px;
            }
            .order-history-page .order-history-filter-button {
                width: 100%;
            }
            .order-history-page .order-history-pagination .page-link {
                padding: .35rem .6rem;
            }
        }
    </style>

    @push('scripts')
        @if ($filterError)
            <script>
                document.querySelectorAll('.toast').forEach(function (toastElement) {
                    new bootstrap.Toast(toastElement).show();
                });
            </script>
        @endif
    @endpush
@endsection
