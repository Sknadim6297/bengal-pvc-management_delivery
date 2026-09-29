<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingTier extends Model
{
    protected $fillable = [
        'quality_id', 'min_quantity', 'max_quantity', 'base_unit_price', 'unit_price', 'discount_percent', 'enabled',
    ];

    protected function casts(): array
    {
        return [
            'min_quantity' => 'integer',
            'max_quantity' => 'integer',
            'base_unit_price' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'enabled' => 'boolean',
        ];
    }

    public function quality(): BelongsTo
    {
        return $this->belongsTo(PrintQuality::class, 'quality_id');
    }
}