<?php

namespace App\Http\Controllers\Api;

use App\Services\WishlistService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends BaseApiController
{
    public function __construct(
        protected WishlistService $wishlistService
    ) {}

    /**
     * Tampilkan seluruh produk dalam wishlist user
     */
    public function index(Request $request): JsonResponse
    {
        $wishlists = $this->wishlistService->getWishlistItems($request->user());

        return $this->sendResponse($wishlists, 'Daftar wishlist pengguna.');
    }

    /**
     * Tambah produk ke wishlist (atau toggle)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $productId = (int) $request->input('product_id');

        try {
            $result = $this->wishlistService->toggle($request->user(), $productId);

            if ($result['is_wishlisted']) {
                return $this->sendResponse([
                    'is_wishlisted' => true,
                    'count' => $result['count'],
                    'item' => $result['item'] ? $result['item']->load('product') : null,
                ], $result['message'], 201);
            }

            return $this->sendResponse([
                'is_wishlisted' => false,
                'count' => $result['count'],
            ], $result['message']);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 400);
        }
    }

    /**
     * Hapus produk dari wishlist
     */
    public function destroy(Request $request, int $productId): JsonResponse
    {
        $result = $this->wishlistService->remove($request->user(), $productId);

        if (!$result['deleted']) {
            return $this->sendError('Produk tidak ada dalam wishlist.', [], 404);
        }

        return $this->sendResponse([
            'count' => $result['count'],
        ], $result['message']);
    }

    /**
     * Pindahkan produk dari wishlist langsung ke keranjang belanja (Shopping Cart)
     */
    public function moveToCart(Request $request, int $productId): JsonResponse
    {
        try {
            $variantId = $request->input('product_variant_id') ? (int) $request->input('product_variant_id') : null;
            $quantity = (int) $request->input('quantity', 1);

            $result = $this->wishlistService->moveToCart($request->user(), $productId, $variantId, $quantity);

            return $this->sendResponse($result['cart_item'], $result['message']);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 400);
        }
    }

    /**
     * Sinkronisasi wishlist dari mobile local storage / offline ke database user
     */
    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        $syncedCount = $this->wishlistService->syncGuestWishlistToUser($request->user(), $request->input('product_ids'));

        return $this->sendResponse([
            'synced_items' => $syncedCount,
            'total_wishlist' => $this->wishlistService->getWishlistCount($request->user()),
        ], 'Wishlist berhasil disinkronkan.');
    }
}
