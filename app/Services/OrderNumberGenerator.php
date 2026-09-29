<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Str;

class OrderNumberGenerator
{
    public function temporary(): string
    {
        return 'TMP-'.Str::random(20);
    }

    public function forOrder(Order $order): string
    {
        $prefix = $order->service_type === 'photo-print' ? 'PHOTO' : 'PVC';

        return $prefix.'-'.$order->created_at->format('Ymd').'-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);
    }
}