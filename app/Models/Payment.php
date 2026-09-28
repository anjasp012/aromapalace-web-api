<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_gateway',
        'transaction_id',
        'payment_type',
        'gross_amount',
        'transaction_status',
        'va_number',
        'qr_string',
        'payload',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'gross_amount' => 'float',
            'payload' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

