<?php

namespace App\Http\Controllers\Api;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\UserSearchHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends BaseApiController
{
    /**
     * Search Autocomplete Suggestions (Produk, Kategori, & Brand)
     */
    public function suggestions(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q'));

        if (empty($query)) {
            return $this->sendResponse([
                'products' => [],
                'categories' => [],
                'brands' => [],
                'stores' => [],
                'articles' => [],
            ], 'Search suggestions.');
        }

        // Catat ke search history user jika user login
        if ($user = $request->user('sanctum')) {
            UserSearchHistory::firstOrCreate([
                'user_id' => $user->id,
                'keyword' => $query,
            ]);
        }

        // 1. Produk Wewangian
        $products = Product::with(['brand', 'category'])
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'ilike', "%{$query}%")
                    ->orWhere('description', 'ilike', "%{$query}%")
                    ->orWhereHas('brand', function ($bq) use ($query) {
                        $bq->where('name', 'ilike', "%{$query}%");
                    })
                    ->orWhereHas('category', function ($cq) use ($query) {
                        $cq->where('name', 'ilike', "%{$query}%");
                    });
            })
            ->take(6)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'primary_image' => $p->primary_image,
                    'base_price' => (float) $p->base_price,
                    'final_price' => (float) $p->final_price,
                    'discount_percent' => (int) ($p->discount_percent ?? 0),
                    'brand' => $p->brand ? ['name' => $p->brand->name, 'slug' => $p->brand->slug] : null,
                    'category' => $p->category ? ['name' => $p->category->name, 'slug' => $p->category->slug] : null,
                    'url' => route('products.show', $p->slug),
                ];
            });

        // 2. Kategori yang Cocok
        $categories = Category::where('is_active', true)
            ->where('name', 'ilike', "%{$query}%")
            ->withCount(['products' => function ($q) {
                $q->where('is_active', true);
            }])
            ->take(4)
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'image_url' => $c->image_url,
                    'icon_url' => $c->icon_url,
                    'products_count' => $c->products_count,
                    'url' => route('products.index', ['category' => $c->slug]),
                ];
            });

        // 3. Brand yang Cocok
        $brands = Brand::where('is_active', true)
            ->where('name', 'ilike', "%{$query}%")
            ->withCount(['products' => function ($q) {
                $q->where('is_active', true);
            }])
            ->take(4)
            ->get()
            ->map(function ($b) {
                return [
                    'id' => $b->id,
                    'name' => $b->name,
                    'slug' => $b->slug,
                    'logo_url' => $b->logo_url,
                    'products_count' => $b->products_count,
                    'url' => route('products.index', ['brand' => $b->slug]),
                ];
            });

        return $this->sendResponse([
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'stores' => [],
            'articles' => [],
        ], 'Saran pencarian.');
    }

    /**
     * Riwayat pencarian user terakhir (Recent Searches)
     */
    public function recentSearches(Request $request): JsonResponse
    {
        $histories = UserSearchHistory::where('user_id', $request->user()->id)
            ->latest()
            ->take(10)
            ->get();

        return $this->sendResponse($histories, 'Riwayat pencarian pengguna.');
    }

    /**
     * Hapus satu item kata kunci dari pencarian terakhir
     */
    public function deleteHistory(Request $request, int $id): JsonResponse
    {
        UserSearchHistory::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->delete();

        return $this->sendResponse(null, 'Riwayat pencarian dihapus.');
    }

    /**
     * Hapus seluruh riwayat pencarian user
     */
    public function clearHistory(Request $request): JsonResponse
    {
        UserSearchHistory::where('user_id', $request->user()->id)->delete();

        return $this->sendResponse(null, 'Seluruh riwayat pencarian berhasil dibersihkan.');
    }
}

