<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BeautyArticle;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $products = Product::where('is_active', true)->select('slug', 'updated_at')->get();
        $articles = BeautyArticle::where('is_published', true)->select('slug', 'updated_at')->get();
        $categories = Category::select('slug', 'updated_at')->get();

        $content = view('web.sitemap', compact('products', 'articles', 'categories'))->render();

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }
}

