<?php

namespace App\Services;

use App\Models\PrintQuality;
use App\Models\PrintService;
use Illuminate\Validation\ValidationException;

class PrintPricingService
{
    public function calculate(string $serviceSlug, string $qualitySlug, int $quantity, bool $coverSelected = false): array
    {
        if ($quantity < 1) {
            throw ValidationException::withMessages(['files' => 'At least one uploaded file or Drive link is required.']);
        }

        $service = PrintService::query()
            ->where('slug', $serviceSlug)
            ->where('enabled', true)
            ->with(['shippingSetting', 'coverSetting'])
            ->firstOrFail();
        $quality = PrintQuality::query()
            ->where('service_id', $service->id)
            ->where('slug', $qualitySlug)
            ->where('enabled', true)
            ->firstOrFail();
        $tier = $quality->pricingTiers()
            ->where('enabled', true)
            ->where('min_quantity', '<=', $quantity)
            ->where(fn ($query) => $query->whereNull('max_quantity')->orWhere('max_quantity', '>=', $quantity))
            ->orderByDesc('min_quantity')
            ->first();

        if (! $tier) {
            throw ValidationException::withMessages(['print_quality' => 'Pricing is not available for that quantity.']);
        }

        $baseUnit = self::toPaise($tier->base_unit_price);
        $unit = self::toPaise($tier->unit_price);
        $baseSubtotal = $baseUnit * $quantity;
        $cardsSubtotal = $unit * $quantity;
        $discount = max(0, $baseSubtotal - $cardsSubtotal);
        $coverUnit = 0;

        if ($coverSelected) {
            if ($serviceSlug !== 'pvc-card' || ! $service->coverSetting?->enabled) {
                throw ValidationException::withMessages(['include_card_cover' => 'Custom printed covers are unavailable.']);
            }

            $coverUnit = self::toPaise($service->coverSetting->price_per_card);
        }

        $coverTotal = $coverUnit * $quantity;
        $shipping = $service->shippingSetting;

        if (! $shipping || ! $shipping->enabled) {
            throw ValidationException::withMessages(['service' => 'Shipping is not configured for this service.']);
        }

        $shippingAmount = $shipping->free_shipping_threshold !== null
            && $quantity >= $shipping->free_shipping_threshold
                ? 0
                : self::toPaise($shipping->flat_rate);
        $total = $cardsSubtotal + $coverTotal + $shippingAmount;

        return [
            'service_id' => $service->id,
            'service_type' => $service->slug,
            'quality_id' => $quality->id,
            'quality_slug' => $quality->slug,
            'quality_name' => $quality->name,
            'quantity' => $quantity,
            'base_unit_price' => self::fromPaise($baseUnit),
            'unit_price' => self::fromPaise($unit),
            'discount_percent' => (string) $tier->discount_percent,
            'subtotal' => self::fromPaise($baseSubtotal),
            'discount_amount' => self::fromPaise($discount),
            'cards_subtotal' => self::fromPaise($cardsSubtotal),
            'cover_selected' => $coverSelected,
            'cover_unit_price' => self::fromPaise($coverUnit),
            'cover_total' => self::fromPaise($coverTotal),
            'shipping_amount' => self::fromPaise($shippingAmount),
            'coupon_discount' => '0.00',
            'total_amount' => self::fromPaise($total),
        ];
    }

    public static function toPaise(string|int $amount): int
    {
        $amount = trim((string) $amount);
        if (! preg_match('/^\d+(?:\.\d{1,2})?$/', $amount)) {
            throw new \InvalidArgumentException('Money values must be non-negative with at most two decimals.');
        }

        [$rupees, $paise] = array_pad(explode('.', $amount, 2), 2, '');
        return ((int) $rupees * 100) + (int) str_pad($paise, 2, '0');
    }

    public static function fromPaise(int $amount): string
    {
        return intdiv($amount, 100).'.'.str_pad((string) ($amount % 100), 2, '0', STR_PAD_LEFT);
    }
}