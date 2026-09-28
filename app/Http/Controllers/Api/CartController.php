<?php

namespace App\Http\Controllers\Api;

use App\Services\CartService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends BaseApiController
{
    public function __construct(
        protected CartService $cartService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $summary = $this->cartService->getCartSummary($request->user());
        return $this->sendResponse($summary, 'Isi keranjang belanja.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        try {
            $item = $this->cartService->addItem(
                $request->user(),
                (int) $validated['product_id'],
                !empty($validated['product_variant_id']) ? (int) $validated['product_variant_id'] : null,
                (int) ($validated['quantity'] ?? 1)
            );
            return $this->sendResponse($item, 'Produk berhasil ditambahkan ke keranjang.', 201);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }

    public function update(Request $request, int $itemId): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $item = $this->cartService->updateQuantity($request->user(), $itemId, (int) $validated['quantity']);
            return $this->sendResponse($item, 'Jumlah produk berhasil diperbarui.');
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }

    public function destroy(Request $request, int $itemId): JsonResponse
    {
        $this->cartService->removeItem($request->user(), $itemId);
        return $this->sendResponse(null, 'Item berhasil dihapus dari keranjang.');
    }

    public function applyPromo(Request $request): JsonResponse
    {
        $request->validate(['promo_code' => 'required|string']);

        try {
            $result = $this->cartService->applyPromo($request->user(), $request->promo_code);
            return $this->sendResponse($result, 'Kode promo berhasil digunakan.');
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }

    public function removePromo(Request $request): JsonResponse
    {
        $this->cartService->removePromo($request->user());
        return $this->sendResponse(null, 'Kode promo dilepas.');
    }

    public function clear(Request $request): JsonResponse
    {
        $this->cartService->clear($request->user());
        return $this->sendResponse(null, 'Keranjang belanja berhasil dikosongkan.');
    }
}
