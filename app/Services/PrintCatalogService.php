<?php

namespace App\Services;

use App\Models\PrintService;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrintCatalogService
{
    public function findEnabled(string $slug): PrintService
    {
        return PrintService::query()
            ->where('slug', $slug)
            ->where('enabled', true)
            ->with([
                'qualities' => fn (HasMany $query) => $query
                    ->where('enabled', true)
                    ->with(['pricingTiers' => fn (HasMany $tiers) => $tiers
                        ->where('enabled', true)
                        ->orderBy('min_quantity')]),
                'shippingSetting',
                'coverSetting',
            ])
            ->firstOrFail();
    }
}