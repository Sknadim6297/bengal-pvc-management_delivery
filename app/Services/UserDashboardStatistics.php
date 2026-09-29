<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;

class UserDashboardStatistics
{
    public static function forUser(User $user): object
    {
        return Order::query()
            ->where('user_id', $user->getKey())
            ->where('service_type', 'pvc-card')
            ->selectRaw('COALESCE(SUM(CASE WHEN status IN (?, ?, ?, ?, ?, ?) THEN quantity ELSE 0 END), 0) as processing_cards', Order::ACTIVE_CARD_STATUSES)
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN quantity ELSE 0 END), 0) as delivered_cards', [Order::STATUS_DELIVERED])
            ->firstOrFail();
    }
}