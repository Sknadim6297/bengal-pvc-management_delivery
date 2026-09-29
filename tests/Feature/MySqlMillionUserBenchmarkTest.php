<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AdminDashboardStatistics;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MySqlMillionUserBenchmarkTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (env('RUN_MYSQL_SCALE_TESTS') !== '1') {
            $this->markTestSkipped('Opt in with RUN_MYSQL_SCALE_TESTS=1 on a dedicated local MySQL benchmark database.');
        }

        $connection = DB::connection();

        if ($connection->getDriverName() !== 'mysql'
            || ! str_ends_with((string) $connection->getDatabaseName(), '_benchmark')) {
            $this->fail('Scale tests require a MySQL database name ending in _benchmark; no other database may be used.');
        }
    }

    public function test_auth_and_admin_queries_remain_bounded_at_million_scale(): void
    {
        $scaleUsers = DB::table('users')
            ->where('email', 'like', 'scale-user-%@benchmark.invalid')
            ->count();

        $this->assertGreaterThanOrEqual(1_000_000, $scaleUsers, 'Run ScaleTestUserSeeder against the dedicated benchmark database first.');

        $admin = User::query()->firstOrNew(['email' => 'scale-admin@benchmark.invalid']);

        if (! $admin->exists) {
            $admin->name = 'Scale Test Admin';
            $admin->password = Hash::make(bin2hex(random_bytes(32)));
        }

        $admin->forceFill([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
        ])->save();

        $loginEmail = 'scale-login-'.bin2hex(random_bytes(6)).'@benchmark.invalid';
        $loginPhone = '9187'.random_int(10_000_000, 99_999_999);
        $loginUser = User::query()->firstOrNew(['email' => $loginEmail]);
        if (! $loginUser->exists) {
            $loginUser->name = 'Scale Authentication Lookup';
            $loginUser->whatsapp_number = $loginPhone;
            $loginUser->district = 'Nadia';
            $loginUser->password = 'ScaleLoginPass123!';
        }
        $loginUser->forceFill([
            'role' => User::ROLE_USER,
            'status' => User::STATUS_ACTIVE,
        ])->save();

        $memoryBefore = memory_get_usage(true);
        $startedAt = hrtime(true);

        $response = $this->actingAs($admin)->get(route('admin.users.index', [
            'q' => 'Scale User 000',
            'sort' => 'name',
            'direction' => 'asc',
        ]));

        $elapsedMilliseconds = (hrtime(true) - $startedAt) / 1_000_000;
        $memoryDelta = max(0, memory_get_peak_usage(true) - $memoryBefore);

        $response->assertOk()->assertViewHas('users', function ($users): bool {
            return count($users->items()) <= 50 && $users->hasMorePages();
        });

        $explain = DB::select(
            'EXPLAIN SELECT id FROM users WHERE name LIKE ? ORDER BY name, id LIMIT 51',
            ['Scale User 000%'],
        );
        $usedIndexes = array_values(array_filter(array_map(fn ($row) => $row->key, $explain)));

        $this->assertContains('users_name_id_index', $usedIndexes);
        $this->assertLessThan(64 * 1024 * 1024, $memoryDelta);

        $detailUserId = User::query()->where('email', 'scale-user-0000001@benchmark.invalid')->value('id');
        $detailStartedAt = hrtime(true);
        $this->get(route('admin.users.show', $detailUserId))->assertOk();
        $detailMilliseconds = (hrtime(true) - $detailStartedAt) / 1_000_000;

        Cache::forget(AdminDashboardStatistics::CACHE_KEY);
        $dashboardStartedAt = hrtime(true);
        $this->get(route('admin.dashboard'))->assertOk();
        $dashboardMilliseconds = (hrtime(true) - $dashboardStartedAt) / 1_000_000;

        $warmDashboardStartedAt = hrtime(true);
        $this->get(route('admin.dashboard'))->assertOk();
        $warmDashboardMilliseconds = (hrtime(true) - $warmDashboardStartedAt) / 1_000_000;

        $this->post('/logout')->assertRedirect('/');
        $loginStartedAt = hrtime(true);
        $this->post('/login', [
            'login' => $loginEmail,
            'password' => 'ScaleLoginPass123!',
        ])->assertRedirect('/dashboard');
        $loginMilliseconds = (hrtime(true) - $loginStartedAt) / 1_000_000;

        $this->post('/logout')->assertRedirect('/');
        $registrationStartedAt = hrtime(true);
        $this->from('/register')->post('/register', [
            'name' => 'Duplicate Benchmark Registration',
            'whatsapp_number' => $loginPhone,
            'email' => $loginEmail,
            'district' => 'Nadia',
            'password' => 'ScaleLoginPass123!',
        ])->assertRedirect('/register')->assertSessionHasErrors(['email', 'whatsapp_number']);
        $registrationMilliseconds = (hrtime(true) - $registrationStartedAt) / 1_000_000;

        $emailPlan = DB::select('EXPLAIN SELECT id FROM users WHERE email = ? LIMIT 1', ['scale-user-0000001@benchmark.invalid']);
        $phonePlan = DB::select('EXPLAIN SELECT id FROM users WHERE whatsapp_number = ? LIMIT 1', ['919000000001']);
        $detailPlan = DB::select('EXPLAIN SELECT id, name, email FROM users WHERE id = ? LIMIT 1', [$detailUserId]);

        $this->assertSame('users_email_unique', $emailPlan[0]->key);
        $this->assertSame('users_whatsapp_number_unique', $phonePlan[0]->key);
        $this->assertSame('PRIMARY', $detailPlan[0]->key);

        fwrite(STDOUT, sprintf(
            "MySQL benchmark rows=%d list=%.1fms detail=%.1fms dashboard_cold=%.1fms dashboard_warm=%.1fms login_lookup_and_test_hash=%.1fms duplicate_registration=%.1fms peak_delta=%dB bcrypt_rounds=%s.\n",
            $scaleUsers,
            $elapsedMilliseconds,
            $detailMilliseconds,
            $dashboardMilliseconds,
            $warmDashboardMilliseconds,
            $loginMilliseconds,
            $registrationMilliseconds,
            $memoryDelta,
            config('hashing.bcrypt.rounds'),
        ));
    }
}
