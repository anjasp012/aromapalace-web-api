<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\BeautyContentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BeautyContentController extends Controller
{
    public function __construct(
        protected BeautyContentService $beautyContentService
    ) {}

    public function index(Request $request): View
    {
        $articles = $this->beautyContentService->getArticles($request->query(), 9);
        $topics = $this->beautyContentService->getTopics();
        $trendingArticles = $this->beautyContentService->getTrendingArticles(4);
        $discountProducts = $this->beautyContentService->getDiscountProducts(20);

        return view('web.articles.index', compact('articles', 'topics', 'trendingArticles', 'discountProducts'));
    }

    public function show(string $slug): View
    {
        $article = $this->beautyContentService->getArticleDetail($slug);
        if (!$article) {
            abort(404, 'Artikel tidak ditemukan.');
        }

        $moreArticles = $this->beautyContentService->getMoreArticles($article, 3);
        $discountProducts = $this->beautyContentService->getDiscountProducts(20);

        return view('web.articles.show', compact('article', 'moreArticles', 'discountProducts'));
    }
}
