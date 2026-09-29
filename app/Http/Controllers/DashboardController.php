<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function user(): View
    {
        return view('user-panel.dashboard', [
            'user' => Auth::user(),
        ]);
    }

    public function admin(): View
    {
        $statistics = User::query()
            ->selectRaw('COUNT(*) as total_accounts')
            ->selectRaw('SUM(CASE WHEN role = ? THEN 1 ELSE 0 END) as registered_users', [User::ROLE_USER])
            ->selectRaw('SUM(CASE WHEN role = ? AND status = ? THEN 1 ELSE 0 END) as active_users', [User::ROLE_USER, User::STATUS_ACTIVE])
            ->first();

        return view('admin-panel.dashboard', [
            'user' => Auth::user(),
            'statistics' => $statistics,
        ]);
    }
}
