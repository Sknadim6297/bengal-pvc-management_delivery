@php
    $orderStatusPresentation = match ($status) {
        \App\Models\Order::STATUS_PENDING => ['Order Placed', 'warning text-dark'],
        \App\Models\Order::STATUS_PAYMENT_PENDING => ['Awaiting Payment', 'warning text-dark'],
        \App\Models\Order::STATUS_CONFIRMED => ['Confirmed', 'primary'],
        \App\Models\Order::STATUS_PROCESSING => ['Processing', 'primary'],
        \App\Models\Order::STATUS_PRINTING => ['Printing', 'primary'],
        \App\Models\Order::STATUS_PACKED => ['Packed', 'primary'],
        \App\Models\Order::STATUS_SHIPPED => ['Shipped', 'info text-dark'],
        \App\Models\Order::STATUS_OUT_FOR_DELIVERY => ['Out for Delivery', 'info text-dark'],
        \App\Models\Order::STATUS_DELIVERED => ['Delivered', 'success'],
        \App\Models\Order::STATUS_FAILED => ['Failed', 'danger'],
        \App\Models\Order::STATUS_CANCELLED => ['Cancelled', 'danger'],
        default => [ucfirst(str_replace('_', ' ', (string) $status)), 'secondary'],
    };
@endphp

@if ($labelOnly ?? false)
    {{ $orderStatusPresentation[0] }}
@else
    <span class="badge rounded-pill bg-{{ $orderStatusPresentation[1] }} {{ $class ?? '' }}">{{ $orderStatusPresentation[0] }}</span>
@endif