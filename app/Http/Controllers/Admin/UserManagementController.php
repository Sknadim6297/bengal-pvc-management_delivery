<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUserIndexRequest;
use App\Http\Requests\UpdateAdminUserStatusRequest;
use App\Models\User;
use App\Support\IndianPhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(AdminUserIndexRequest $request): View
    {
        $filters = $request->validated();
        $users = User::query()->select([
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
            if (filter_var($search, FILTER_VALIDATE_EMAIL) !== false) {
                $users->where('email', Str::lower($search));
            } else {
                $phoneNumber = IndianPhoneNumber::normalize($search);

                if (IndianPhoneNumber::isValid($phoneNumber)) {
                    $users->where('whatsapp_number', $phoneNumber);
                } else {
                    $prefix = str_replace(['%', '_', '\\'], '', $search).'%';
                    $searchColumn = str_contains($search, '@') ? 'email' : 'name';
                    $users->where($searchColumn, 'like', $searchColumn === 'email' ? Str::lower($prefix) : $prefix);
                }
            }
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
        $user = User::query()->select([
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
        ])->findOrFail($userId);

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

        return back()->with('statusMessage', 'User status updated.');
    }
}
