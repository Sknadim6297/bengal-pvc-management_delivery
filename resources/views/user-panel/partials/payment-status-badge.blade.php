@php
    $paymentStatusPresentation = match (strtolower((string) $status)) {
        'pending' => ['Pending', 'warning text-dark'],
        'paid' => ['Paid', 'success'],
        default => [ucfirst(str_replace('_', ' ', (string) $status)), 'secondary'],
    };
@endphp

<span class="badge rounded-pill bg-{{ $paymentStatusPresentation[1] }} {{ $class ?? '' }}">{{ $paymentStatusPresentation[0] }}</span>