<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    /**
     * Dapatkan katalog produk dengan filter lengkap (Search, Category, Brand, Price, Sorting)
     */
    public function getProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Product::with(['brand', 'category', 'images', 'variants'])
            ->where('is_active', true);

        // Search by keyword
        $search = $filters['search'] ?? null;
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%")
                  ->orWhereHas('brand', fn($b) => $b->where('name', 'ilike', "%{$search}%"))
                  ->orWhereHas('category', fn($c) => $c->where('name', 'ilike', "%{$search}%"));
            });
        }

        // Filter Category (ID atau Slug)
        if (!empty($filters['category_id'])) {
            $query->where('category_id', (int) $filters['category_id']);
        } elseif (!empty($filters['category_slug']) || !empty($filters['category'])) {
            $catSlug = $filters['category_slug'] ?? $filters['category'];
            $query->whereHas('category', fn($c) => $c->where('slug', $catSlug));
        }

        // Filter Brand (ID atau Slug)
        if (!empty($filters['brand_id'])) {
            $query->where('brand_id', (int) $filters['brand_id']);
        } elseif (!empty($filters['brand_slug']) || !empty($filters['brand'])) {
            $brandSlug = $filters['brand_slug'] ?? $filters['brand'];
            $query->whereHas('brand', fn($b) => $b->where('slug', $brandSlug));
        }

        // Filter Discount
        if (!empty($filters['is_discount']) || !empty($filters['discount'])) {
            $query->where('is_discount', true);
        }

        // Filter Price Range
        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $minPrice = (float) $filters['min_price'];
            $query->where(function ($q) use ($minPrice) {
                $q->where('discount_price', '>=', $minPrice)
                  ->orWhere(function ($sub) use ($minPrice) {
                      $sub->whereNull('discount_price')->where('base_price', '>=', $minPrice);
                  });
            });
        }

        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $maxPrice = (float) $filters['max_price'];
            $query->where(function ($q) use ($maxPrice) {
                $q->where(function ($sub) use ($maxPrice) {
                    $sub->whereNotNull('discount_price')->where('discount_price', '<=', $maxPrice);
                })->orWhere(function ($sub) use ($maxPrice) {
                    $sub->whereNull('discount_price')->where('base_price', '<=', $maxPrice);
                });
            });
        }

        // Sorting
        $sort = $filters['sort_by'] ?? $filters['sort'] ?? 'popular';
        match ($sort) {
            'price_low', 'price_asc' => $query->orderByRaw('COALESCE(discount_price, base_price) ASC'),
            'price_high', 'price_desc' => $query->orderByRaw('COALESCE(discount_price, base_price) DESC'),
            'rating' => $query->orderBy('rating_avg', 'desc'),
            'newest' => $query->latest(),
            default => $query->orderBy('is_popular', 'desc')->orderBy('rating_avg', 'desc'),
        };

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Dapatkan detail produk berdasarkan ID atau slug
     */
    public function getProductDetail(string|int $slugOrId): ?Product
    {
        return Product::with([
            'brand',
            'category',
            'images',
            'variants',
            'reviews.user',
        ])
        ->where('is_active', true)
        ->where(function ($q) use ($slugOrId) {
            if (is_numeric($slugOrId)) {
                $q->where('id', (int) $slugOrId)->orWhere('slug', (string) $slugOrId);
            } else {
                $q->where('slug', $slugOrId);
            }
        })
        ->first();
    }

    /**
     * Ringkasan rating dan breakdown bintang (1-5)
     */
    public function getRatingSummary(Product $product): array
    {
        $ratingCounts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

        $reviews = ProductReview::where('product_id', $product->id)
            ->where('is_approved', true)
            ->get();

        foreach ($reviews as $rev) {
            if (isset($ratingCounts[$rev->rating])) {
                $ratingCounts[$rev->rating]++;
            }
        }

        return [
            'average' => $product->rating_avg,
            'total_reviews' => $product->reviews_count,
            'breakdown' => $ratingCounts,
        ];
    }

    /**
     * Produk terkait (dalam kategori yang sama)
     */
    public function getRelatedProducts(Product $product, int $limit = 4): Collection
    {
        return Product::with(['brand', 'images'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->take($limit)
            ->get();
    }

    /**
     * Data metadata share produk
     */
    public function getShareData(Product $product): array
    {
        return [
            'title' => $product->name,
            'description' => $product->short_description ?? substr(strip_tags($product->description), 0, 150),
            'url' => config('app.url') . "/products/{$product->slug}",
            'image' => $product->primary_image,
        ];
    }

    /**
     * Ambil seluruh kategori aktif
     */
    public function getCategories(): Collection
    {
        return Category::where('is_active', true)
            ->withCount(['products' => fn($q) => $q->where('is_active', true)])
            ->orderBy('sort_order', 'asc')
            ->get();
    }

    /**
     * Ambil seluruh brand aktif
     */
    public function getBrands(): Collection
    {
        return Brand::where('is_active', true)
            ->withCount(['products' => fn($q) => $q->where('is_active', true)])
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Ambil produk yang sedang diskon
     */
    public function getDiscountProducts(int $limit = 20): Collection
    {
        return Product::with(['brand', 'category', 'images', 'variants'])
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('is_discount', true)
                  ->orWhere(function ($sub) {
                      $sub->whereNotNull('discount_price')->where('discount_price', '>', 0);
                  })
                  ->orWhere('discount_percent', '>', 0);
            })
            ->orderByRaw('COALESCE(discount_percent, 0) DESC')
            ->take($limit)
            ->get();
    }
}

