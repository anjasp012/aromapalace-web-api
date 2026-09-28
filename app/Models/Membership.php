<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Membership extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tier',
        'points',
        'total_spent',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'total_spent' => 'float',
            'joined_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Hitung tier berikutnya dan sisa belanja yang dibutuhkan
     */
    public function getNextTierInfoAttribute(): array
    {
        return match ($this->tier) {
            'Silver' => [
                'next_tier' => 'Gold',
                'target_spent' => 2000000,
                'remaining_spent' => max(0, 2000000 - $this->total_spent),
                'progress_percent' => min(100, round(($this->total_spent / 2000000) * 100, 1)),
            ],
            'Gold' => [
                'next_tier' => 'Platinum',
                'target_spent' => 5000000,
                'remaining_spent' => max(0, 5000000 - $this->total_spent),
                'progress_percent' => min(100, round(($this->total_spent / 5000000) * 100, 1)),
            ],
            'Platinum' => [
                'next_tier' => null,
                'target_spent' => null,
                'remaining_spent' => 0,
                'progress_percent' => 100,
            ],
        };
    }
}

