<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingSetting extends Model
{
    protected $fillable = ['service_id', 'flat_rate', 'free_shipping_threshold', 'enabled'];

    protected function casts(): array
    {
        return [
            'flat_rate' => 'decimal:2',
            'free_shipping_threshold' => 'integer',
            'enabled' => 'boolean',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(PrintService::class, 'service_id');
    }
}