<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePricingSettingsRequest;
use App\Models\PricingTier;
use App\Models\PrintQuality;
use App\Models\PrintService;
use App\Services\PricingSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PricingSettingsController extends Controller
{
    public function edit(): View
    {
        $photoService = PrintService::query()
            ->where('slug', 'photo-print')
            ->with(['qualities' => fn ($query) => $query
                ->orderBy('display_order')
                ->orderBy('id')
                ->with(['pricingTiers' => fn ($tiers) => $tiers->where('min_quantity', 1)->orderBy('id')]), 'shippingSetting'])
            ->firstOrFail();

        $photoOptions = $photoService->qualities->map(fn (PrintQuality $quality): array => [
            'id' => $quality->id,
            'name' => $quality->name,
            'slug' => $quality->slug,
            'description' => $quality->description,
            'unit_price' => (string) ($quality->pricingTiers->first()?->unit_price ?? '0.00'),
            'enabled' => $quality->enabled,
            'display_order' => $quality->display_order,
        ])->values();

        return view('admin-panel.pricing.edit', [
            'pricing' => PricingSettings::remember(),
            'photoOptions' => $photoOptions,
            'photoShippingFee' => (string) ($photoService->shippingSetting?->flat_rate ?? '0.00'),
        ]);
    }

    public function update(UpdatePricingSettingsRequest $request): RedirectResponse
    {
        $values = $request->validated();

        DB::transaction(function () use ($values): void {
            $service = PrintService::query()->where('slug', 'pvc-card')->firstOrFail();
            $normal = PrintQuality::query()->where('service_id', $service->id)->where('slug', 'normal')->firstOrFail();
            $premium = PrintQuality::query()->where('service_id', $service->id)->where('slug', 'premium')->firstOrFail();
            $basePrice = PricingTier::query()
                ->where('quality_id', $normal->id)
                ->where('min_quantity', 2)
                ->value('base_unit_price');

            $tiers = [
                [$normal->id, 1, 1, $values['normal_single_price'], $values['normal_single_price'], 0],
                [$normal->id, 2, 5, $basePrice, $values['normal_2_5_price'], $values['normal_2_5_discount']],
                [$normal->id, 6, 7, $basePrice, $values['normal_6_7_price'], $values['normal_6_7_discount']],
                [$normal->id, 8, 10, $basePrice, $values['normal_8_10_price'], $values['normal_8_10_discount']],
                [$normal->id, 11, null, $basePrice, $values['normal_11_plus_price'], $values['normal_11_plus_discount']],
                [$premium->id, 1, 11, $values['premium_under_12_price'], $values['premium_under_12_price'], 0],
                [$premium->id, 12, null, $values['premium_12_plus_price'], $values['premium_12_plus_price'], 0],
            ];

            foreach ($tiers as [$qualityId, $minimum, $maximum, $base, $unit, $discount]) {
                PricingTier::query()->updateOrCreate(
                    ['quality_id' => $qualityId, 'min_quantity' => $minimum],
                    [
                        'max_quantity' => $maximum,
                        'base_unit_price' => (string) $base,
                        'unit_price' => (string) $unit.'.00',
                        'discount_percent' => (string) $discount.'.00',
                        'enabled' => true,
                    ],
                );
            }

            $service->shippingSetting()->updateOrCreate(
                ['service_id' => $service->id],
                [
                    'flat_rate' => (string) $values['shipping_fee'].'.00',
                    'free_shipping_threshold' => $values['free_shipping_minimum_quantity'],
                    'enabled' => true,
                ],
            );
            $service->coverSetting()->updateOrCreate(
                ['service_id' => $service->id],
                [
                    'price_per_card' => (string) $values['card_cover_price'].'.00',
                    'enabled' => true,
                ],
            );

            if (array_key_exists('photo_options', $values)) {
                $photoService = PrintService::query()->where('slug', 'photo-print')->firstOrFail();
                $seenSlugs = [];

                foreach ($values['photo_options'] as $optionValues) {
                    $slug = $optionValues['slug'];
                    if (isset($seenSlugs[$slug])) {
                        throw ValidationException::withMessages(['photo_options' => 'Photo option slugs must be unique.']);
                    }
                    $seenSlugs[$slug] = true;

                    $existingSlug = PrintQuality::query()
                        ->where('service_id', $photoService->id)
                        ->where('slug', $slug)
                        ->when(! empty($optionValues['id']), fn ($query) => $query->where('id', '!=', $optionValues['id']))
                        ->exists();
                    if ($existingSlug) {
                        throw ValidationException::withMessages(['photo_options' => 'A photo option already uses that slug.']);
                    }

                    $option = ! empty($optionValues['id'])
                        ? PrintQuality::query()->where('service_id', $photoService->id)->findOrFail($optionValues['id'])
                        : new PrintQuality(['service_id' => $photoService->id]);
                    $option->name = $optionValues['name'];
                    $option->slug = $slug;
                    $option->description = $optionValues['description'] ?? null;
                    $option->enabled = (bool) $optionValues['enabled'];
                    $option->display_order = (int) ($optionValues['display_order'] ?? 0);
                    $option->save();

                    $tier = PricingTier::query()->firstOrNew([
                        'quality_id' => $option->id,
                        'min_quantity' => 1,
                    ]);
                    $tier->max_quantity = null;
                    $tier->base_unit_price = $optionValues['unit_price'];
                    $tier->unit_price = $optionValues['unit_price'];
                    $tier->discount_percent = '0.00';
                    $tier->enabled = (bool) $optionValues['enabled'];
                    $tier->save();
                }
            }

            if (array_key_exists('photo_shipping_fee', $values)) {
                $photoService = PrintService::query()->where('slug', 'photo-print')->firstOrFail();
                $photoService->shippingSetting()->updateOrCreate(
                    ['service_id' => $photoService->id],
                    [
                        'flat_rate' => $values['photo_shipping_fee'],
                        'free_shipping_threshold' => null,
                        'enabled' => true,
                    ],
                );
            }
        });

        PricingSettings::forget();

        return back()->with('statusMessage', 'PVC pricing updated.');
    }
}