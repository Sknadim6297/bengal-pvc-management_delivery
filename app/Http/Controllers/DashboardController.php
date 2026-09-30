<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Services\AdminDashboardStatistics;
use App\Services\LiveOrderFeedService;
use App\Services\UserDashboardStatistics;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function user(LiveOrderFeedService $liveOrderFeed): View
    {
        $user = Auth::user();

        return view('user-panel.dashboard', [
            'user' => $user,
            'statistics' => UserDashboardStatistics::forUser($user),
            'liveOrderFeed' => $liveOrderFeed->latest(),
            'recentOrders' => Order::query()
                ->select(['id', 'user_id', 'order_number', 'service_type', 'status', 'quantity', 'created_at'])
                ->where('user_id', $user->id)
                ->latest('created_at')
                ->limit(5)
                ->get(),
        ]);
    }

    public function admin(): View
    {
        return view('admin-panel.dashboard', [
            'user' => Auth::user(),
            'statistics' => AdminDashboardStatistics::remember(),
            'recentOrders' => Order::query()
                ->select(['id', 'user_id', 'order_number', 'service_type', 'status', 'created_at'])
                ->with('user:id,name')
                ->latest('created_at')
                ->latest('id')
                ->limit(10)
                ->get(),
            'recentUsers' => User::query()
                ->select(['id', 'name', 'email', 'whatsapp_number', 'status', 'created_at'])
                ->where('role', User::ROLE_USER)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(10)
                ->get(),
        ]);
    }
}
