<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureSchema();
        $this->seedMissingCatalogRows();
        $this->syncLegacyPricingSettings();
        $this->importLegacySubmissions();
    }

    private function ensureSchema(): void
    {
        if (! Schema::hasTable('print_services')) {
            Schema::create('print_services', function (Blueprint $table) {
                $table->id();
                $table->string('slug', 40)->unique();
                $table->string('name', 120);
                $table->boolean('enabled')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('print_qualities')) {
            Schema::create('print_qualities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->constrained('print_services')->cascadeOnDelete();
                $table->string('slug', 40);
                $table->string('name', 120);
                $table->string('description', 255)->nullable();
                $table->unsignedSmallInteger('display_order')->default(0);
                $table->boolean('enabled')->default(true);
                $table->timestamps();
                $table->unique(['service_id', 'slug']);
                $table->index(['service_id', 'enabled']);
            });
        }

        if (! Schema::hasTable('pricing_tiers')) {
            Schema::create('pricing_tiers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('quality_id')->constrained('print_qualities')->cascadeOnDelete();
                $table->unsignedInteger('min_quantity');
                $table->unsignedInteger('max_quantity')->nullable();
                $table->decimal('base_unit_price', 10, 2);
                $table->decimal('unit_price', 10, 2);
                $table->decimal('discount_percent', 5, 2)->default(0);
                $table->boolean('enabled')->default(true);
                $table->timestamps();
                $table->index(['quality_id', 'enabled', 'min_quantity', 'max_quantity'], 'pricing_tiers_lookup_index');
            });
        }

        if (! Schema::hasTable('shipping_settings')) {
            Schema::create('shipping_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->unique()->constrained('print_services')->cascadeOnDelete();
                $table->decimal('flat_rate', 10, 2);
                $table->unsignedInteger('free_shipping_threshold')->nullable();
                $table->boolean('enabled')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('card_cover_settings')) {
            Schema::create('card_cover_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_id')->unique()->constrained('print_services')->cascadeOnDelete();
                $table->decimal('price_per_card', 10, 2);
                $table->boolean('enabled')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->restrictOnDelete();
                $table->string('order_number', 32)->unique();
                $table->string('service_type', 40);
                $table->string('status', 32)->default('pending');
                $table->string('payment_status', 32)->default('pending');
                $table->unsignedInteger('quantity');
                $table->decimal('subtotal', 12, 2);
                $table->decimal('discount_amount', 12, 2)->default(0);
                $table->decimal('cover_amount', 12, 2)->default(0);
                $table->decimal('shipping_amount', 12, 2)->default(0);
                $table->decimal('coupon_discount', 12, 2)->default(0);
                $table->decimal('total_amount', 12, 2);
                $table->json('delivery_address')->nullable();
                $table->string('submission_key', 64)->nullable();
                $table->timestamps();
                $table->index(['user_id', 'created_at'], 'orders_user_created_index');
                $table->index(['user_id', 'status', 'created_at'], 'orders_user_status_created_index');
                $table->index(['service_type', 'status', 'created_at'], 'orders_service_status_created_index');
                $table->index(['payment_status', 'created_at'], 'orders_payment_created_index');
                $table->unique(['user_id', 'submission_key'], 'orders_user_submission_unique');
            });
        }

        if (! Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->string('quality_slug', 40);
                $table->string('quality_name', 120);
                $table->string('option_slug', 40)->nullable();
                $table->unsignedInteger('quantity');
                $table->decimal('base_unit_price', 10, 2);
                $table->decimal('unit_price', 10, 2);
                $table->decimal('discount_percent', 5, 2)->default(0);
                $table->decimal('discount_amount', 12, 2)->default(0);
                $table->decimal('subtotal', 12, 2);
                $table->boolean('cover_selected')->default(false);
                $table->decimal('cover_unit_price', 10, 2)->default(0);
                $table->decimal('cover_total', 12, 2)->default(0);
                $table->timestamps();
                $table->index('order_id');
            });
        }

        if (! Schema::hasTable('order_files')) {
            Schema::create('order_files', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->string('type', 16);
                $table->string('original_name')->nullable();
                $table->string('storage_path')->nullable();
                $table->text('drive_url')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->string('mime_type', 120)->nullable();
                $table->string('status', 24)->default('received');
                $table->timestamps();
                $table->index(['order_id', 'type']);
            });
        }

        if (! Schema::hasTable('order_status_history')) {
            Schema::create('order_status_history', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->string('old_status', 32)->nullable();
                $table->string('new_status', 32);
                $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('note')->nullable();
                $table->timestamps();
                $table->index(['order_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->string('gateway', 60)->nullable();
                $table->string('transaction_id', 120)->nullable();
                $table->decimal('amount', 12, 2);
                $table->string('status', 24)->default('pending');
                $table->timestamp('paid_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->index(['order_id', 'status']);
            });
        }
    }

    private function seedMissingCatalogRows(): void
    {
        $now = now();
        $serviceIds = [];

        foreach ([['pvc-card', 'PVC Card Print'], ['photo-print', 'Photo Print']] as [$slug, $name]) {
            $serviceIds[$slug] = DB::table('print_services')->where('slug', $slug)->value('id');
            if (! $serviceIds[$slug]) {
                $serviceIds[$slug] = DB::table('print_services')->insertGetId([
                    'slug' => $slug,
                    'name' => $name,
                    'enabled' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $qualities = [
            ['pvc-card', 'normal', 'Normal Quality', '800 Micron PVC. Bulk discounts + coupons.', 1],
            ['pvc-card', 'premium', 'Premium Quality', '800 Micron Premium PVC. Super Gloss. No discount. Long lasting.', 2],
            ['photo-print', 'photo-4x6', '4x6 Inch (Standard)', 'Lab Quality. Glossy. 300 GSM.', 1],
            ['photo-print', 'photo-a4', 'A4 Size (8x12)', 'Premium Photo Print. Large Format.', 2],
        ];
        $qualityIds = [];

        foreach ($qualities as [$serviceSlug, $slug, $name, $description, $displayOrder]) {
            $qualityIds[$slug] = DB::table('print_qualities')
                ->where('service_id', $serviceIds[$serviceSlug])
                ->where('slug', $slug)
                ->value('id');
            if (! $qualityIds[$slug]) {
                $qualityIds[$slug] = DB::table('print_qualities')->insertGetId([
                    'service_id' => $serviceIds[$serviceSlug],
                    'slug' => $slug,
                    'name' => $name,
                    'description' => $description,
                    'display_order' => $displayOrder,
                    'enabled' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $tiers = [
            ['normal', 1, 1, '77.00', '77.00', '0.00'],
            ['normal', 2, 5, '80.00', '55.00', '31.00'],
            ['normal', 6, 7, '80.00', '48.00', '40.00'],
            ['normal', 8, 10, '80.00', '42.00', '48.00'],
            ['normal', 11, null, '80.00', '37.00', '54.00'],
            ['premium', 1, 11, '90.00', '90.00', '0.00'],
            ['premium', 12, null, '50.00', '50.00', '0.00'],
            ['photo-4x6', 1, null, '120.00', '120.00', '0.00'],
            ['photo-a4', 1, null, '240.00', '240.00', '0.00'],
        ];

        foreach ($tiers as [$qualitySlug, $minimum, $maximum, $base, $unit, $discount]) {
            $exists = DB::table('pricing_tiers')
                ->where('quality_id', $qualityIds[$qualitySlug])
                ->where('min_quantity', $minimum)
                ->exists();
            if (! $exists) {
                DB::table('pricing_tiers')->insert([
                    'quality_id' => $qualityIds[$qualitySlug],
                    'min_quantity' => $minimum,
                    'max_quantity' => $maximum,
                    'base_unit_price' => $base,
                    'unit_price' => $unit,
                    'discount_percent' => $discount,
                    'enabled' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        foreach ([['pvc-card', '40.00', 10], ['photo-print', '50.00', null]] as [$slug, $flatRate, $threshold]) {
            DB::table('shipping_settings')->insertOrIgnore([
                'service_id' => $serviceIds[$slug],
                'flat_rate' => $flatRate,
                'free_shipping_threshold' => $threshold,
                'enabled' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('card_cover_settings')->insertOrIgnore([
            'service_id' => $serviceIds['pvc-card'],
            'price_per_card' => '15.00',
            'enabled' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function syncLegacyPricingSettings(): void
    {
        if (! Schema::hasTable('pricing_settings')) {
            return;
        }

        $settings = DB::table('pricing_settings')->where('id', 1)->first();
        $serviceId = DB::table('print_services')->where('slug', 'pvc-card')->value('id');
        $normalId = DB::table('print_qualities')->where('service_id', $serviceId)->where('slug', 'normal')->value('id');
        $premiumId = DB::table('print_qualities')->where('service_id', $serviceId)->where('slug', 'premium')->value('id');

        if (! $settings || ! $normalId || ! $premiumId) {
            return;
        }

        $now = now();
        $mappedTiers = [
            [$normalId, 1, 1, $settings->normal_single_price, $settings->normal_single_price, 0],
            [$normalId, 2, 5, 80, $settings->normal_2_5_price, $settings->normal_2_5_discount],
            [$normalId, 6, 7, 80, $settings->normal_6_7_price, $settings->normal_6_7_discount],
            [$normalId, 8, 10, 80, $settings->normal_8_10_price, $settings->normal_8_10_discount],
            [$normalId, 11, null, 80, $settings->normal_11_plus_price, $settings->normal_11_plus_discount],
            [$premiumId, 1, 11, $settings->premium_under_12_price, $settings->premium_under_12_price, 0],
            [$premiumId, 12, null, $settings->premium_12_plus_price, $settings->premium_12_plus_price, 0],
        ];

        foreach ($mappedTiers as [$qualityId, $minimum, $maximum, $base, $unit, $discount]) {
            DB::table('pricing_tiers')->updateOrInsert(
                ['quality_id' => $qualityId, 'min_quantity' => $minimum],
                [
                    'max_quantity' => $maximum,
                    'base_unit_price' => ((int) $base).'.00',
                    'unit_price' => ((int) $unit).'.00',
                    'discount_percent' => ((int) $discount).'.00',
                    'enabled' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }

        DB::table('shipping_settings')
            ->where('service_id', $serviceId)
            ->update([
                'flat_rate' => ((int) $settings->shipping_fee).'.00',
                'free_shipping_threshold' => $settings->free_shipping_minimum_quantity,
                'updated_at' => $now,
            ]);
        DB::table('card_cover_settings')
            ->where('service_id', $serviceId)
            ->update([
                'price_per_card' => ((int) $settings->card_cover_price).'.00',
                'updated_at' => $now,
            ]);
    }

    private function importLegacySubmissions(): void
    {
        if (! Schema::hasTable('pvc_print_submissions')) {
            return;
        }

        foreach (DB::table('pvc_print_submissions')->orderBy('id')->cursor() as $submission) {
            $submissionKey = 'legacy-pvc-submission-'.$submission->id;
            if (DB::table('orders')->where('user_id', $submission->user_id)->where('submission_key', $submissionKey)->exists()) {
                continue;
            }

            if (! DB::table('users')->where('id', $submission->user_id)->exists()) {
                continue;
            }

            $paths = json_decode($submission->pdf_paths, true) ?: [];
            $links = json_decode($submission->drive_links, true) ?: [];
            $quantity = count($paths) + count($links);
            if ($quantity < 1) {
                continue;
            }

            $qualitySlug = in_array($submission->print_quality, ['normal', 'premium'], true)
                ? $submission->print_quality
                : 'normal';
            $serviceId = DB::table('print_services')->where('slug', 'pvc-card')->value('id');
            $quality = DB::table('print_qualities')->where('service_id', $serviceId)->where('slug', $qualitySlug)->first();
            $tier = DB::table('pricing_tiers')
                ->where('quality_id', $quality->id)
                ->where('enabled', true)
                ->where('min_quantity', '<=', $quantity)
                ->where(fn ($query) => $query->whereNull('max_quantity')->orWhere('max_quantity', '>=', $quantity))
                ->orderByDesc('min_quantity')
                ->first();
            $shipping = DB::table('shipping_settings')->where('service_id', $serviceId)->first();
            $cover = DB::table('card_cover_settings')->where('service_id', $serviceId)->first();

            $baseUnit = $this->toPaise($tier->base_unit_price);
            $unit = $this->toPaise($tier->unit_price);
            $baseSubtotal = $baseUnit * $quantity;
            $cardsSubtotal = $unit * $quantity;
            $discount = max(0, $baseSubtotal - $cardsSubtotal);
            $coverSelected = (bool) $submission->include_card_cover;
            $coverUnit = $coverSelected ? $this->toPaise($cover->price_per_card) : 0;
            $coverTotal = $coverUnit * $quantity;
            $shippingAmount = $shipping->free_shipping_threshold !== null && $quantity >= $shipping->free_shipping_threshold
                ? 0
                : $this->toPaise($shipping->flat_rate);
            $createdAt = $submission->created_at ?? now();

            DB::transaction(function () use (
                $submission,
                $submissionKey,
                $quantity,
                $qualitySlug,
                $quality,
                $tier,
                $baseUnit,
                $unit,
                $baseSubtotal,
                $cardsSubtotal,
                $discount,
                $coverSelected,
                $coverUnit,
                $coverTotal,
                $shippingAmount,
                $createdAt,
                $paths,
                $links,
            ): void {
                $orderId = DB::table('orders')->insertGetId([
                    'user_id' => $submission->user_id,
                    'order_number' => 'TMP-'.Str::random(20),
                    'service_type' => 'pvc-card',
                    'status' => 'pending',
                    'payment_status' => 'pending',
                    'quantity' => $quantity,
                    'subtotal' => $this->fromPaise($baseSubtotal),
                    'discount_amount' => $this->fromPaise($discount),
                    'cover_amount' => $this->fromPaise($coverTotal),
                    'shipping_amount' => $this->fromPaise($shippingAmount),
                    'coupon_discount' => '0.00',
                    'total_amount' => $this->fromPaise($cardsSubtotal + $coverTotal + $shippingAmount),
                    'submission_key' => $submissionKey,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                DB::table('orders')->where('id', $orderId)->update([
                    'order_number' => 'PVC-'.date('Ymd', strtotime($createdAt)).'-'.str_pad((string) $orderId, 6, '0', STR_PAD_LEFT),
                ]);

                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'quality_slug' => $qualitySlug,
                    'quality_name' => $quality->name,
                    'quantity' => $quantity,
                    'base_unit_price' => $this->fromPaise($baseUnit),
                    'unit_price' => $this->fromPaise($unit),
                    'discount_percent' => $tier->discount_percent,
                    'discount_amount' => $this->fromPaise($discount),
                    'subtotal' => $this->fromPaise($cardsSubtotal),
                    'cover_selected' => $coverSelected,
                    'cover_unit_price' => $this->fromPaise($coverUnit),
                    'cover_total' => $this->fromPaise($coverTotal),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                foreach ($paths as $path) {
                    $exists = Storage::disk('local')->exists($path);
                    DB::table('order_files')->insert([
                        'order_id' => $orderId,
                        'type' => 'pdf',
                        'original_name' => basename(str_replace('\\', '/', $path)),
                        'storage_path' => $path,
                        'file_size' => $exists ? Storage::disk('local')->size($path) : null,
                        'mime_type' => 'application/pdf',
                        'status' => $exists ? 'received' : 'missing',
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }

                foreach ($links as $link) {
                    DB::table('order_files')->insert([
                        'order_id' => $orderId,
                        'type' => 'drive',
                        'drive_url' => $link,
                        'status' => 'received',
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);
                }

                DB::table('order_status_history')->insert([
                    'order_id' => $orderId,
                    'old_status' => null,
                    'new_status' => 'pending',
                    'changed_by' => $submission->user_id,
                    'note' => 'Imported from an earlier PVC file submission.',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            });
        }
    }

    private function toPaise(string|int $amount): int
    {
        [$rupees, $paise] = array_pad(explode('.', (string) $amount, 2), 2, '');
        return ((int) $rupees * 100) + (int) str_pad($paise, 2, '0', STR_PAD_LEFT);
    }

    private function fromPaise(int $amount): string
    {
        return intdiv($amount, 100).'.'.str_pad((string) ($amount % 100), 2, '0', STR_PAD_LEFT);
    }

    public function down(): void
    {
    }
};