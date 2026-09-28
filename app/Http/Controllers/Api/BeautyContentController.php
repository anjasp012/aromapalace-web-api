<?php

namespace App\Http\Controllers\Api;

use App\Services\BeautyContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BeautyContentController extends BaseApiController
{
    public function __construct(
        protected BeautyContentService $beautyContentService
    ) {}

    /**
     * Daftar topik kecantikan
     */
    public function topics(): JsonResponse
    {
        $topics = $this->beautyContentService->getTopics();

        return $this->sendResponse($topics, 'Daftar topik konten kecantikan.');
    }

    /**
     * Daftar artikel kecantikan dengan filter topik
     */
    public function articles(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 10);
        $articles = $this->beautyContentService->getArticles($request->query(), $perPage);

        return $this->sendResponse($articles, 'Daftar artikel kecantikan.');
    }

    /**
     * Tren kecantikan saat ini (Beauty Trends)
     */
    public function trends(): JsonResponse
    {
        $trends = $this->beautyContentService->getTrendingArticles(6);

        return $this->sendResponse($trends, 'Tren kecantikan terkini.');
    }

    /**
     * Detail artikel kecantikan beserta produk terkait yang direkomendasikan
     */
    public function show(string $slug): JsonResponse
    {
        $article = $this->beautyContentService->getArticleDetail($slug);

        if (!$article) {
            return $this->sendError('Artikel tidak ditemukan.', [], 404);
        }

        $relatedProducts = $this->beautyContentService->getRelatedProducts($article);

        return $this->sendResponse([
            'article' => $article,
            'related_products' => $relatedProducts,
        ], 'Detail artikel kecantikan.');
    }
}
