<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PrintService extends Model
{
    protected $fillable = ['slug', 'name', 'enabled'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }

    public function qualities(): HasMany
    {
        return $this->hasMany(PrintQuality::class, 'service_id');
    }

    public function shippingSetting(): HasOne
    {
        return $this->hasOne(ShippingSetting::class, 'service_id');
    }

    public function coverSetting(): HasOne
    {
        return $this->hasOne(CardCoverSetting::class, 'service_id');
    }
}