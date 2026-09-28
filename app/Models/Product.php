<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_id',
        'category_id',
        'name',
        'slug',
        'short_description',
        'description',
        'how_to_use',
        'ingredients',
        'base_price',
        'discount_price',
        'discount_percent',
        'stock',
        'rating_avg',
        'reviews_count',
        'is_featured',
        'is_popular',
        'is_discount',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'float',
            'discount_price' => 'float',
            'discount_percent' => 'integer',
            'stock' => 'integer',
            'rating_avg' => 'float',
            'reviews_count' => 'integer',
            'is_featured' => 'boolean',
            'is_popular' => 'boolean',
            'is_discount' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected $appends = [
        'final_price',
        'primary_image',
    ];

    public function getFinalPriceAttribute(): float
    {
        return ($this->discount_price && $this->discount_price > 0)
            ? (float) $this->discount_price
            : (float) $this->base_price;
    }

    public function getPrimaryImageAttribute(): ?string
    {
        return $this->images->firstWhere('is_primary', true)?->image_url
            ?? $this->images->first()?->image_url;
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order', 'asc');
    }

    public function primaryImageRelation(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class)->where('is_approved', true)->orderBy('created_at', 'desc');
    }
}

