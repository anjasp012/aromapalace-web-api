<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    public function index(Request $request): View
    {
        $products = $this->productService->getProducts($request->query(), 20);
        $categories = $this->productService->getCategories();
        $brands = $this->productService->getBrands();
        $sort = $request->query('sort', 'popular');

        return view('web.products.index', compact('products', 'categories', 'brands', 'sort'));
    }

    public function show(string $slug): View
    {
        $product = $this->productService->getProductDetail($slug);
        if (!$product) {
            abort(404, 'Produk tidak ditemukan.');
        }

        $relatedProducts = $this->productService->getRelatedProducts($product, 4);

        return view('web.products.show', compact('product', 'relatedProducts'));
    }
}
