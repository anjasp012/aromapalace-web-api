<?php

namespace App\Services;

use App\Models\Banner;
use App\Models\BeautyArticle;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;

class HomeService
{
    /**
     * Dapatkan seluruh payload data Beranda untuk Web dan Mobile API
     */
    public function getHomeData(): array
    {
        $banners = Banner::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        $categories = Category::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        $recommendedProducts = Product::with(['brand', 'category', 'images', 'variants'])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->take(20)
            ->get();

        $popularProducts = Product::with(['brand', 'category', 'images', 'variants'])
            ->where('is_active', true)
            ->where('is_popular', true)
            ->orderBy('rating_avg', 'desc')
            ->take(10)
            ->get();

        $discountProducts = Product::with(['brand', 'category', 'images', 'variants'])
            ->where('is_active', true)
            ->where('is_discount', true)
            ->orderBy('discount_percent', 'desc')
            ->take(20)
            ->get();

        $featuredBrands = Brand::where('is_active', true)
            ->where('is_featured', true)
            ->take(10)
            ->get();

        $exclusivePromos = Promotion::where('is_active', true)
            ->where('end_date', '>=', now())
            ->take(5)
            ->get();

        $latestArticles = BeautyArticle::with('topic')
            ->where('is_published', true)
            ->orderBy('published_at', 'desc')
            ->take(4)
            ->get();

        return [
            'banners' => $banners,
            'categories' => $categories,
            'recommended_products' => $recommendedProducts,
            'popular_products' => $popularProducts,
            'discount_products' => $discountProducts,
            'featured_brands' => $featuredBrands,
            'exclusive_promos' => $exclusivePromos,
            'latest_articles' => $latestArticles,
        ];
    }
}

