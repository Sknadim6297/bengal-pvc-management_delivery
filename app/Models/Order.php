<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAYMENT_PENDING = 'payment_pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_PRINTING = 'printing';
    public const STATUS_PACKED = 'packed';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PAYMENT_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_PROCESSING,
        self::STATUS_PRINTING,
        self::STATUS_PACKED,
        self::STATUS_SHIPPED,
        self::STATUS_OUT_FOR_DELIVERY,
        self::STATUS_DELIVERED,
        self::STATUS_FAILED,
        self::STATUS_CANCELLED,
    ];

    public const ACTIVE_CARD_STATUSES = [
        self::STATUS_CONFIRMED,
        self::STATUS_PROCESSING,
        self::STATUS_PRINTING,
        self::STATUS_PACKED,
        self::STATUS_SHIPPED,
        self::STATUS_OUT_FOR_DELIVERY,
    ];

    protected $fillable = [
        'order_number',
        'service_type',
        'status',
        'payment_status',
        'quantity',
        'subtotal',
        'discount_amount',
        'cover_amount',
        'shipping_amount',
        'coupon_discount',
        'total_amount',
        'delivery_address',
        'submission_key',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'cover_amount' => 'decimal:2',
            'shipping_amount' => 'decimal:2',
            'coupon_discount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'delivery_address' => 'array',
        ];
    }

    public static function allowedTransitions(): array
    {
        return [
            self::STATUS_PENDING => [self::STATUS_PAYMENT_PENDING, self::STATUS_CONFIRMED, self::STATUS_FAILED, self::STATUS_CANCELLED],
            self::STATUS_PAYMENT_PENDING => [self::STATUS_CONFIRMED, self::STATUS_FAILED, self::STATUS_CANCELLED],
            self::STATUS_CONFIRMED => [self::STATUS_PROCESSING, self::STATUS_FAILED, self::STATUS_CANCELLED],
            self::STATUS_PROCESSING => [self::STATUS_PRINTING, self::STATUS_FAILED],
            self::STATUS_PRINTING => [self::STATUS_PACKED, self::STATUS_FAILED],
            self::STATUS_PACKED => [self::STATUS_SHIPPED, self::STATUS_FAILED],
            self::STATUS_SHIPPED => [self::STATUS_OUT_FOR_DELIVERY, self::STATUS_DELIVERED, self::STATUS_FAILED],
            self::STATUS_OUT_FOR_DELIVERY => [self::STATUS_DELIVERED, self::STATUS_FAILED],
            self::STATUS_DELIVERED => [],
            self::STATUS_FAILED => [],
            self::STATUS_CANCELLED => [],
        ];
    }

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::allowedTransitions()[$this->status] ?? [], true);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(OrderFile::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at')->orderBy('id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}