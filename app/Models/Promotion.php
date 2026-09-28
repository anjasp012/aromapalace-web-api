<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'description',
        'banner_image',
        'discount_type',
        'discount_value',
        'min_purchase',
        'max_discount',
        'quota',
        'used_count',
        'start_date',
        'end_date',
        'terms_conditions',
        'is_exclusive',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'float',
            'min_purchase' => 'float',
            'max_discount' => 'float',
            'quota' => 'integer',
            'used_count' => 'integer',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'is_exclusive' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function isValidForAmount(float $amount): bool
    {
        $now = now();
        if (!$this->is_active) {
            return false;
        }
        if ($now->lt($this->start_date) || $now->gt($this->end_date)) {
            return false;
        }
        if ($this->quota > 0 && $this->used_count >= $this->quota) {
            return false;
        }
        if ($amount < $this->min_purchase) {
            return false;
        }
        return true;
    }

    public function calculateDiscount(float $amount): float
    {
        if ($this->discount_type === 'percentage') {
            $discount = ($amount * $this->discount_value) / 100;
            if ($this->max_discount && $this->max_discount > 0) {
                $discount = min($discount, $this->max_discount);
            }
            return round($discount, 2);
        }

        return min($this->discount_value, $amount);
    }
}

