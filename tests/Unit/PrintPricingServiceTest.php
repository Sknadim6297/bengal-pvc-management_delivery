<?php

namespace Tests\Unit;

use App\Services\PrintPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrintPricingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_normal_quality_uses_source_bulk_prices_and_shipping_threshold(): void
    {
        $pricing = app(PrintPricingService::class);

        $one = $pricing->calculate('pvc-card', 'normal', 1);
        $two = $pricing->calculate('pvc-card', 'normal', 2);
        $six = $pricing->calculate('pvc-card', 'normal', 6);
        $eight = $pricing->calculate('pvc-card', 'normal', 8);
        $eleven = $pricing->calculate('pvc-card', 'normal', 11);

        $this->assertSame('120.00', $one['total_amount']);
        $this->assertSame('55.00', $two['unit_price']);
        $this->assertSame('50.00', $two['discount_amount']);
        $this->assertSame('48.00', $six['unit_price']);
        $this->assertSame('42.00', $eight['unit_price']);
        $this->assertSame('37.00', $eleven['unit_price']);
        $this->assertSame('0.00', $eleven['shipping_amount']);
    }

    public function test_premium_quality_and_custom_cover_use_active_database_prices(): void
    {
        $pricing = app(PrintPricingService::class);

        $eleven = $pricing->calculate('pvc-card', 'premium', 11);
        $twelve = $pricing->calculate('pvc-card', 'premium', 12);
        $covered = $pricing->calculate('pvc-card', 'normal', 3, true);

        $this->assertSame('90.00', $eleven['unit_price']);
        $this->assertSame('50.00', $twelve['unit_price']);
        $this->assertSame('45.00', $covered['cover_total']);
        $this->assertSame('250.00', $covered['total_amount']);
    }

    public function test_photo_pricing_uses_source_sizes_and_shipping(): void
    {
        $pricing = app(PrintPricingService::class);

        $standard = $pricing->calculate('photo-print', 'photo-4x6', 2);
        $large = $pricing->calculate('photo-print', 'photo-a4', 1);

        $this->assertSame('120.00', $standard['unit_price']);
        $this->assertSame('290.00', $standard['total_amount']);
        $this->assertSame('240.00', $large['unit_price']);
        $this->assertSame('290.00', $large['total_amount']);
    }
}