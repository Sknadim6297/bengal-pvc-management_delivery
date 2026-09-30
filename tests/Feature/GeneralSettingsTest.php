<?php

namespace Tests\Feature;

use App\Models\GeneralSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GeneralSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_general_settings_are_available_only_to_admins(): void
    {
        $this->get(route('admin.general-settings.edit'))->assertRedirect(route('login'));

        $user = User::factory()->create();
        $this->actingAs($user)
            ->get(route('admin.general-settings.edit'))
            ->assertForbidden();
        $this->put(route('admin.general-settings.update'), $this->validSettings())
            ->assertForbidden();

        $this->actingAs(User::factory()->create(['role' => User::ROLE_ADMIN]))
            ->get(route('admin.general-settings.edit'))
            ->assertOk()
            ->assertSee('General Settings')
            ->assertSee('name="_token"', false)
            ->assertSee('name="_method" value="PUT"', false);
    }

    public function test_admin_can_update_application_name_and_helpline_without_changing_account_data(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'name' => 'Original Admin']);

        $this->actingAs($admin)
            ->from(route('admin.general-settings.edit'))
            ->put(route('admin.general-settings.update'), $this->validSettings([
                'application_name' => 'New PVC House',
                'helpline_number' => '+91 9876543210',
                'whatsapp_contact_url' => 'https://wa.me/919876543210',
            ]))
            ->assertRedirect(route('admin.general-settings.edit'))
            ->assertSessionHas('toast_success', 'General settings updated successfully.');

        $this->assertDatabaseHas('general_settings', [
            'id' => 1,
            'application_name' => 'New PVC House',
            'helpline_number' => '+91 9876543210',
            'whatsapp_contact_url' => 'https://wa.me/919876543210',
        ]);
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'name' => 'Original Admin', 'role' => User::ROLE_ADMIN]);
    }

    public function test_admin_can_upload_logo_and_favicon_images(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->put(route('admin.general-settings.update'), $this->validSettings([
                'logo' => UploadedFile::fake()->image('logo.png', 400, 200),
                'favicon' => UploadedFile::fake()->image('favicon.png', 64, 64),
            ]))
            ->assertRedirect()
            ->assertSessionHas('toast_success');

        $settings = GeneralSetting::query()->findOrFail(1);
        $this->assertStringStartsWith('general-settings/', $settings->logo_path);
        $this->assertStringStartsWith('general-settings/', $settings->favicon_path);
        $this->assertTrue(Storage::disk('public')->exists($settings->logo_path));
        $this->assertTrue(Storage::disk('public')->exists($settings->favicon_path));
    }

    public function test_invalid_image_upload_is_rejected_without_changing_settings(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $settings = GeneralSetting::query()->findOrFail(1);
        $originalName = $settings->application_name;

        $this->actingAs($admin)
            ->from(route('admin.general-settings.edit'))
            ->put(route('admin.general-settings.update'), $this->validSettings([
                'application_name' => 'Must Not Save',
                'logo' => UploadedFile::fake()->create('malware.php', 20, 'application/x-php'),
            ]))
            ->assertRedirect(route('admin.general-settings.edit'))
            ->assertSessionHas('toast_error');

        $this->assertDatabaseHas('general_settings', ['id' => 1, 'application_name' => $originalName]);
        $this->assertSame([], Storage::disk('public')->allFiles('general-settings'));
    }

    public function test_omitting_a_new_logo_preserves_the_existing_logo(): void
    {
        Storage::fake('public');
        $path = UploadedFile::fake()->image('existing.png')->store('general-settings', 'public');
        $settings = GeneralSetting::query()->findOrFail(1);
        $settings->logo_path = $path;
        $settings->save();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->put(route('admin.general-settings.update'), $this->validSettings(['application_name' => 'Updated Name']))
            ->assertRedirect();

        $this->assertDatabaseHas('general_settings', ['id' => 1, 'logo_path' => $path]);
        $this->assertTrue(Storage::disk('public')->exists($path));
    }

    public function test_malformed_array_input_returns_one_validation_toast_and_does_not_partially_save(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $settings = GeneralSetting::query()->findOrFail(1);
        $originalHelpline = $settings->helpline_number;

        $this->actingAs($admin)
            ->from(route('admin.general-settings.edit'))
            ->put(route('admin.general-settings.update'), $this->validSettings([
                'application_name' => ['malformed'],
                'helpline_number' => '+91 9999999999',
            ]))
            ->assertRedirect(route('admin.general-settings.edit'))
            ->assertSessionHas('toast_error')
            ->assertSessionMissing('_errors');

        $this->assertDatabaseHas('general_settings', [
            'id' => 1,
            'application_name' => 'India PVC',
            'helpline_number' => $originalHelpline,
        ]);
    }

    public function test_web_csrf_middleware_is_applied_to_settings_updates(): void
    {
        $route = Route::getRoutes()->getByName('admin.general-settings.update');

        $this->assertNotNull($route);
        $this->assertContains('web', $route->gatherMiddleware());
        $this->assertSame(['PUT'], $route->methods());
    }

    public function test_user_panel_renders_updated_application_name_logo_and_helpline(): void
    {
        Storage::fake('public');
        $logoPath = 'general-settings/updated-logo.png';
        $faviconPath = 'general-settings/updated-favicon.png';
        Storage::disk('public')->put($logoPath, UploadedFile::fake()->image('logo.png')->get());
        Storage::disk('public')->put($faviconPath, UploadedFile::fake()->image('favicon.png')->get());

        $settings = GeneralSetting::query()->findOrFail(1);
        $settings->application_name = 'Configured PVC';
        $settings->brand_name = 'Configured Brand';
        $settings->logo_path = $logoPath;
        $settings->favicon_path = $faviconPath;
        $settings->helpline_number = '+91 9000000000';
        $settings->whatsapp_contact_url = 'https://wa.me/919000000000';
        $settings->save();

        $user = User::factory()->create(['name' => 'Account Holder']);
        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('<title>Configured PVC</title>', false)
            ->assertSee('Configured Brand', false)
            ->assertSee(asset('storage/'.$logoPath), false)
            ->assertSee(asset('storage/'.$faviconPath), false)
            ->assertSee('Helpline: +91 9000000000')
            ->assertSee('https://wa.me/919000000000', false)
            ->assertSee('Account Holder');
    }

    private function validSettings(array $overrides = []): array
    {
        return array_replace([
            'application_name' => 'India PVC',
            'brand_name' => 'Bengal PVC',
            'helpline_number' => '+91 8900162634',
            'whatsapp_contact_url' => '',
        ], $overrides);
    }
}