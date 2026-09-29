<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

    public function test_admin_dashboard_uses_database_aggregates(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        User::factory()->count(2)->create(['status' => User::STATUS_ACTIVE]);
        User::factory()->create(['status' => User::STATUS_SUSPENDED]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Total Accounts')
            ->assertSee('Registered Users')
            ->assertSee('Active Users')
            ->assertSee('4');
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

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->patch(route('admin.users.status', $user->id), ['status' => User::STATUS_SUSPENDED])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', ['id' => $user->id, 'status' => User::STATUS_SUSPENDED]);

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
            'users_role_status_index',
            'users_role_id_index',
            'users_status_id_index',
            'users_name_index',
            'users_created_at_index',
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
}
