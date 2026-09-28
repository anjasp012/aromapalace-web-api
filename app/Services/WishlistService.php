<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Exception;
use Illuminate\Support\Collection;

class WishlistService
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Ambil item wishlist (berbasis User model untuk user login, atau ID array untuk guest)
     */
    public function getWishlistItems(?User $user, array $guestProductIds = []): Collection
    {
        if ($user) {
            return Wishlist::with(['product.brand', 'product.category', 'product.images', 'product.variants'])
                ->where('user_id', $user->id)
                ->latest()
                ->get();
        }

        if (empty($guestProductIds)) {
            return collect();
        }

        $products = Product::with(['brand', 'category', 'images', 'variants'])
            ->whereIn('id', $guestProductIds)
            ->where('is_active', true)
            ->get();

        return $products->map(function ($product) {
            return (object)[
                'id' => $product->id,
                'product_id' => $product->id,
                'product' => $product,
                'created_at' => now(),
            ];
        });
    }

    /**
     * Ambil list ID produk yang di-wishlist
     */
    public function getWishlistProductIds(?User $user, array $guestProductIds = []): array
    {
        if ($user) {
            return Wishlist::where('user_id', $user->id)->pluck('product_id')->toArray();
        }

        return array_values(array_unique(array_filter(array_map('intval', $guestProductIds))));
    }

    /**
     * Hitung total item wishlist
     */
    public function getWishlistCount(?User $user, array $guestProductIds = []): int
    {
        if ($user) {
            return Wishlist::where('user_id', $user->id)->count();
        }

        return count(array_unique(array_filter(array_map('intval', $guestProductIds))));
    }

    /**
     * Cek apakah produk ada dalam wishlist
     */
    public function isWishlisted(?User $user, int $productId, array $guestProductIds = []): bool
    {
        if ($user) {
            return Wishlist::where('user_id', $user->id)->where('product_id', $productId)->exists();
        }

        return in_array($productId, $guestProductIds);
    }

    /**
     * Toggle produk ke / dari wishlist
     */
    public function toggle(?User $user, int $productId, array $guestProductIds = []): array
    {
        $product = Product::find($productId);
        if (!$product) {
            throw new Exception('Produk tidak ditemukan.', 404);
        }

        if ($user) {
            $existing = Wishlist::where('user_id', $user->id)->where('product_id', $productId)->first();

            if ($existing) {
                $existing->delete();
                $isWishlisted = false;
                $message = "Produk berhasil dihapus dari Wishlist.";
                $item = null;
            } else {
                $item = Wishlist::create([
                    'user_id' => $user->id,
                    'product_id' => $productId,
                ]);
                $isWishlisted = true;
                $message = "Produk berhasil ditambahkan ke Wishlist.";
            }

            $count = Wishlist::where('user_id', $user->id)->count();
            $updatedSessionIds = $guestProductIds;
        } else {
            $ids = array_values(array_unique(array_filter(array_map('intval', $guestProductIds))));
            if (in_array($productId, $ids)) {
                $ids = array_values(array_diff($ids, [$productId]));
                $isWishlisted = false;
                $message = "Produk berhasil dihapus dari Wishlist.";
            } else {
                $ids[] = $productId;
                $isWishlisted = true;
                $message = "Produk berhasil ditambahkan ke Wishlist.";
            }

            $count = count($ids);
            $updatedSessionIds = $ids;
            $item = null;
        }

        return [
            'is_wishlisted' => $isWishlisted,
            'count' => $count,
            'message' => $message,
            'session_ids' => $updatedSessionIds,
            'item' => $item,
        ];
    }

    /**
     * Hapus item dari wishlist
     */
    public function remove(?User $user, int $productId, array $guestProductIds = []): array
    {
        if ($user) {
            $deleted = Wishlist::where('user_id', $user->id)->where('product_id', $productId)->delete();
            $count = Wishlist::where('user_id', $user->id)->count();
            $updatedSessionIds = $guestProductIds;
        } else {
            $ids = array_values(array_unique(array_filter(array_map('intval', $guestProductIds))));
            $deleted = in_array($productId, $ids);
            $updatedSessionIds = array_values(array_diff($ids, [$productId]));
            $count = count($updatedSessionIds);
        }

        return [
            'deleted' => (bool) $deleted,
            'count' => $count,
            'session_ids' => $updatedSessionIds,
            'message' => $deleted ? 'Produk berhasil dihapus dari wishlist.' : 'Produk tidak ada dalam wishlist.',
        ];
    }

    /**
     * Pindahkan item dari wishlist langsung ke keranjang belanja
     */
    public function moveToCart(User $user, int $productId, ?int $variantId = null, int $quantity = 1): array
    {
        $product = Product::with('variants')->where('id', $productId)->where('is_active', true)->first();
        if (!$product) {
            throw new Exception('Produk tidak ditemukan atau sudah tidak tersedia.', 404);
        }

        $chosenVariantId = $variantId ?? $product->variants->first()?->id;

        // Gunakan CartService untuk konsistensi stok dan validasi
        $cartItem = $this->cartService->addItem($user, $productId, $chosenVariantId, $quantity);

        // Hapus dari wishlist user
        Wishlist::where('user_id', $user->id)->where('product_id', $productId)->delete();

        return [
            'cart_item' => $cartItem,
            'message' => 'Produk berhasil dipindahkan ke keranjang belanja.',
        ];
    }

    /**
     * Sinkronisasi wishlist guest (session) ke database user setelah login/register
     */
    public function syncGuestWishlistToUser(User $user, array $guestProductIds): int
    {
        if (empty($guestProductIds)) {
            return 0;
        }

        $existingProductIds = Wishlist::where('user_id', $user->id)->pluck('product_id')->toArray();
        $toInsert = array_diff($guestProductIds, $existingProductIds);

        $inserted = 0;
        foreach ($toInsert as $productId) {
            if (Product::where('id', $productId)->where('is_active', true)->exists()) {
                Wishlist::create([
                    'user_id' => $user->id,
                    'product_id' => $productId,
                ]);
                $inserted++;
            }
        }

        return $inserted;
    }
}

