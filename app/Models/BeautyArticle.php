<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BeautyArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'topic_id',
        'title',
        'slug',
        'summary',
        'content',
        'cover_image',
        'author_name',
        'reading_time_minutes',
        'is_trending',
        'is_published',
        'published_at',
        'related_product_ids',
    ];

    protected function casts(): array
    {
        return [
            'reading_time_minutes' => 'integer',
            'is_trending' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'related_product_ids' => 'array',
        ];
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(BeautyTopic::class, 'topic_id');
    }
}

