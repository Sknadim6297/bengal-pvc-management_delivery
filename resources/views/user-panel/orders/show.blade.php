@extends('user-panel.layout.app')

@section('title', 'Order Details | ' . $generalSettings->application_name)

@section('content')
    @php
        $whatsappUrl = $generalSettings->whatsappSupportUrl('Hello, I need help with Order '.$order->order_number.'.');
        $helplineUrl = $generalSettings->helplineTelUrl();
        $paymentStatus = strtolower((string) $order->payment_status);
    @endphp

    <div class="order-details-page">
        <header class="order-details-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
            <div class="min-w-0">
                <a href="{{ route('user.order-history') }}" class="order-details-back small text-decoration-none">
                    <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Order History
                </a>
                <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                    <h1 class="h4 fw-bold mb-0 order-details-number">Order #{{ $order->order_number }}</h1>
                    @include('user-panel.partials.order-status-badge', ['status' => $order->status])
                </div>
                <p class="small text-muted mb-0 mt-1">Placed on <time datetime="{{ $order->created_at?->toIso8601String() }}">{{ $order->created_at?->format('d M Y \a\t h:i A') }}</time></p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('user.track-help', ['order_id' => $order->order_number]) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-truck me-1" aria-hidden="true"></i>Track Order
                </a>
                <a href="{{ route('user.order-history') }}" class="btn btn-outline-secondary btn-sm">All Orders</a>
            </div>
        </header>

        @if (session('statusMessage'))
            <div class="alert alert-success" role="status">{{ session('statusMessage') }}</div>
        @endif

        <div class="row g-3 align-items-start">
            <div class="col-12 col-xl-8 order-details-main">
                <section class="support-box order-details-section">
                    <h2 class="h6 fw-bold mb-3"><i class="bi bi-box-seam text-primary me-2" aria-hidden="true"></i>Order Information</h2>
                    <dl class="order-information-grid mb-0">
                        <div class="order-information-item">
                            <dt>Service</dt>
                            <dd>{{ $order->service_type === 'pvc-card' ? 'PVC Card Print' : ucfirst(str_replace('-', ' ', $order->service_type)) }}</dd>
                        </div>
                        <div class="order-information-item">
                            <dt>{{ $order->service_type === 'photo-print' ? 'Photo size' : 'Print quality' }}</dt>
                            <dd>{{ $order->items->first()?->quality_name ?? '—' }}</dd>
                        </div>
                        <div class="order-information-item">
                            <dt>Quantity</dt>
                            <dd>{{ $order->quantity }}</dd>
                        </div>
                        <div class="order-information-item">
                            <dt>Payment</dt>
                            <dd>@include('user-panel.partials.payment-status-badge', ['status' => $order->payment_status])</dd>
                        </div>
                        @if ($order->service_type === 'photo-print')
                            <div class="order-information-item">
                                <dt>Delivery district</dt>
                                <dd>{{ data_get($order->delivery_address, 'district', '—') }}</dd>
                            </div>
                        @endif
                    </dl>

                    @if ($order->service_type === 'photo-print')
                        @php($deliveryAddressLabels = ['village_area' => 'Area', 'landmark' => 'Landmark', 'post_office' => 'Post office', 'police_station' => 'Police station', 'pincode' => 'PIN code'])
                        @if (collect($deliveryAddressLabels)->contains(fn ($label, $key) => filled(data_get($order->delivery_address, $key))))
                            <div class="order-delivery-address mt-3 pt-3 border-top">
                                <h3 class="small fw-semibold mb-2">Delivery address</h3>
                                <dl class="order-information-grid mb-0">
                                    @foreach ($deliveryAddressLabels as $addressKey => $addressLabel)
                                        @if (filled(data_get($order->delivery_address, $addressKey)))
                                            <div class="order-information-item">
                                                <dt>{{ $addressLabel }}</dt>
                                                <dd>{{ data_get($order->delivery_address, $addressKey) }}</dd>
                                            </div>
                                        @endif
                                    @endforeach
                                </dl>
                            </div>
                        @endif
                    @endif
                </section>

                <section class="support-box order-details-section">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                        <h2 class="h6 fw-bold mb-0"><i class="bi bi-paperclip text-primary me-2" aria-hidden="true"></i>Files &amp; Documents</h2>
                        <span class="small text-muted">{{ $order->files->count() }} {{ $order->files->count() === 1 ? 'file' : 'files' }}</span>
                    </div>
                    <div class="order-document-list">
                        @forelse ($order->files as $file)
                            <div class="order-document-row">
                                <span class="order-document-icon" aria-hidden="true">
                                    <i class="bi {{ $file->type === 'pdf' ? 'bi-file-earmark-pdf' : ($file->type === 'photo' ? 'bi-file-earmark-image' : 'bi-link-45deg') }}"></i>
                                </span>
                                <div class="order-document-info">
                                    <div class="order-document-name">{{ $file->original_name ?: ($file->type === 'drive' ? 'Google Drive link' : ucfirst($file->type)) }}</div>
                                    <div class="small text-muted">
                                        @if ($file->type === 'pdf')
                                            PDF
                                        @elseif ($file->type === 'photo')
                                            Photo
                                        @else
                                            Google Drive
                                        @endif
                                        @if ($file->file_size)
                                            · {{ number_format($file->file_size / 1024, 1) }} KB
                                        @endif
                                    </div>
                                </div>
                                <div class="order-document-action">
                                    @if (in_array($file->type, ['pdf', 'photo'], true))
                                        @if ($file->status !== 'missing' && filled($file->storage_path))
                                            <a href="{{ route('user.orders.files.download', [$order->id, $file->id]) }}" class="btn btn-sm btn-outline-primary" aria-label="Download {{ $file->type === 'photo' ? 'photo' : 'PDF' }}: {{ $file->original_name }}">
                                                <i class="bi bi-download me-sm-1" aria-hidden="true"></i><span class="d-none d-sm-inline">Download</span>
                                            </a>
                                        @else
                                            <span class="small text-muted">Unavailable</span>
                                        @endif
                                    @elseif ($file->drive_url)
                                        <a href="{{ $file->drive_url }}" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener noreferrer">
                                            <i class="bi bi-box-arrow-up-right me-sm-1" aria-hidden="true"></i><span class="d-none d-sm-inline">Open Link</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="order-details-empty small text-muted">
                                <i class="bi bi-file-earmark me-2" aria-hidden="true"></i>No documents attached to this order.
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="support-box order-details-section">
                    <h2 class="h6 fw-bold mb-3"><i class="bi bi-clock-history text-primary me-2" aria-hidden="true"></i>Status History</h2>
                    <div class="order-activity-list">
                        @forelse ($order->statusHistory as $entry)
                            <article class="order-activity-item {{ $loop->last ? 'is-latest' : '' }}">
                                <span class="order-activity-marker" aria-hidden="true">
                                    <i class="bi {{ $loop->last ? 'bi-record-circle-fill' : 'bi-check-lg' }}"></i>
                                </span>
                                <div class="order-activity-content">
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        @if ($entry->old_status)
                                            <span class="small fw-semibold">@include('user-panel.partials.order-status-badge', ['status' => $entry->old_status, 'labelOnly' => true]) <i class="bi bi-arrow-right mx-1" aria-hidden="true"></i> @include('user-panel.partials.order-status-badge', ['status' => $entry->new_status, 'labelOnly' => true])</span>
                                        @else
                                            <span class="small fw-semibold">{{ $entry->note ?: '' }}</span>
                                            @if (! $entry->note)
                                                @include('user-panel.partials.order-status-badge', ['status' => $entry->new_status])
                                            @endif
                                        @endif
                                    </div>
                                    <div class="small text-muted mt-1">
                                        {{ $entry->created_at?->format('d M Y, h:i A') }}
                                        @if ($entry->changedBy?->name)
                                            <span class="mx-1">·</span>Updated by {{ $entry->changedBy->name }}
                                        @endif
                                    </div>
                                    @if ($entry->old_status && filled($entry->note))
                                        <p class="small text-muted mb-0 mt-1">{{ $entry->note }}</p>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="order-details-empty small text-muted">
                                <i class="bi bi-clock me-2" aria-hidden="true"></i>No status history has been recorded for this order.
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>

            <aside class="col-12 col-xl-4 order-details-summary">
                <section class="support-box order-details-section order-pricing-card">
                    <h2 class="h6 fw-bold mb-3"><i class="bi bi-receipt text-primary me-2" aria-hidden="true"></i>Order Summary</h2>
                    @if ($order->service_type === 'photo-print')
                        <div class="order-price-row">
                            <span>Rate per photo</span>
                            <span>₹{{ number_format((float) ($order->items->first()?->unit_price ?? 0), 2) }}</span>
                        </div>
                    @endif
                    <div class="order-price-row">
                        <span>Subtotal</span>
                        <span>₹{{ number_format((float) $order->subtotal, 2) }}</span>
                    </div>
                    @unless ($order->service_type === 'photo-print')
                        @if ((float) $order->discount_amount > 0)
                            <div class="order-price-row">
                                <span>Bulk discount</span>
                                <span>−₹{{ number_format((float) $order->discount_amount, 2) }}</span>
                            </div>
                        @endif
                        @if ((float) $order->cover_amount > 0)
                            <div class="order-price-row">
                                <span>Card cover</span>
                                <span>₹{{ number_format((float) $order->cover_amount, 2) }}</span>
                            </div>
                        @endif
                    @endunless
                    <div class="order-price-row">
                        <span>Shipping</span>
                        <span>₹{{ number_format((float) $order->shipping_amount, 2) }}</span>
                    </div>
                    @unless ($order->service_type === 'photo-print')
                        @if ((float) $order->coupon_discount > 0)
                            <div class="order-price-row">
                                <span>Coupon discount</span>
                                <span>−₹{{ number_format((float) $order->coupon_discount, 2) }}</span>
                            </div>
                        @endif
                    @endunless
                    <div class="order-price-total d-flex justify-content-between align-items-center gap-3 mt-3 pt-3 border-top">
                        <span>Total</span>
                        <strong>₹{{ number_format((float) $order->total_amount, 2) }}</strong>
                    </div>
                </section>

                <section class="support-box order-details-section">
                    <h2 class="h6 fw-bold mb-3"><i class="bi bi-credit-card text-primary me-2" aria-hidden="true"></i>Payment</h2>
                    @forelse ($order->payments as $payment)
                        <div class="order-payment-entry {{ $loop->last ? '' : 'mb-3 pb-3 border-bottom' }}">
                            <div class="d-flex justify-content-between align-items-center gap-2">
                                @include('user-panel.partials.payment-status-badge', ['status' => $payment->status])
                                <strong>₹{{ number_format((float) $payment->amount, 2) }}</strong>
                            </div>
                            @if ($payment->gateway)
                                <div class="small text-muted mt-2">{{ ucfirst($payment->gateway) }}</div>
                            @endif
                            @if ($payment->transaction_id)
                                <div class="small text-muted mt-1">Reference: <span class="text-break">{{ $payment->transaction_id }}</span></div>
                            @endif
                            @if ($payment->paid_at)
                                <div class="small text-muted mt-1">{{ $payment->paid_at->format('d M Y, h:i A') }}</div>
                            @endif
                        </div>
                    @empty
                        <div class="order-payment-empty">
                            @include('user-panel.partials.payment-status-badge', ['status' => $order->payment_status])
                            <div class="fw-semibold mt-2">Payment pending</div>
                            <p class="small text-muted mb-0 mt-1">No payment has been recorded for this order yet.</p>
                        </div>
                    @endforelse
                </section>

                <section class="support-box order-details-help">
                    <div class="d-flex align-items-start gap-3">
                        <span class="order-details-help-icon" aria-hidden="true"><i class="bi bi-headset"></i></span>
                        <div>
                            <h2 class="h6 fw-bold mb-1">Need Help?</h2>
                            <p class="small text-muted mb-3">Questions about this order? Our support team can help.</p>
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="{{ route('user.track-help', ['order_id' => $order->order_number]) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-truck me-1" aria-hidden="true"></i>Track Order
                        </a>
                        @if ($whatsappUrl)
                            <a href="{{ $whatsappUrl }}" class="btn btn-success btn-sm" target="_blank" rel="noopener noreferrer">
                                <i class="bi bi-whatsapp me-1" aria-hidden="true"></i>Chat on WhatsApp
                            </a>
                        @endif
                        @if ($helplineUrl)
                            <a href="{{ $helplineUrl }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-telephone me-1" aria-hidden="true"></i>Call Support
                            </a>
                        @endif
                    </div>
                </section>
            </aside>
        </div>
    </div>

    <style>
        .order-details-page .order-details-header,
        .order-details-page .support-box {
            border-radius: 14px;
        }
        .order-details-page .order-details-back {
            color: #5146e5;
            font-weight: 600;
        }
        .order-details-page .order-details-number,
        .order-details-page .order-document-name {
            overflow-wrap: anywhere;
        }
        .order-details-page .order-details-main,
        .order-details-page .order-details-summary {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .order-details-page .order-details-section,
        .order-details-page .order-details-help {
            padding: 18px;
        }
        .order-details-page .order-information-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px 16px;
        }
        .order-details-page .order-information-item dt {
            margin-bottom: 3px;
            color: #737b8c;
            font-size: .75rem;
            font-weight: 500;
        }
        .order-details-page .order-information-item dd {
            margin: 0;
            color: #252b3a;
            font-size: .875rem;
            font-weight: 600;
            overflow-wrap: anywhere;
        }
        .order-details-page .order-document-list {
            display: flex;
            flex-direction: column;
        }
        .order-details-page .order-document-row {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            padding: 12px 0;
            border-bottom: 1px solid #edf0f4;
        }
        .order-details-page .order-document-row:first-child {
            padding-top: 0;
        }
        .order-details-page .order-document-row:last-child {
            padding-bottom: 0;
            border-bottom: 0;
        }
        .order-details-page .order-document-icon,
        .order-details-page .order-details-help-icon {
            display: grid;
            flex: 0 0 38px;
            width: 38px;
            height: 38px;
            place-items: center;
            border-radius: 10px;
            background: #eef1ff;
            color: #5146e5;
            font-size: 1.1rem;
        }
        .order-details-page .order-document-info {
            flex: 1 1 auto;
            min-width: 0;
        }
        .order-details-page .order-document-name {
            color: #252b3a;
            font-size: .875rem;
            font-weight: 600;
        }
        .order-details-page .order-document-action {
            flex: 0 0 auto;
        }
        .order-details-page .order-document-action .btn,
        .order-details-page .order-details-help .btn {
            border-radius: 8px;
        }
        .order-details-page .order-details-empty {
            padding: 12px;
            border-radius: 9px;
            background: #f7f8fc;
        }
        .order-details-page .order-activity-item {
            position: relative;
            min-height: 58px;
            padding: 0 0 16px 36px;
        }
        .order-details-page .order-activity-item:not(:last-child)::before {
            position: absolute;
            top: 24px;
            bottom: 0;
            left: 10px;
            width: 2px;
            background: #dfe3eb;
            content: '';
        }
        .order-details-page .order-activity-marker {
            position: absolute;
            top: 0;
            left: 0;
            display: grid;
            width: 22px;
            height: 22px;
            place-items: center;
            border-radius: 50%;
            background: #198754;
            color: #fff;
            font-size: .75rem;
        }
        .order-details-page .order-activity-item.is-latest .order-activity-marker {
            background: #5146e5;
        }
        .order-details-page .order-activity-content {
            min-width: 0;
        }
        .order-details-page .order-price-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 7px 0;
            color: #555d6e;
            font-size: .875rem;
        }
        .order-details-page .order-price-row span:last-child {
            color: #252b3a;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
        .order-details-page .order-price-total {
            color: #252b3a;
            font-size: .9rem;
        }
        .order-details-page .order-price-total strong {
            font-size: 1.1rem;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
        .order-details-page .order-payment-empty {
            padding: 12px;
            border-radius: 9px;
            background: #f7f8fc;
        }
        .order-details-page .order-details-help {
            background: #f7fff9;
            border-color: #ccebd7;
        }
        .order-details-page .order-details-help-icon {
            background: #dcf8e5;
            color: #16a34a;
        }
        @media (max-width: 767.98px) {
            .order-details-page .order-information-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .order-details-page .order-details-header .btn {
                min-height: 40px;
            }
        }
        @media (max-width: 400px) {
            .order-details-page .order-document-row {
                align-items: flex-start;
                gap: 8px;
            }
            .order-details-page .order-document-icon {
                flex-basis: 32px;
                width: 32px;
                height: 32px;
            }
            .order-details-page .order-document-action .btn {
                min-width: 36px;
                padding: 6px 8px;
            }
        }
    </style>
@endsection