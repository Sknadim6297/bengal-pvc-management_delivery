<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $pvcServiceId = DB::table('print_services')->insertGetId([
            'slug' => 'pvc-card', 'name' => 'PVC Card Print', 'enabled' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $photoServiceId = DB::table('print_services')->insertGetId([
            'slug' => 'photo-print', 'name' => 'Photo Print', 'enabled' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);

        $qualities = [
            'normal' => [$pvcServiceId, 'Normal Quality', '800 Micron PVC. Bulk discounts + coupons.'],
            'premium' => [$pvcServiceId, 'Premium Quality', '800 Micron Premium PVC. Super Gloss. No discount. Long lasting.'],
            'photo-4x6' => [$photoServiceId, '4x6 Inch (Standard)', 'Lab Quality. Glossy. 300 GSM.'],
            'photo-a4' => [$photoServiceId, 'A4 Size (8x12)', 'Premium Photo Print. Large Format.'],
        ];
        $qualityIds = [];

        foreach ($qualities as $slug => [$serviceId, $name, $description]) {
            $qualityIds[$slug] = DB::table('print_qualities')->insertGetId([
                'service_id' => $serviceId,
                'slug' => $slug,
                'name' => $name,
                'description' => $description,
                'enabled' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $tiers = [
            ['normal', 1, 1, '80.00', '80.00', '0.00'],
            ['normal', 2, 5, '80.00', '55.00', '31.00'],
            ['normal', 6, 7, '80.00', '48.00', '40.00'],
            ['normal', 8, 10, '80.00', '42.00', '48.00'],
            ['normal', 11, null, '80.00', '37.00', '54.00'],
            ['premium', 1, 11, '90.00', '90.00', '0.00'],
            ['premium', 12, null, '50.00', '50.00', '0.00'],
            ['photo-4x6', 1, null, '120.00', '120.00', '0.00'],
            ['photo-a4', 1, null, '240.00', '240.00', '0.00'],
        ];

        foreach ($tiers as [$quality, $min, $max, $basePrice, $unitPrice, $discount]) {
            DB::table('pricing_tiers')->insert([
                'quality_id' => $qualityIds[$quality],
                'min_quantity' => $min,
                'max_quantity' => $max,
                'base_unit_price' => $basePrice,
                'unit_price' => $unitPrice,
                'discount_percent' => $discount,
                'enabled' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('shipping_settings')->insert([
            ['service_id' => $pvcServiceId, 'flat_rate' => '40.00', 'free_shipping_threshold' => 10, 'enabled' => true, 'created_at' => $now, 'updated_at' => $now],
            ['service_id' => $photoServiceId, 'flat_rate' => '50.00', 'free_shipping_threshold' => null, 'enabled' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('card_cover_settings')->insert([
            'service_id' => $pvcServiceId,
            'price_per_card' => '15.00',
            'enabled' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('card_cover_settings')->delete();
        DB::table('shipping_settings')->delete();
        DB::table('pricing_tiers')->delete();
        DB::table('print_qualities')->delete();
        DB::table('print_services')->whereIn('slug', ['pvc-card', 'photo-print'])->delete();
    }
};