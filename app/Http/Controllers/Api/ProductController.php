<?php

namespace App\Http\Controllers\Api;

use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends BaseApiController
{
    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * Product Catalog dengan search, filter, dan sorting
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $products = $this->productService->getProducts($request->query(), $perPage);

        return $this->sendResponse($products, 'Katalog produk.');
    }

    /**
     * Product Detail
     */
    public function show(string $slugOrId): JsonResponse
    {
        $product = $this->productService->getProductDetail($slugOrId);

        if (!$product) {
            return $this->sendError('Produk tidak ditemukan.', [], 404);
        }

        $ratingSummary = $this->productService->getRatingSummary($product);
        $shareData = $this->productService->getShareData($product);

        return $this->sendResponse([
            'product' => $product,
            'rating_summary' => $ratingSummary,
            'share' => $shareData,
        ], 'Detail produk.');
    }

    /**
     * Daftar Kategori
     */
    public function categories(): JsonResponse
    {
        $categories = $this->productService->getCategories();

        return $this->sendResponse($categories, 'Daftar kategori produk.');
    }

    /**
     * Daftar Brand
     */
    public function brands(): JsonResponse
    {
        $brands = $this->productService->getBrands();

        return $this->sendResponse($brands, 'Daftar brand terkemuka.');
    }
}
