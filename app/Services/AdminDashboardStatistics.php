<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class AdminDashboardStatistics
{
    public const CACHE_KEY = 'admin.dashboard.statistics.v2';

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

            $statistics->total_pvc_orders = 0;
            $statistics->processing_orders = 0;
            $statistics->delivered_orders = 0;
            $statistics->failed_orders = 0;
            $statistics->total_photo_orders = 0;

            return $statistics;
        });
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
