<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'fulfillment_type',
        'store_id',
        'address_id',
        'shipping_address_snapshot',
        'shipping_courier',
        'shipping_service',
        'shipping_cost',
        'estimated_delivery',
        'subtotal',
        'discount_amount',
        'total_amount',
        'promo_code',
        'payment_method',
        'payment_status',
        'order_status',
        'tracking_number',
        'pickup_code',
        'notes',
        'paid_at',
        'completed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'shipping_address_snapshot' => 'array',
            'shipping_cost' => 'float',
            'subtotal' => 'float',
            'discount_amount' => 'float',
            'total_amount' => 'float',
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(UserAddress::class, 'address_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at', 'asc');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}

