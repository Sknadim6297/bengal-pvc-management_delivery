        @extends('user-panel.layout.app')

@section('content')
    @php
        $terminalStatuses = [\App\Models\Order::STATUS_FAILED, \App\Models\Order::STATUS_CANCELLED];
        $statusLabel = $order ? ucfirst(str_replace('_', ' ', $order->status)) : null;
        $timelineStages = [];

        if ($order) {
            $historyStatuses = $order->statusHistory->pluck('new_status')->all();

            if (in_array($order->status, $terminalStatuses, true)) {
                foreach ($order->statusHistory as $event) {
                    $timelineStages[] = [
                        'status' => $event->new_status,
                        'label' => ucfirst(str_replace('_', ' ', $event->new_status)),
                        'state' => $event->new_status === $order->status ? 'current' : 'complete',
                        'created_at' => $event->created_at,
                    ];
                }

                if (! in_array($order->status, $historyStatuses, true)) {
                    $timelineStages[] = [
                        'status' => $order->status,
                        'label' => $statusLabel,
                        'state' => 'current',
                        'created_at' => null,
                    ];
                }
            } else {
                $firstRecordedStatus = $historyStatuses[0] ?? null;
                $initialStatus = in_array($firstRecordedStatus, [
                    \App\Models\Order::STATUS_PENDING,
                    \App\Models\Order::STATUS_PAYMENT_PENDING,
                ], true)
                    ? $firstRecordedStatus
                    : ($order->service_type === 'photo-print'
                        ? \App\Models\Order::STATUS_PAYMENT_PENDING
                        : \App\Models\Order::STATUS_PENDING);

                $progressStatuses = [
                    \App\Models\Order::STATUS_PENDING,
                    \App\Models\Order::STATUS_PAYMENT_PENDING,
                    \App\Models\Order::STATUS_CONFIRMED,
                    \App\Models\Order::STATUS_PROCESSING,
                    \App\Models\Order::STATUS_PRINTING,
                    \App\Models\Order::STATUS_PACKED,
                    \App\Models\Order::STATUS_SHIPPED,
                    \App\Models\Order::STATUS_OUT_FOR_DELIVERY,
                    \App\Models\Order::STATUS_DELIVERED,
                ];

                if ($initialStatus === \App\Models\Order::STATUS_PAYMENT_PENDING) {
                    array_shift($progressStatuses);
                } elseif (! in_array(\App\Models\Order::STATUS_PAYMENT_PENDING, $historyStatuses, true)
                    && $order->status !== \App\Models\Order::STATUS_PAYMENT_PENDING) {
                    $progressStatuses = array_values(array_diff($progressStatuses, [\App\Models\Order::STATUS_PAYMENT_PENDING]));
                }

                $currentStageIndex = array_search($order->status, $progressStatuses, true);

                if ($currentStageIndex === false) {
                    $progressStatuses[] = $order->status;
                    $currentStageIndex = array_key_last($progressStatuses);
                }

                foreach ($progressStatuses as $stageIndex => $stageStatus) {
                    $event = $order->statusHistory->firstWhere('new_status', $stageStatus);
                    $createdAt = $event?->created_at;

                    if ($createdAt === null && $stageStatus === $initialStatus) {
                        $createdAt = $order->created_at;
                    }

                    $timelineStages[] = [
                        'status' => $stageStatus,
                        'label' => ucfirst(str_replace('_', ' ', $stageStatus)),
                        'state' => $stageIndex === $currentStageIndex
                            ? 'current'
                            : ($stageIndex < $currentStageIndex ? 'complete' : 'future'),
                        'created_at' => $createdAt,
                    ];
                }
            }
        }

        $supportMessage = $order
            ? 'Hello, I need help with Order '.$order->order_number.'.'
            : 'Hello, I need help with my PVC order.';
        $whatsappUrl = $generalSettings->whatsappSupportUrl($supportMessage);
        $helplineUrl = $generalSettings->helplineTelUrl();
    @endphp

    <div class="support-page">
        <div class="support-page-header mb-3">
            <h1 class="page-title mb-1">Support &amp; Tracking</h1>
            <p class="text-muted mb-0">Track your PVC order and get support when you need it.</p>
        </div>

        @if ($trackingError)
            <div class="toast-container position-fixed top-0 end-0 p-3" aria-live="polite" aria-atomic="true">
                <div class="toast align-items-center text-bg-danger border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                    <div class="d-flex">
                        <div class="toast-body">{{ $trackingError }}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        @endif

        <section class="support-box tracking-box support-search mb-3">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="support-icon mb-0"><i class="bi bi-box-seam" aria-hidden="true"></i></div>
                <div>
                    <h2 class="h5 fw-bold mb-1">Track Your Order</h2>
                    <p class="text-muted small mb-0">Enter your Order ID to check its latest status.</p>
                </div>
            </div>

            <form id="trackingForm" method="GET" action="{{ route('user.track-help') }}" class="row g-2 align-items-stretch">
                <div class="col-12 col-md">
                    <label class="visually-hidden" for="order_id">Order ID</label>
                    <div class="input-group support-order-search">
                        <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                        <input id="order_id" name="order_id" type="text" class="form-control"
                            value="{{ $orderNumber }}" placeholder="PVC-20260930-000007"
                            maxlength="32" autocomplete="off" aria-describedby="orderIdHelp">
                    </div>
                </div>
                <div class="col-12 col-md-auto">
                    <button id="trackOrderButton" type="submit" class="btn btn-primary support-track-button w-100">
                        <i class="bi bi-search me-1" aria-hidden="true"></i> Track Order
                    </button>
                </div>
            </form>
            <div id="orderIdHelp" class="form-text mt-2">Use the Order ID from your order confirmation.</div>

            @if (! $order && ! $trackingError)
                <div class="support-empty-state mt-3 d-flex align-items-center gap-3">
                    <i class="bi bi-receipt-cutoff" aria-hidden="true"></i>
                    <div>
                        <div class="fw-semibold">Track an order</div>
                        <div class="small text-muted">Enter the Order ID from your order confirmation above.</div>
                    </div>
                </div>
            @endif
        </section>

        @if ($order)
            <section class="support-box tracking-result mb-3">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start gap-3">
                    <div class="min-w-0">
                        <div class="small text-muted mb-1">Order ID</div>
                        <h2 class="support-order-number mb-2">{{ $order->order_number }}</h2>
                        <div class="small text-muted">Placed: <time datetime="{{ $order->created_at?->toIso8601String() }}">{{ $order->created_at?->format('d M Y, h:i A') }}</time></div>
                    </div>
                    <div class="support-current-status">
                        <div class="small text-muted mb-1">Current Status</div>
                        @include('user-panel.partials.order-status-badge', ['status' => $order->status, 'class' => 'support-status-badge'])
                    </div>
                </div>

                <div class="support-status-section mt-4 pt-3 border-top">
                    <h3 class="h6 fw-bold mb-3">Order Progress</h3>
                    <div class="tracking-timeline" aria-label="Order progress">
                    @forelse ($timelineStages as $stage)
                        <div class="tracking-timeline-step is-{{ $stage['state'] }} {{ $stage['state'] === 'current' && in_array($stage['status'], $terminalStatuses, true) ? 'is-terminal' : '' }}" @if ($stage['state'] === 'current') aria-current="step" @endif>
                            <span class="tracking-timeline-marker" aria-hidden="true">
                                <i class="bi {{ $stage['state'] === 'complete' ? 'bi-check-lg' : ($stage['state'] === 'current' ? 'bi-record-circle-fill' : 'bi-circle') }}"></i>
                            </span>
                            <div class="tracking-timeline-label">@include('user-panel.partials.order-status-badge', ['status' => $stage['status'], 'labelOnly' => true])</div>
                            @if ($stage['created_at'])
                                <time class="tracking-timeline-time" datetime="{{ $stage['created_at']->toIso8601String() }}">{{ $stage['created_at']->format('d M, h:i A') }}</time>
                            @endif
                        </div>
                    @empty
                    @endforelse
                    </div>
                </div>
            </section>
        @endif

        <section class="support-box whatsapp-box support-contact">
            <div class="support-contact-copy d-flex align-items-start gap-3">
                <div class="support-icon whatsapp-icon mb-0"><i class="bi bi-headset" aria-hidden="true"></i></div>
                <div>
                    <h2 class="h5 fw-bold mb-1">Need Help?</h2>
                    <p class="text-muted small mb-0">Our support team can help with your order or bulk PVC requirements.</p>
                </div>
            </div>

            <div class="support-contact-actions">
                @if ($whatsappUrl)
                    <a href="{{ $whatsappUrl }}" class="btn btn-success" target="_blank" rel="noopener noreferrer">
                        <i class="bi bi-whatsapp me-2" aria-hidden="true"></i>Chat on WhatsApp
                    </a>
                @endif
                @if ($helplineUrl)
                    <a href="{{ $helplineUrl }}" class="btn btn-outline-secondary">
                        <i class="bi bi-telephone me-2" aria-hidden="true"></i>Call Support <span class="ms-1">{{ $generalSettings->helpline_number }}</span>
                    </a>
                @endif
            </div>

            @unless ($whatsappUrl || $helplineUrl)
                <p class="small text-muted mb-0">Support contact information is not configured.</p>
            @endunless
        </section>
    </div>

    <style>
        .support-page .support-box {
            padding: 22px;
            border-radius: 14px;
            transition: box-shadow .2s ease;
        }
        .support-page .support-box:hover {
            transform: none;
            box-shadow: 0 8px 25px rgba(0, 0, 0, .08);
        }
        .support-page .support-search .form-control,
        .support-page .support-search .input-group-text,
        .support-page .support-track-button {
            min-height: 48px;
        }
        .support-page .support-search .input-group-text {
            background: #fff;
            color: #737b8c;
            border-color: #dfe3eb;
            border-radius: 9px 0 0 9px;
        }
        .support-page .support-search .form-control {
            border-left: 0;
            border-radius: 0 9px 9px 0;
            box-shadow: none;
        }
        .support-page .support-search .form-control:focus {
            border-color: #5146e5;
            box-shadow: 0 0 0 3px rgba(81, 70, 229, .1);
        }
        .support-page .support-track-button {
            min-width: 154px;
            border-radius: 9px;
            font-weight: 600;
        }
        .support-page .support-empty-state {
            padding: 12px 14px;
            border: 1px solid #e3e7ee;
            border-radius: 10px;
            background: #f8f9fb;
        }
        .support-page .support-empty-state > i {
            color: #5146e5;
            font-size: 1.2rem;
        }
        .support-page .support-order-number {
            max-width: 100%;
            color: #252b3a;
            font-size: 1.25rem;
            font-weight: 700;
            overflow-wrap: anywhere;
        }
        .support-page .support-status-badge {
            flex: 0 0 auto;
            padding: .55rem .8rem;
            font-size: .72rem;
            text-transform: uppercase;
        }
        .support-page .tracking-timeline {
            display: flex;
            align-items: flex-start;
        }
        .support-page .tracking-timeline-step {
            position: relative;
            flex: 1 1 0;
            min-width: 0;
            padding-right: 12px;
        }
        .support-page .tracking-timeline-step:not(:last-child)::after {
            position: absolute;
            top: 12px;
            left: 30px;
            right: 12px;
            height: 2px;
            background: #dfe3eb;
            content: '';
        }
        .support-page .tracking-timeline-step.is-complete::after {
            background: #198754;
        }
        .support-page .tracking-timeline-marker {
            position: relative;
            z-index: 1;
            display: grid;
            width: 26px;
            height: 26px;
            place-items: center;
            border-radius: 50%;
            background: #dfe3eb;
            color: #6c757d;
            font-size: .8rem;
        }
        .support-page .tracking-timeline-step.is-complete .tracking-timeline-marker {
            background: #198754;
            color: #fff;
        }
        .support-page .tracking-timeline-step.is-complete .tracking-timeline-label {
            color: #198754;
        }
        .support-page .tracking-timeline-step.is-current .tracking-timeline-marker {
            background: #5146e5;
            color: #fff;
        }
        .support-page .tracking-timeline-step.is-current .tracking-timeline-label {
            color: #5146e5;
            font-weight: 700;
        }
        .support-page .tracking-timeline-step.is-future .tracking-timeline-label {
            color: #737b8c;
            font-weight: 500;
        }
        .support-page .tracking-timeline-step.is-terminal .tracking-timeline-marker {
            background: #dc3545;
        }
        .support-page .tracking-timeline-label {
            margin-top: 9px;
            color: #252b3a;
            font-size: .82rem;
            font-weight: 600;
            overflow-wrap: anywhere;
        }
        .support-page .tracking-timeline-time {
            display: block;
            margin-top: 3px;
            color: #737b8c;
            font-size: .75rem;
        }
        .support-page .support-contact {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }
        .support-page .support-contact-actions {
            display: flex;
            flex: 0 0 auto;
            flex-wrap: wrap;
            gap: 8px;
        }
        .support-page .support-contact-actions .btn {
            min-height: 42px;
            border-radius: 9px;
            font-weight: 600;
        }
        @media (max-width: 767.98px) {
            .support-page .support-box {
                padding: 18px;
            }
            .support-page .support-track-button {
                min-width: 0;
            }
            .support-page .support-status-badge {
                align-self: flex-start;
            }
            .support-page .tracking-timeline {
                flex-direction: column;
                gap: 0;
            }
            .support-page .tracking-timeline-step {
                flex: 0 0 auto;
                min-height: 48px;
                padding: 0 0 12px 38px;
            }
            .support-page .tracking-timeline-step:not(:last-child)::after {
                top: 25px;
                bottom: -1px;
                left: 12px;
                right: auto;
                width: 2px;
                height: auto;
            }
            .support-page .tracking-timeline-marker {
                position: absolute;
                top: 0;
                left: 0;
            }
            .support-page .tracking-timeline-label {
                margin-top: 0;
            }
            .support-page .support-contact {
                align-items: stretch;
                flex-direction: column;
                gap: 16px;
            }
            .support-page .support-contact-actions {
                display: grid;
                grid-template-columns: 1fr;
            }
            .support-page .support-contact-actions .btn {
                width: 100%;
            }
        }
    </style>

@endsection

@push('scripts')
    <script>
        document.getElementById('trackingForm').addEventListener('submit', function () {
            const button = document.getElementById('trackOrderButton');
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Checking';
        });

        document.querySelectorAll('.toast').forEach(function (toastElement) {
            new bootstrap.Toast(toastElement).show();
        });
    </script>
@endpush
