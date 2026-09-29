<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderFile extends Model
{
    protected $fillable = [
        'type', 'original_name', 'storage_path', 'drive_url', 'file_size', 'mime_type', 'status',
    ];

    protected function casts(): array
    {
        return ['file_size' => 'integer'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}