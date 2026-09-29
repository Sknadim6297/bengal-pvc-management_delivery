<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AdminDashboardStatistics;
use Database\Seeders\ScaleTestUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_list_uses_bounded_cursor_pagination(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        User::factory()->count(120)->create();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertOk()
            ->assertViewHas('users', fn ($users): bool => count($users->items()) === 50 && $users->hasMorePages());

        $response->assertDontSee(User::firstOrFail()->password, false);

        $userListQuery = collect(DB::getQueryLog())->first(function (array $query): bool {
            return str_contains(strtolower($query['query']), 'from "users"')
                && str_contains(strtolower($query['query']), 'limit 51');
        });

        $this->assertNotNull($userListQuery, 'The first cursor page must request at most 51 rows to display 50 plus a continuation check.');
    }

    public function test_admin_search_and_filters_are_applied_before_pagination(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        User::factory()->create([
            'name' => 'North District Customer',
            'email' => 'north.customer@example.com',
            'role' => User::ROLE_USER,
            'status' => User::STATUS_ACTIVE,
        ]);
        User::factory()->create([
            'name' => 'North Suspended Customer',
            'email' => 'north.suspended@example.com',
            'role' => User::ROLE_USER,
            'status' => User::STATUS_SUSPENDED,
        ]);
        User::factory()->create([
            'name' => 'South District Customer',
            'email' => 'south.customer@example.com',
            'role' => User::ROLE_USER,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index', [
                'q' => 'North',
                'role' => User::ROLE_USER,
                'status' => User::STATUS_ACTIVE,
                'sort' => 'name',
                'direction' => 'asc',
            ]))
            ->assertOk()
            ->assertSee('north.customer@example.com')
            ->assertDontSee('north.suspended@example.com')
            ->assertDontSee('south.customer@example.com');

        $this->get(route('admin.users.index', ['q' => 'north.customer@']))
            ->assertOk()
            ->assertSee('north.customer@example.com')
            ->assertDontSee('south.customer@example.com');
    }

    public function test_admin_user_list_shows_only_the_search_controls(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Search ID, name, email, WhatsApp or district')
            ->assertSee('admin-table')
            ->assertDontSee('id="role"', false)
            ->assertDontSee('id="status"', false)
            ->assertDontSee('id="from"', false)
            ->assertDontSee('id="to"', false)
            ->assertDontSee('id="sort"', false)
            ->assertDontSee('id="direction"', false);
    }

    public function test_admin_search_supports_user_id_district_and_whatsapp(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create([
            'name' => 'Field Search Customer',
            'email' => 'field.customer@example.com',
            'whatsapp_number' => '919876543210',
            'district' => 'Kolkata',
        ]);

        foreach ([(string) $user->id, 'Kolkata', '919876543210'] as $term) {
            $this->actingAs($admin)
                ->get(route('admin.users.index', ['q' => $term]))
                ->assertOk()
                ->assertSee('field.customer@example.com');
        }
    }

    public function test_admin_dashboard_uses_database_aggregates(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        User::factory()->count(2)->create(['status' => User::STATUS_ACTIVE]);
        User::factory()->create(['status' => User::STATUS_SUSPENDED]);
        Cache::forget(AdminDashboardStatistics::CACHE_KEY);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Total Users')
            ->assertSee('Active Users')
            ->assertSee('Total Orders')
            ->assertSee('Pending Orders')
            ->assertSee('Processing Orders')
            ->assertSee('Delivered Orders')
            ->assertSee('Failed Orders')
            ->assertSee('Total Revenue')
            ->assertSee("Today's Orders")
            ->assertSee("Today's Revenue")
            ->assertSee('No orders found')
            ->assertViewHas('statistics', fn (object $statistics): bool => $statistics->total_users === 3
                && $statistics->active_users === 2
                && $statistics->suspended_users === 1
                && $statistics->total_orders === 0
                && $statistics->total_pvc_orders === 0
                && (float) $statistics->total_revenue === 0.0);
    }

    public function test_admin_dashboard_loads_only_ten_recent_users(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        User::factory()->count(12)->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('recentUsers', fn ($users): bool => $users->count() === 10);
    }

    public function test_admin_dashboard_reuses_aggregate_cache(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        Cache::forget(AdminDashboardStatistics::CACHE_KEY);
        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->get(route('admin.dashboard'))->assertOk();

        $aggregateQueries = collect(DB::getQueryLog())->filter(
            fn (array $query): bool => str_contains(strtolower($query['query']), 'count(*) as total_accounts'),
        );

        $this->assertCount(1, $aggregateQueries);
        $this->assertTrue(Cache::has(AdminDashboardStatistics::CACHE_KEY));
    }

    public function test_user_details_never_expose_password_or_remember_token(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create([
            'email' => 'details@example.com',
            'remember_token' => 'do-not-display-this-token',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.show', $user->id))
            ->assertOk()
            ->assertSee('details@example.com')
            ->assertDontSee($user->password, false)
            ->assertDontSee('do-not-display-this-token');
    }

    public function test_admin_can_suspend_and_reactivate_users_but_cannot_change_admin_status(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $otherAdmin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        Cache::put(AdminDashboardStatistics::CACHE_KEY, (object) ['total_accounts' => 0], now()->addMinute());

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->patch(route('admin.users.status', $user->id), ['status' => User::STATUS_SUSPENDED])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', ['id' => $user->id, 'status' => User::STATUS_SUSPENDED]);
        $this->assertFalse(Cache::has(AdminDashboardStatistics::CACHE_KEY));

        $this->actingAs($admin)
            ->patch(route('admin.users.status', $otherAdmin->id), ['status' => User::STATUS_SUSPENDED])
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $otherAdmin->id, 'status' => User::STATUS_ACTIVE]);
    }

    public function test_non_admin_cannot_access_user_management(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $managedUser = User::factory()->create(['role' => User::ROLE_USER]);

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $this->get(route('admin.users.show', $managedUser->id))->assertForbidden();
        $this->patch(route('admin.users.status', $managedUser->id), ['status' => User::STATUS_SUSPENDED])->assertForbidden();
    }

    public function test_guest_cannot_access_user_management(): void
    {
        $this->get(route('admin.users.index'))->assertRedirect('/login');
    }

    public function test_sort_columns_are_validated_before_querying(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->get(route('admin.users.index', ['sort' => 'name desc; drop table users']))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHasErrors('sort');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_user_management_indexes_exist_for_lookup_filter_and_cursor_order(): void
    {
        $indexNames = collect(Schema::getIndexes('users'))->pluck('name')->all();

        foreach ([
            'users_email_unique',
            'users_whatsapp_number_unique',
            'users_role_status_id_index',
            'users_role_id_index',
            'users_status_id_index',
            'users_name_id_index',
            'users_district_id_index',
            'users_created_at_id_index',
        ] as $indexName) {
            $this->assertContains($indexName, $indexNames);
        }

        $queryPlan = DB::select(
            'EXPLAIN QUERY PLAN SELECT id FROM users WHERE whatsapp_number = ?',
            ['919876543210'],
        );
        $planDetails = implode(' ', array_map(fn ($row) => $row->detail, $queryPlan));

        $this->assertStringContainsString('users_whatsapp_number_unique', $planDetails);
    }

    public function test_million_row_seeder_refuses_the_regular_test_database(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Scale data can only be seeded');

        (new ScaleTestUserSeeder)->run();
    }
}
