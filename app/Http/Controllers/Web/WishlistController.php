<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\WishlistService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function __construct(
        protected WishlistService $wishlistService
    ) {}

    /**
     * Display the wishlist page.
     */
    public function index(Request $request): View
    {
        $wishlists = $this->wishlistService->getWishlistItems(auth()->user());

        return view('web.wishlist.index', compact('wishlists'));
    }

    /**
     * Toggle product in/out of wishlist via AJAX.
     */
    public function toggle(Request $request): JsonResponse
    {
        $productId = (int) $request->input('product_id');

        try {
            $result = $this->wishlistService->toggle(auth()->user(), $productId);

            return response()->json([
                'success' => true,
                'is_wishlisted' => $result['is_wishlisted'],
                'count' => $result['count'],
                'message' => $result['message'],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Remove an item from wishlist.
     */
    public function destroy(int $productId): RedirectResponse
    {
        $this->wishlistService->remove(auth()->user(), $productId);

        return back()->with('success', 'Item removed from wishlist.');
    }

    /**
     * Move a wishlisted product directly to shopping cart.
     */
    public function moveToCart(Request $request, int $productId): RedirectResponse
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('info', 'Please sign in to add items to your shopping bag.');
        }

        try {
            $variantId = $request->input('product_variant_id') ? (int) $request->input('product_variant_id') : null;
            $quantity = (int) ($request->input('quantity', 1));

            $this->wishlistService->moveToCart(auth()->user(), $productId, $variantId, $quantity);

            return redirect()->route('cart.index')->with('success', 'Item moved to your shopping bag.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}


