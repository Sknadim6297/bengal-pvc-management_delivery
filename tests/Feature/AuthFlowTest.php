<?php

namespace Tests\Feature;

use Database\Seeders\AdminUserSeeder;
use App\Models\User;
use App\Services\AdminDashboardStatistics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_the_home_page(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee(asset('assets/style.css'), false)
            ->assertSee(asset('assets/img/pvc_logo.png'), false)
            ->assertSee(route('login'), false)
            ->assertSee(route('register'), false);
    }

    public function test_login_and_register_pages_render_the_existing_auth_design(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('login-card')
            ->assertSee('WhatsApp or Email')
            ->assertSee('Login to Dashboard')
            ->assertSee('name="login"', false)
            ->assertSee('method="POST"', false)
            ->assertSee('type="text"', false)
            ->assertSee(asset('assets/style.css'), false)
            ->assertSee(asset('assets/img/pvc_logo.png'), false);

        $this->get('/register')
            ->assertOk()
            ->assertSee('login-card')
            ->assertSee('Full Name')
            ->assertSee('WhatsApp Number')
            ->assertSee('Email Address')
            ->assertSee('Select District')
            ->assertSee('Create Secure Password')
            ->assertSee('name="whatsapp_number"', false)
            ->assertSee('name="district"', false)
            ->assertSee('name="password" type="password"', false)
            ->assertSee('method="POST"', false)
            ->assertDontSee('name="role"', false)
            ->assertDontSee('password_confirmation')
            ->assertSee(asset('assets/style.css'), false)
            ->assertSee(asset('assets/img/pvc_logo.png'), false);
    }

    public function test_all_html_user_sidebar_pages_render_for_an_authenticated_user(): void
    {
        $user = User::factory()->create();
        $routes = [
            'dashboard',
            'user.pvc-card-print',
            'user.photo-print',
            'user.recover-failed-order',
            'user.order-history',
            'user.track-help',
            'user.security',
        ];

        foreach ($routes as $route) {
            $this->actingAs($user)
                ->get(route($route))
                ->assertOk()
                ->assertSee('sidebar-menu')
                ->assertSee(asset('assets/style.css'), false)
                ->assertSee(asset('assets/img/pvc_logo.png'), false);
        }

        $this->get(route('user.pvc-card-print'))
            ->assertSee('Indias <span>PVC</span>', false);

        $this->get(route('dashboard'))
            ->assertSeeInOrder([
                'Dashboard',
                'PVC Card Print',
                'Photo Print',
                'Recover Failed Order',
                'Order History',
                'Track &amp; Help',
                'Security Settings',
            ], false);
    }

    public function test_user_can_register_and_is_redirected_to_dashboard(): void
    {
        $response = $this->post('/register', [
            'name' => 'Jane User',
            'whatsapp_number' => '9876543210',
            'email' => 'jane@example.com',
            'district' => 'Nadia',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'role' => 'user',
        ]);
        $this->assertAuthenticated();
        $this->assertTrue(password_verify('Password123!', User::where('email', 'jane@example.com')->value('password')));
    }

    public function test_registration_accepts_html_fields_without_confirmation_and_ignores_privileged_role(): void
    {
        Cache::put(AdminDashboardStatistics::CACHE_KEY, (object) ['total_accounts' => 0], now()->addMinute());

        $this->post('/register', [
            'name' => 'Jane User',
            'whatsapp_number' => '+91 98765 43210',
            'email' => 'JANE@example.com',
            'district' => 'Nadia',
            'password' => 'SecurePass123!',
            'role' => 'admin',
        ])->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', [
            'name' => 'Jane User',
            'email' => 'jane@example.com',
            'whatsapp_number' => '919876543210',
            'district' => 'Nadia',
            'role' => 'user',
            'status' => 'active',
        ]);

        $storedPassword = User::where('email', 'jane@example.com')->value('password');
        $this->assertNotSame('SecurePass123!', $storedPassword);
        $this->assertTrue(password_verify('SecurePass123!', $storedPassword));
        $this->assertFalse(Cache::has(AdminDashboardStatistics::CACHE_KEY));
    }

    public function test_registration_rejects_duplicate_emails_and_admin_role_input(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $this->from('/register')->post('/register', [
            'name' => 'Attacker',
            'whatsapp_number' => '9876543210',
            'email' => 'existing@example.com',
            'district' => 'Nadia',
            'password' => 'Password123!',
            'role' => 'admin',
        ])->assertRedirect('/register')->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('users', ['email' => 'attacker@example.com']);

        $this->post('/register', [
            'name' => 'Attacker',
            'whatsapp_number' => '9876543211',
            'email' => 'attacker@example.com',
            'district' => 'Nadia',
            'password' => 'Password123!',
            'role' => 'admin',
        ])->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', [
            'email' => 'attacker@example.com',
            'role' => 'user',
        ]);
    }

    public function test_registration_rejects_duplicate_whatsapp_numbers(): void
    {
        User::factory()->create(['whatsapp_number' => '919876543210']);

        $this->from('/register')->post('/register', [
            'name' => 'Duplicate Phone',
            'whatsapp_number' => '+91 98765 43210',
            'email' => 'duplicate-phone@example.com',
            'district' => 'Nadia',
            'password' => 'Password123!',
        ])->assertRedirect('/register')->assertSessionHasErrors('whatsapp_number');

        $this->assertDatabaseMissing('users', ['email' => 'duplicate-phone@example.com']);
    }

    public function test_invalid_login_is_rejected_with_validation_errors(): void
    {
        $this->from('/login')->post('/login', [
            'login' => 'not-an-email',
            'password' => '',
        ])->assertRedirect('/login')
            ->assertSessionHasErrors(['login', 'password']);

        $this->from('/login')->post('/login', [
            'login' => 'missing@example.com',
            'password' => 'incorrect',
        ])->assertRedirect('/login')->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_user_can_login_with_a_normalized_whatsapp_number(): void
    {
        $user = User::factory()->create([
            'email' => 'phone-user@example.com',
            'whatsapp_number' => '919876543210',
            'password' => bcrypt('Password123!'),
        ]);

        $this->post('/login', [
            'login' => '+91 98765 43210',
            'password' => 'Password123!',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_accounts_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'suspended@example.com',
            'status' => User::STATUS_SUSPENDED,
            'password' => bcrypt('Password123!'),
        ]);

        $this->from('/login')->post('/login', [
            'login' => 'suspended@example.com',
            'password' => 'Password123!',
        ])->assertRedirect('/login')->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_suspended_account_is_logged_out_on_its_next_request(): void
    {
        $user = User::factory()->create([
            'email' => 'suspend-during-session@example.com',
            'password' => bcrypt('Password123!'),
        ]);

        $this->post('/login', [
            'login' => 'suspend-during-session@example.com',
            'password' => 'Password123!',
        ])->assertRedirect('/dashboard');

        User::whereKey($user->id)->update(['status' => User::STATUS_SUSPENDED]);
        $this->app['auth']->forgetGuards();

        $this->get('/dashboard')
            ->assertRedirect('/login')
            ->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->from('/login')->post('/login', [
                'login' => 'throttle@example.com',
                'password' => 'wrong-password',
            ])->assertRedirect('/login');
        }

        $this->post('/login', [
            'login' => 'throttle@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }

    public function test_registration_attempts_are_rate_limited_by_ip(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->from('/register')->post('/register', [])->assertRedirect('/register');
        }

        $this->post('/register', [])->assertStatus(429);
    }

    public function test_session_cookie_defaults_are_http_only_and_same_site_lax(): void
    {
        $this->assertTrue(config('session.http_only'));
        $this->assertSame('lax', config('session.same_site'));
        $this->assertFalse((bool) config('session.secure'));
    }

    public function test_response_has_baseline_security_headers(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'whatsapp_number' => '919876543210',
            'password' => bcrypt('Password123!'),
        ]);

        $this->withSession(['private_marker' => 'clear-after-logout']);
        $this->post('/login', [
            'login' => 'user@example.com',
            'password' => 'Password123!',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('method="POST" action="'.route('logout').'"', false)
            ->assertSee('name="_token"', false)
            ->assertSee(route('user.pvc-card-print'), false);

        $logoutSessionId = session()->getId();
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
        $this->assertNotSame($logoutSessionId, session()->getId());
        $this->assertFalse(session()->has('private_marker'));
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/logout')->assertStatus(405);
    }

    public function test_sequential_users_only_see_their_own_dashboard_identity_and_statistics(): void
    {
        $userA = User::factory()->create([
            'name' => 'Account Alpha',
            'email' => 'alpha@example.com',
            'password' => bcrypt('Password123!'),
        ]);
        $userB = User::factory()->create([
            'name' => 'Account Beta',
            'email' => 'beta@example.com',
            'password' => bcrypt('Password123!'),
        ]);

        $this->post('/login', [
            'login' => $userA->email,
            'password' => 'Password123!',
        ])->assertRedirect('/dashboard');

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Welcome, <span>Account Alpha!</span>', false)
            ->assertSee('Account Alpha')
            ->assertDontSee('Account Beta')
            ->assertSee('No live orders yet')
            ->assertSee(route('user.pvc-card-print'), false)
            ->assertViewHas('statistics', fn (object $statistics): bool => $statistics->user_id === $userA->id
                && $statistics->processing_cards === 0
                && $statistics->delivered_cards === 0);

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();

        $this->post('/login', [
            'login' => $userB->email,
            'password' => 'Password123!',
        ])->assertRedirect('/dashboard');

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Welcome, <span>Account Beta!</span>', false)
            ->assertSee('Account Beta')
            ->assertDontSee('Account Alpha')
            ->assertViewHas('statistics', fn (object $statistics): bool => $statistics->user_id === $userB->id
                && $statistics->processing_cards === 0
                && $statistics->delivered_cards === 0);
    }

    public function test_production_pages_do_not_render_demo_order_names_or_placeholder_whatsapp_urls(): void
    {
        foreach (['/', '/login', '/register'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertDontSee('Dipen Samanta')
                ->assertDontSee('Amit Das')
                ->assertDontSee('Sujoy Roy')
                ->assertDontSee('RAKSHAKAR DAS')
                ->assertDontSee('https://wa.me/919XXXXXXXXX');
        }

        $this->get('/')
            ->assertOk()
            ->assertSee('No live orders yet')
            ->assertDontSee('DEBASISH SAU')
            ->assertDontSee('MD SANOWAR HOSSAIN PAIK')
            ->assertDontSee('NAZIMUL ISLAM')
            ->assertDontSee('Sinarul Saikh');

        $user = User::factory()->create();
        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Dipen Samanta')
            ->assertDontSee('Amit Das')
            ->assertDontSee('Sujoy Roy')
            ->assertDontSee('https://wa.me/919XXXXXXXXX')
            ->assertSee('Join Whatsapp Channel')
            ->assertSee('disabled', false);
    }

    public function test_whatsapp_channel_action_uses_its_configured_url(): void
    {
        Config::set('services.whatsapp_channel_url', 'https://whatsapp.com/channel/test-channel');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('href="https://whatsapp.com/channel/test-channel"', false)
            ->assertSee('Join Whatsapp Channel');
    }

    public function test_login_regenerates_the_session_identifier(): void
    {
        User::factory()->create([
            'email' => 'session-user@example.com',
            'password' => bcrypt('Password123!'),
        ]);

        $session = $this->app['session']->driver();
        $oldSessionId = $session->getId();

        $this->post('/login', [
            'login' => 'session-user@example.com',
            'password' => 'Password123!',
        ])->assertRedirect('/dashboard');

        $this->assertNotSame($oldSessionId, $session->getId());
    }

    public function test_admin_can_login_and_access_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'status' => 'active',
            'password' => bcrypt('Password123!'),
        ]);

        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/admin/login')->assertOk()->assertSee('WhatsApp or Email');

        $response = $this->post('/login', [
            'login' => 'admin@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($admin);
        $this->get('/admin/dashboard')->assertOk();
    }

    public function test_admin_dashboard_uses_database_aggregates(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        User::factory()->count(2)->create(['status' => User::STATUS_ACTIVE]);
        User::factory()->create(['status' => User::STATUS_SUSPENDED]);
        Cache::forget(AdminDashboardStatistics::CACHE_KEY);

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Total Users')
            ->assertSee('Active Users')
            ->assertSee('Suspended Users')
            ->assertSee('Recent Orders')
            ->assertSee('Recent Users')
            ->assertViewHas('statistics', fn (object $statistics): bool => $statistics->total_users === 3
                && $statistics->active_users === 2
                && $statistics->suspended_users === 1);
    }

    public function test_admin_seeder_is_configured_hashed_and_idempotent(): void
    {
        Config::set([
            'auth.admin.name' => 'Seeded Admin',
            'auth.admin.email' => 'seeded-admin@example.com',
            'auth.admin.password' => 'SeededAdmin123!',
        ]);

        $this->seed(AdminUserSeeder::class);
        $this->seed(AdminUserSeeder::class);

        $this->assertDatabaseCount('users', 1);
        $admin = User::where('email', 'seeded-admin@example.com')->firstOrFail();
        $this->assertSame(User::ROLE_ADMIN, $admin->role);
        $this->assertSame(User::STATUS_ACTIVE, $admin->status);
        $this->assertTrue(Hash::check('SeededAdmin123!', $admin->password));
    }

    public function test_user_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get('/admin/dashboard')
            ->assertStatus(403);
    }

    public function test_guest_is_redirected_from_protected_dashboards_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/admin/dashboard')->assertRedirect('/login');
    }
}
