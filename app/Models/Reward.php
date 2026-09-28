<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'image_url',
        'points_required',
        'reward_type',
        'discount_amount',
        'promo_prefix',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'points_required' => 'integer',
            'discount_amount' => 'float',
            'is_active' => 'boolean',
        ];
    }
}

