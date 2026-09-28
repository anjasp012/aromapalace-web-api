<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $summary = $this->cartService->getCartSummary(auth()->user());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $summary,
            ]);
        }

        return view('web.cart.index', compact('summary'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'nullable|integer|min:1',
        ]);

        try {
            $cartItem = $this->cartService->addItem(
                auth()->user(),
                (int) $validated['product_id'],
                !empty($validated['product_variant_id']) ? (int) $validated['product_variant_id'] : null,
                (int) ($validated['quantity'] ?? 1)
            );

            $summary = $this->cartService->getCartSummary(auth()->user());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Produk berhasil ditambahkan ke keranjang.',
                    'data' => $summary,
                ]);
            }

            return redirect()->route('cart.index')->with('success', 'Produk berhasil ditambahkan ke keranjang belanja.');
        } catch (Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $this->cartService->updateQuantity(auth()->user(), $id, (int) $validated['quantity']);
            $summary = $this->cartService->getCartSummary(auth()->user());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Jumlah produk diperbarui.',
                    'data' => $summary,
                ]);
            }

            return redirect()->route('cart.index')->with('success', 'Jumlah produk diperbarui.');
        } catch (Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $this->cartService->removeItem(auth()->user(), $id);
        $summary = $this->cartService->getCartSummary(auth()->user());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Produk dihapus dari keranjang.',
                'data' => $summary,
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Produk dihapus dari keranjang.');
    }

    public function applyPromo(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate(['promo_code' => 'required|string']);

        try {
            $this->cartService->applyPromo(auth()->user(), $request->promo_code);
            $summary = $this->cartService->getCartSummary(auth()->user());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Kupon promo berhasil digunakan.',
                    'data' => $summary,
                ]);
            }

            return redirect()->route('cart.index')->with('success', 'Kupon promo berhasil digunakan.');
        } catch (Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function removePromo(Request $request): RedirectResponse|JsonResponse
    {
        $this->cartService->removePromo(auth()->user());
        $summary = $this->cartService->getCartSummary(auth()->user());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kupon promo telah dilepas.',
                'data' => $summary,
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Kupon promo telah dilepas.');
    }
}
