<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class AdminDashboardStatistics
{
    public const CACHE_KEY = 'admin.dashboard.statistics.v3';

    public static function remember(): object
    {
        return Cache::remember(self::CACHE_KEY, now()->addSeconds(30), static function (): object {
            $statistics = User::query()
                ->selectRaw('COUNT(*) as total_accounts')
                ->selectRaw('SUM(CASE WHEN role = ? THEN 1 ELSE 0 END) as registered_users', [User::ROLE_USER])
                ->selectRaw('SUM(CASE WHEN role = ? AND status = ? THEN 1 ELSE 0 END) as active_users', [User::ROLE_USER, User::STATUS_ACTIVE])
                ->selectRaw('SUM(CASE WHEN role = ? AND status = ? THEN 1 ELSE 0 END) as suspended_users', [User::ROLE_USER, User::STATUS_SUSPENDED])
                ->firstOrFail();

            $statistics->total_users = (int) $statistics->registered_users;
            $statistics->active_users = (int) $statistics->active_users;
            $statistics->suspended_users = (int) $statistics->suspended_users;

            $orders = Order::query()
                ->selectRaw('COUNT(*) as total_orders')
                ->selectRaw('COALESCE(SUM(CASE WHEN service_type = ? THEN 1 ELSE 0 END), 0) as total_pvc_orders', ['pvc-card'])
                ->selectRaw('COALESCE(SUM(CASE WHEN service_type = ? THEN 1 ELSE 0 END), 0) as total_photo_orders', ['photo-print'])
                ->selectRaw('COALESCE(SUM(CASE WHEN status IN (?, ?) THEN 1 ELSE 0 END), 0) as pending_orders', [Order::STATUS_PENDING, Order::STATUS_PAYMENT_PENDING])
                ->selectRaw('COALESCE(SUM(CASE WHEN status IN (?, ?, ?, ?, ?, ?) THEN 1 ELSE 0 END), 0) as processing_orders', Order::ACTIVE_CARD_STATUSES)
                ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as delivered_orders', [Order::STATUS_DELIVERED])
                ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as failed_orders', [Order::STATUS_FAILED])
                ->selectRaw('COALESCE(SUM(CASE WHEN payment_status = ? THEN total_amount ELSE 0 END), 0) as total_revenue', ['paid'])
                ->firstOrFail();

            $today = Order::query()
                ->where('created_at', '>=', now()->startOfDay())
                ->where('created_at', '<', now()->addDay()->startOfDay())
                ->selectRaw('COUNT(*) as today_orders')
                ->selectRaw('COALESCE(SUM(CASE WHEN payment_status = ? THEN total_amount ELSE 0 END), 0) as today_revenue', ['paid'])
                ->firstOrFail();

            foreach ([
                'total_orders', 'total_pvc_orders', 'total_photo_orders', 'pending_orders',
                'processing_orders', 'delivered_orders', 'failed_orders',
            ] as $column) {
                $statistics->{$column} = (int) $orders->{$column};
            }
            $statistics->total_revenue = (string) $orders->total_revenue;
            $statistics->today_orders = (int) $today->today_orders;
            $statistics->today_revenue = (string) $today->today_revenue;

            return $statistics;
        });
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
