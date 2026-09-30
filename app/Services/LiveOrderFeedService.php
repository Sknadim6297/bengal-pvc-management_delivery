<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class LiveOrderFeedService
{
    public function latest(): Collection
    {
        return Order::query()
            ->select(['id', 'user_id', 'service_type', 'status', 'quantity', 'delivery_address', 'created_at'])
            ->with(['user:id,name,district'])
            ->where('service_type', 'pvc-card')
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->whereHas('user', fn ($query) => $query->where('role', User::ROLE_USER))
            ->latest('created_at')
            ->latest('id')
            ->limit(5)
            ->get();
    }
}