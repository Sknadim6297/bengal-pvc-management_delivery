<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $name = trim((string) config('auth.admin.name'));
        $email = Str::lower(trim((string) config('auth.admin.email')));
        $password = (string) config('auth.admin.password');

        if ($name === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false || strlen($password) < 12) {
            throw new RuntimeException('Admin seeding requires ADMIN_NAME, a valid ADMIN_EMAIL, and an ADMIN_PASSWORD of at least 12 characters.');
        }

        $admin = User::firstOrNew(['email' => $email]);
        $admin->name = $name;

        if (! $admin->exists || ! Hash::check($password, (string) $admin->password)) {
            $admin->password = $password;
        }

        $admin->forceFill([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ])->save();
    }
}
