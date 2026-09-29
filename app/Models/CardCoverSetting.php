<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardCoverSetting extends Model
{
    protected $fillable = ['service_id', 'price_per_card', 'enabled'];

    protected function casts(): array
    {
        return ['price_per_card' => 'decimal:2', 'enabled' => 'boolean'];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(PrintService::class, 'service_id');
    }
}