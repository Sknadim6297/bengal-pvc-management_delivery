<?php

namespace Tests\Feature;

use App\Models\User;
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

    public function test_admin_listing_remains_bounded_and_uses_the_name_index_at_million_scale(): void
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

        $this->assertContains('users_name_index', $usedIndexes);
        $this->assertLessThan(64 * 1024 * 1024, $memoryDelta);

        fwrite(STDOUT, sprintf(
            "MySQL million-row admin page: %.1f ms, %d bytes peak delta, %d scale records.\n",
            $elapsedMilliseconds,
            $memoryDelta,
            $scaleUsers,
        ));
    }
}
