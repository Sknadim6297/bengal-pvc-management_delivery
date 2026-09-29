@extends('user-panel.layout.app')

@section('title', 'Order Details | India PVC')

@section('content')
    <div class="content-card">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="welcome-title mb-1">Order {{ $order->order_number }}</h1>
                <p class="text-muted mb-0">Placed {{ $order->created_at?->format('Y-m-d H:i') }}</p>
            </div>
            <a href="{{ route('user.order-history') }}" class="btn btn-outline-primary">Order history</a>
        </div>

        @if (session('statusMessage'))
            <div class="alert alert-success" role="status">{{ session('statusMessage') }}</div>
        @endif

        <div class="row g-4">
            <div class="col-12 col-xl-6">
                <h2 class="h5 fw-bold">Order</h2>
                <dl class="row">
                    <dt class="col-sm-5">Service</dt><dd class="col-sm-7">{{ $order->service_type === 'pvc-card' ? 'PVC Card Print' : ucfirst(str_replace('-', ' ', $order->service_type)) }}</dd>
                    <dt class="col-sm-5">{{ $order->service_type === 'photo-print' ? 'Photo size' : 'Print quality' }}</dt><dd class="col-sm-7">{{ $order->items->first()?->quality_name ?? '—' }}</dd>
                    @if ($order->service_type === 'photo-print')
                        <dt class="col-sm-5">Delivery district</dt><dd class="col-sm-7">{{ data_get($order->delivery_address, 'district', '—') }}</dd>
                    @endif
                    <dt class="col-sm-5">Quantity</dt><dd class="col-sm-7">{{ $order->quantity }}</dd>
                    <dt class="col-sm-5">Status</dt><dd class="col-sm-7">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</dd>
                    <dt class="col-sm-5">Payment status</dt><dd class="col-sm-7">{{ ucfirst($order->payment_status) }}</dd>
                    @if ($order->service_type === 'photo-print')
                        @foreach (['village_area' => 'Area', 'landmark' => 'Landmark', 'post_office' => 'Post office', 'police_station' => 'Police station', 'pincode' => 'PIN code'] as $addressKey => $addressLabel)
                            @if (filled(data_get($order->delivery_address, $addressKey)))
                                <dt class="col-sm-5">{{ $addressLabel }}</dt><dd class="col-sm-7">{{ data_get($order->delivery_address, $addressKey) }}</dd>
                            @endif
                        @endforeach
                    @endif
                </dl>
            </div>
            <div class="col-12 col-xl-6">
                <h2 class="h5 fw-bold">Pricing</h2>
                <dl class="row">
                    @if ($order->service_type === 'photo-print')
                        <dt class="col-sm-7">Rate per photo</dt><dd class="col-sm-5">₹{{ number_format((float) ($order->items->first()?->unit_price ?? 0), 2) }}</dd>
                    @endif
                    <dt class="col-sm-7">Subtotal</dt><dd class="col-sm-5">₹{{ number_format((float) $order->subtotal, 2) }}</dd>
                    @unless ($order->service_type === 'photo-print')
                        <dt class="col-sm-7">Bulk discount</dt><dd class="col-sm-5">−₹{{ number_format((float) $order->discount_amount, 2) }}</dd>
                        <dt class="col-sm-7">Card cover</dt><dd class="col-sm-5">₹{{ number_format((float) $order->cover_amount, 2) }}</dd>
                    @endunless
                    <dt class="col-sm-7">Shipping</dt><dd class="col-sm-5">₹{{ number_format((float) $order->shipping_amount, 2) }}</dd>
                    @unless ($order->service_type === 'photo-print')
                        <dt class="col-sm-7">Coupon discount</dt><dd class="col-sm-5">−₹{{ number_format((float) $order->coupon_discount, 2) }}</dd>
                    @endunless
                    <dt class="col-sm-7 fw-bold">Total</dt><dd class="col-sm-5 fw-bold">₹{{ number_format((float) $order->total_amount, 2) }}</dd>
                </dl>
            </div>
        </div>

        <section class="mt-4">
            <h2 class="h5 fw-bold">Files and Drive links</h2>
            <ul class="list-group">
                @forelse ($order->files as $file)
                    <li class="list-group-item d-flex flex-column flex-sm-row justify-content-between gap-2">
                        @if (in_array($file->type, ['pdf', 'photo'], true))
                            <span>{{ $file->original_name }} <small class="text-muted">{{ $file->file_size ? number_format($file->file_size / 1024, 1).' KB' : '' }}</small></span>
                            @if ($file->status !== 'missing')
                                <a href="{{ route('user.orders.files.download', [$order->id, $file->id]) }}">Download {{ $file->type === 'photo' ? 'photo' : 'PDF' }}</a>
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

        <section class="mt-4">
            <h2 class="h5 fw-bold">Payment</h2>
            @forelse ($order->payments as $payment)
                <p class="mb-1">{{ ucfirst($payment->status) }} · ₹{{ number_format((float) $payment->amount, 2) }}</p>
                @if ($payment->transaction_id)<p class="small text-muted">Reference: {{ $payment->transaction_id }}</p>@endif
            @empty
                <p class="text-muted">No payment record.</p>
            @endforelse
        </section>

        <section class="mt-4">
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