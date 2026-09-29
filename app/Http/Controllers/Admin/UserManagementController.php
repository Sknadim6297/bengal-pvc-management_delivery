<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUserIndexRequest;
use App\Http\Requests\UpdateAdminUserStatusRequest;
use App\Models\User;
use App\Services\AdminDashboardStatistics;
use App\Support\IndianPhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(AdminUserIndexRequest $request): View
    {
        $filters = $request->validated();
        $users = User::query()
            ->where('role', User::ROLE_USER)
            ->where('id', '!=', Auth::id())
            ->select([
                'id',
                'name',
                'email',
                'whatsapp_number',
                'district',
                'role',
                'status',
                'created_at',
            ]);

        if (! empty($filters['role'])) {
            $users->where('role', $filters['role']);
        }

        if (! empty($filters['status'])) {
            $users->where('status', $filters['status']);
        }

        if (! empty($filters['from'])) {
            $users->where('created_at', '>=', Carbon::parse($filters['from'])->startOfDay());
        }

        if (! empty($filters['to'])) {
            $users->where('created_at', '<', Carbon::parse($filters['to'])->addDay()->startOfDay());
        }

        $search = trim((string) ($filters['q'] ?? ''));

        if ($search !== '') {
            $prefix = str_replace(['%', '_', '\\'], '', $search).'%';
            $phoneNumber = IndianPhoneNumber::normalize($search);

            $users->where(function ($query) use ($search, $prefix, $phoneNumber): void {
                if (ctype_digit($search)) {
                    $query->orWhere('id', (int) $search);
                }

                $query->orWhere('name', 'like', $prefix)
                    ->orWhere('email', 'like', Str::lower($prefix))
                    ->orWhere('district', 'like', $prefix);

                if (IndianPhoneNumber::isValid($phoneNumber)) {
                    $query->orWhere('whatsapp_number', $phoneNumber);
                }
            });
        }

        $sortColumns = [
            'id' => 'id',
            'name' => 'name',
            'email' => 'email',
            'role' => 'role',
            'status' => 'status',
            'created_at' => 'created_at',
        ];
        $sort = $filters['sort'] ?? 'id';
        $direction = $filters['direction'] ?? 'desc';
        $sortColumn = $sortColumns[$sort];

        $users->orderBy($sortColumn, $direction);

        if ($sortColumn !== 'id' && $sortColumn !== 'email') {
            $users->orderBy('id', $direction);
        }

        return view('admin-panel.users.index', [
            'users' => $users->cursorPaginate(50)->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function show(int $userId): View
    {
        $user = User::query()
            ->where('role', User::ROLE_USER)
            ->where('id', '!=', Auth::id())
            ->select([
                'id',
                'name',
                'email',
                'whatsapp_number',
                'district',
                'role',
                'status',
                'email_verified_at',
                'created_at',
                'updated_at',
            ])
            ->findOrFail($userId);

        return view('admin-panel.users.show', ['user' => $user]);
    }

    public function updateStatus(UpdateAdminUserStatusRequest $request, int $userId): RedirectResponse
    {
        $user = User::query()->select(['id', 'role', 'status'])->findOrFail($userId);

        if ($user->isAdmin()) {
            abort(403, 'Administrator accounts cannot be changed from user management.');
        }

        $user->status = $request->validated('status');
        $user->save();
        AdminDashboardStatistics::forget();

        return back()->with('statusMessage', 'User status updated.');
    }
}
