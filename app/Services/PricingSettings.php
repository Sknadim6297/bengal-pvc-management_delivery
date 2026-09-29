<?php

namespace App\Services;

use App\Models\PricingTier;
use Illuminate\Support\Facades\Cache;

class PricingSettings
{
    public const CACHE_KEY = 'pricing.catalog.settings.v2';

    public static function remember(): object
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(5), static function (): object {
            $service = app(PrintCatalogService::class)->findEnabled('pvc-card');
            $qualities = $service->qualities->keyBy('slug');
            $normalTiers = $qualities->get('normal')->pricingTiers->keyBy('min_quantity');
            $premiumTiers = $qualities->get('premium')->pricingTiers->keyBy('min_quantity');
            $shipping = $service->shippingSetting;
            $cover = $service->coverSetting;

            return (object) [
                'normal_single_price' => (int) $normalTiers->get(1)->unit_price,
                'normal_2_5_price' => (int) $normalTiers->get(2)->unit_price,
                'normal_2_5_discount' => (int) $normalTiers->get(2)->discount_percent,
                'normal_6_7_price' => (int) $normalTiers->get(6)->unit_price,
                'normal_6_7_discount' => (int) $normalTiers->get(6)->discount_percent,
                'normal_8_10_price' => (int) $normalTiers->get(8)->unit_price,
                'normal_8_10_discount' => (int) $normalTiers->get(8)->discount_percent,
                'normal_11_plus_price' => (int) $normalTiers->get(11)->unit_price,
                'normal_11_plus_discount' => (int) $normalTiers->get(11)->discount_percent,
                'premium_under_12_price' => (int) $premiumTiers->get(1)->unit_price,
                'premium_12_plus_price' => (int) $premiumTiers->get(12)->unit_price,
                'shipping_fee' => (int) $shipping->flat_rate,
                'free_shipping_minimum_quantity' => (int) $shipping->free_shipping_threshold,
                'card_cover_price' => (int) $cover->price_per_card,
            ];
        });
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}