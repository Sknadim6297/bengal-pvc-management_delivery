<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'quality_slug',
        'quality_name',
        'option_slug',
        'quantity',
        'base_unit_price',
        'unit_price',
        'discount_percent',
        'discount_amount',
        'subtotal',
        'cover_selected',
        'cover_unit_price',
        'cover_total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'base_unit_price' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'cover_selected' => 'boolean',
            'cover_unit_price' => 'decimal:2',
            'cover_total' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}