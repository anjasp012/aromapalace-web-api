<?php

namespace App\Services;

use App\Models\BeautyArticle;
use App\Models\BeautyTopic;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class BeautyContentService
{
    /**
     * Dapatkan daftar artikel dengan filter topik dan status trending
     */
    public function getArticles(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = BeautyArticle::with('topic')
            ->where('is_published', true);

        if (!empty($filters['topic'])) {
            $topicSlug = $filters['topic'];
            $query->whereHas('topic', fn($t) => $t->where('slug', $topicSlug));
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['trending']) || !empty($filters['is_trending'])) {
            $query->where('is_trending', true);
        }

        return $query->orderBy('published_at', 'desc')->paginate($perPage)->withQueryString();
    }

    /**
     * Dapatkan daftar topik kecantikan beserta jumlah artikel
     */
    public function getTopics(): Collection
    {
        return BeautyTopic::withCount(['articles' => fn($q) => $q->where('is_published', true)])
            ->orderBy('sort_order', 'asc')
            ->get();
    }

    /**
     * Dapatkan artikel yang sedang trending
     */
    public function getTrendingArticles(int $limit = 4): Collection
    {
        return BeautyArticle::with('topic')
            ->where('is_published', true)
            ->where('is_trending', true)
            ->take($limit)
            ->get();
    }

    /**
     * Dapatkan artikel lain untuk rekomendasi di halaman detail
     */
    public function getMoreArticles(BeautyArticle $currentArticle, int $limit = 3): Collection
    {
        return BeautyArticle::with('topic')
            ->where('is_published', true)
            ->where('id', '!=', $currentArticle->id)
            ->orderBy('published_at', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Dapatkan detail artikel berdasarkan slug
     */
    public function getArticleDetail(string $slug): ?BeautyArticle
    {
        return BeautyArticle::with('topic')
            ->where('slug', $slug)
            ->where('is_published', true)
            ->first();
    }

    /**
     * Dapatkan produk terkait yang direkomendasikan pada artikel
     */
    public function getRelatedProducts(BeautyArticle $article): Collection
    {
        if (empty($article->related_product_ids)) {
            return new Collection();
        }

        return Product::with(['brand', 'images', 'variants'])
            ->whereIn('id', $article->related_product_ids)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Dapatkan produk diskon untuk etalase penawaran spesial
     */
    public function getDiscountProducts(int $limit = 20): Collection
    {
        return Product::with(['brand', 'category', 'images', 'variants'])
            ->where('is_active', true)
            ->where('is_discount', true)
            ->orderBy('discount_percent', 'desc')
            ->take($limit)
            ->get();
    }
}

