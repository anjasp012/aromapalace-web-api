<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'price',
        'discount_price',
        'stock',
        'attribute_name',
        'attribute_value',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'discount_price' => 'float',
            'stock' => 'integer',
        ];
    }

    protected $appends = [
        'final_price',
    ];

    public function getFinalPriceAttribute(): float
    {
        return ($this->discount_price && $this->discount_price > 0)
            ? (float) $this->discount_price
            : (float) $this->price;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

