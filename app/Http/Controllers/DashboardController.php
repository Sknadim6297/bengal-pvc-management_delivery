<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AdminDashboardStatistics;
use App\Services\UserDashboardStatistics;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function user(): View
    {
        $user = Auth::user();

        abort_unless($user instanceof User, 401);

        return view('user-panel.dashboard', [
            'user' => $user,
            'statistics' => UserDashboardStatistics::forUser($user),
        ]);
    }

    public function admin(): View
    {
        return view('admin-panel.dashboard', [
            'user' => Auth::user(),
            'statistics' => AdminDashboardStatistics::remember(),
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
