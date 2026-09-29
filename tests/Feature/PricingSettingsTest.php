<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PricingSettings;
use App\Services\PrintPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PricingSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_pvc_card_print_uses_saved_pricing_values(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create();
        PricingSettings::remember();

        $values = [
            'normal_single_price' => 99,
            'normal_2_5_price' => 70,
            'normal_2_5_discount' => 25,
            'normal_6_7_price' => 60,
            'normal_6_7_discount' => 35,
            'normal_8_10_price' => 50,
            'normal_8_10_discount' => 45,
            'normal_11_plus_price' => 40,
            'normal_11_plus_discount' => 50,
            'premium_under_12_price' => 88,
            'premium_12_plus_price' => 49,
            'shipping_fee' => 45,
            'free_shipping_minimum_quantity' => 12,
            'card_cover_price' => 18,
        ];

        $this->actingAs($admin)
            ->from(route('admin.pricing.edit'))
            ->put(route('admin.pricing.update'), $values)
            ->assertRedirect(route('admin.pricing.edit'));

        $normalQualityId = DB::table('print_qualities')->where('slug', 'normal')->value('id');
        $premiumQualityId = DB::table('print_qualities')->where('slug', 'premium')->value('id');
        $this->assertDatabaseHas('pricing_tiers', [
            'quality_id' => $normalQualityId,
            'min_quantity' => 1,
            'unit_price' => '99.00',
        ]);
        $this->assertDatabaseHas('pricing_tiers', [
            'quality_id' => $normalQualityId,
            'min_quantity' => 2,
            'unit_price' => '70.00',
            'discount_percent' => '25.00',
        ]);
        $this->assertDatabaseHas('pricing_tiers', [
            'quality_id' => $premiumQualityId,
            'min_quantity' => 12,
            'unit_price' => '49.00',
        ]);
        $this->assertDatabaseHas('shipping_settings', ['flat_rate' => '45.00', 'free_shipping_threshold' => 12]);
        $this->assertDatabaseHas('card_cover_settings', ['price_per_card' => '18.00']);

        $this->actingAs($user)
            ->get(route('user.pvc-card-print'))
            ->assertOk()
            ->assertSee('₹99 / card')
            ->assertSee('25% OFF')
            ->assertSee('₹88')
            ->assertSee('₹49')
            ->assertSee('₹45 Flat')
            ->assertSee('Free on 12+ cards')
            ->assertSee('₹18/card');
    }

    public function test_pricing_settings_are_restricted_to_admins(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.pricing.edit'))
            ->assertForbidden();

        $this->actingAs($user)
            ->put(route('admin.pricing.update'), [])
            ->assertForbidden();
    }

    public function test_admin_pricing_editor_shows_configured_defaults(): void
    {
        PricingSettings::forget();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.pricing.edit'))
            ->assertOk()
            ->assertSee('PVC Pricing Settings')
            ->assertSee('value="77"', false)
            ->assertSee('value="55"', false)
            ->assertSee('value="90"', false)
            ->assertSee('value="40"', false);
    }

    public function test_invalid_discount_percentages_are_rejected(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $values = (array) PricingSettings::remember();
        $values['normal_2_5_discount'] = 101;

        $this->actingAs($admin)
            ->from(route('admin.pricing.edit'))
            ->put(route('admin.pricing.update'), $values)
            ->assertRedirect(route('admin.pricing.edit'))
            ->assertSessionHasErrors('normal_2_5_discount');
    }

    public function test_server_pricing_calculates_every_pvc_tier_shipping_and_cover(): void
    {
        $pricing = app(PrintPricingService::class);

        foreach ([1 => '77.00', 2 => '55.00', 6 => '48.00', 8 => '42.00', 11 => '37.00'] as $quantity => $unitPrice) {
            $this->assertSame($unitPrice, $pricing->calculate('pvc-card', 'normal', $quantity)['unit_price']);
        }

        $this->assertSame('40.00', $pricing->calculate('pvc-card', 'normal', 9)['shipping_amount']);
        $this->assertSame('0.00', $pricing->calculate('pvc-card', 'normal', 10)['shipping_amount']);
        $this->assertSame('90.00', $pricing->calculate('pvc-card', 'premium', 1)['unit_price']);
        $this->assertSame('90.00', $pricing->calculate('pvc-card', 'premium', 11)['unit_price']);
        $this->assertSame('50.00', $pricing->calculate('pvc-card', 'premium', 12)['unit_price']);

        $covered = $pricing->calculate('pvc-card', 'normal', 2, true);
        $this->assertSame('30.00', $covered['cover_total']);
        $this->assertSame('180.00', $covered['total_amount']);
    }
}