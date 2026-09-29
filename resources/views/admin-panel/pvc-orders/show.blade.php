@extends('admin-panel.layout.app')

@section('title', $order->order_number.' | PVC Orders')

@section('content')
    <div class="content-card">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="welcome-title mb-1">{{ $order->order_number }}</h1>
                <p class="text-muted mb-0">Placed {{ $order->created_at?->format('Y-m-d H:i') }}</p>
            </div>
            <a href="{{ route('admin.pvc-orders.index') }}" class="admin-link-button">All PVC orders</a>
        </div>

        @if (session('statusMessage'))
            <div class="alert alert-success" role="status">{{ session('statusMessage') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
        @endif

        <div class="row g-4">
            <div class="col-12 col-xl-6">
                <h2 class="h5 fw-bold">Customer</h2>
                <dl class="row">
                    <dt class="col-sm-4">Name</dt><dd class="col-sm-8">{{ $order->user?->name ?? 'Deleted user' }}</dd>
                    <dt class="col-sm-4">User ID</dt><dd class="col-sm-8">{{ $order->user_id }}</dd>
                    <dt class="col-sm-4">WhatsApp</dt><dd class="col-sm-8">{{ $order->user?->whatsapp_number ?? '—' }}</dd>
                    <dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ $order->user?->email ?? '—' }}</dd>
                    <dt class="col-sm-4">District</dt><dd class="col-sm-8">{{ $order->user?->district ?? '—' }}</dd>
                </dl>
            </div>
            <div class="col-12 col-xl-6">
                <h2 class="h5 fw-bold">Order</h2>
                <dl class="row">
                    <dt class="col-sm-5">Service</dt><dd class="col-sm-7">PVC Card Print</dd>
                    <dt class="col-sm-5">Quantity</dt><dd class="col-sm-7">{{ $order->quantity }}</dd>
                    <dt class="col-sm-5">Quality</dt><dd class="col-sm-7">{{ $order->items->first()?->quality_name ?? '—' }}</dd>
                    <dt class="col-sm-5">Order status</dt><dd class="col-sm-7">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</dd>
                    <dt class="col-sm-5">Payment status</dt><dd class="col-sm-7">{{ ucfirst($order->payment_status) }}</dd>
                </dl>
            </div>
        </div>

        <section class="admin-data-section">
            <h2 class="h5 fw-bold">Files and Drive links</h2>
            <ul class="list-group">
                @forelse ($order->files as $file)
                    <li class="list-group-item d-flex flex-column flex-sm-row justify-content-between gap-2">
                        @if ($file->type === 'pdf')
                            <span>{{ $file->original_name }} <small class="text-muted">{{ $file->file_size ? number_format($file->file_size / 1024, 1).' KB' : '' }}</small></span>
                            @if ($file->status !== 'missing')
                                <a href="{{ route('admin.pvc-orders.files.download', [$order->id, $file->id]) }}">Download PDF</a>
                            @else
                                <span class="text-muted">File unavailable</span>
                            @endif
                        @else
                            <span>Google Drive</span><a href="{{ $file->drive_url }}" target="_blank" rel="noopener noreferrer">Open shared link</a>
                        @endif
                    </li>
                @empty
                    <li class="list-group-item text-muted">No files attached.</li>
                @endforelse
            </ul>
        </section>

        <section class="admin-data-section">
            <h2 class="h5 fw-bold">Pricing snapshot</h2>
            @foreach ($order->items as $item)
                <dl class="row">
                    <dt class="col-sm-7">Price per card</dt><dd class="col-sm-5">₹{{ number_format((float) $item->unit_price, 2) }}</dd>
                    <dt class="col-sm-7">Subtotal</dt><dd class="col-sm-5">₹{{ number_format((float) $order->subtotal, 2) }}</dd>
                    <dt class="col-sm-7">Bulk discount</dt><dd class="col-sm-5">−₹{{ number_format((float) $order->discount_amount, 2) }}</dd>
                    <dt class="col-sm-7">Card cover</dt><dd class="col-sm-5">₹{{ number_format((float) $order->cover_amount, 2) }}</dd>
                    <dt class="col-sm-7">Shipping</dt><dd class="col-sm-5">₹{{ number_format((float) $order->shipping_amount, 2) }}</dd>
                    <dt class="col-sm-7">Coupon discount</dt><dd class="col-sm-5">−₹{{ number_format((float) $order->coupon_discount, 2) }}</dd>
                    <dt class="col-sm-7 fw-bold">Total</dt><dd class="col-sm-5 fw-bold">₹{{ number_format((float) $order->total_amount, 2) }}</dd>
                </dl>
            @endforeach
        </section>

        <section class="admin-data-section">
            <div class="row g-4">
                <div class="col-12 col-lg-6">
                    <h2 class="h5 fw-bold">Payment records</h2>
                    @forelse ($order->payments as $payment)
                        <p class="mb-1">{{ ucfirst($payment->status) }} · ₹{{ number_format((float) $payment->amount, 2) }}</p>
                        @if ($payment->transaction_id)<p class="small text-muted mb-2">Reference: {{ $payment->transaction_id }}</p>@endif
                    @empty
                        <p class="text-muted mb-0">No payment record.</p>
                    @endforelse
                </div>
                <div class="col-12 col-lg-6">
                    <h2 class="h5 fw-bold">Update status</h2>
                    @if ($allowedStatuses)
                        <form method="POST" action="{{ route('admin.pvc-orders.status', $order->id) }}" class="d-flex flex-column gap-2">
                            @csrf
                            @method('PATCH')
                            <label for="status" class="form-label">Next status</label>
                            <select id="status" name="status" class="form-select" required>
                                @foreach ($allowedStatuses as $status)<option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>@endforeach
                            </select>
                            <label for="note" class="form-label">Note</label>
                            <textarea id="note" name="note" class="form-control" rows="2" maxlength="1000"></textarea>
                            <button type="submit" class="admin-search-button align-self-start">Update status</button>
                        </form>
                    @else
                        <p class="text-muted mb-0">This order is in a final status.</p>
                    @endif
                </div>
            </div>
        </section>

        <section class="admin-data-section">
            <h2 class="h5 fw-bold">Status timeline</h2>
            @forelse ($order->statusHistory as $entry)
                <div class="d-flex gap-3 border-bottom py-3">
                    <i class="bi bi-circle-fill text-primary mt-1" aria-hidden="true"></i>
                    <div>
                        <strong>{{ $entry->old_status ? ucfirst(str_replace('_', ' ', $entry->old_status)).' → ' : '' }}{{ ucfirst(str_replace('_', ' ', $entry->new_status)) }}</strong>
                        <div class="small text-muted">{{ $entry->created_at?->format('Y-m-d H:i') }} · {{ $entry->changedBy?->name ?? 'System' }}</div>
                        @if ($entry->note)<p class="mb-0">{{ $entry->note }}</p>@endif
                    </div>
                </div>
            @empty
                <p class="text-muted mb-0">No status updates recorded.</p>
            @endforelse
        </section>
    </div>
@endsection