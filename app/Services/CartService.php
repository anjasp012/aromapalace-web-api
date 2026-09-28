<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Promotion;
use App\Models\User;
use Exception;

class CartService
{
    /**
     * Ambil isi keranjang belanja user beserta kalkulasi subtotal dan diskon
     */
    public function getCartSummary(User $user): array
    {
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        $items = CartItem::with(['product.brand', 'product.images', 'variant'])
            ->where('cart_id', $cart->id)
            ->get();

        $subtotal = 0.0;
        $formattedItems = [];

        foreach ($items as $item) {
            $unitPrice = $item->variant
                ? $item->variant->final_price
                : ($item->product?->final_price ?? 0);

            $itemSubtotal = $unitPrice * $item->quantity;
            $subtotal += $itemSubtotal;

            $formattedItems[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name,
                'product_slug' => $item->product?->slug,
                'product_image' => $item->product?->primary_image,
                'brand_name' => $item->product?->brand?->name,
                'variant_id' => $item->product_variant_id,
                'variant_name' => $item->variant?->name,
                'unit_price' => $unitPrice,
                'quantity' => $item->quantity,
                'subtotal' => $itemSubtotal,
                'available_stock' => $item->variant ? $item->variant->stock : ($item->product?->stock ?? 0),
            ];
        }

        // Cek promo code jika ada
        $discountAmount = 0.0;
        $promoDetails = null;

        if ($cart->applied_promo_code) {
            $promo = Promotion::where('code', $cart->applied_promo_code)
                ->where('is_active', true)
                ->first();

            if ($promo && $promo->isValidForAmount($subtotal)) {
                $discountAmount = $promo->calculateDiscount($subtotal);
                $promoDetails = [
                    'code' => $promo->code,
                    'title' => $promo->title,
                    'discount_amount' => $discountAmount,
                ];
            } else {
                $cart->update(['applied_promo_code' => null]);
            }
        }

        $total = max(0, $subtotal - $discountAmount);

        return [
            'cart_id' => $cart->id,
            'items' => $formattedItems,
            'total_items' => count($formattedItems),
            'total_quantity' => array_sum(array_column($formattedItems, 'quantity')),
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'applied_promo' => $promoDetails,
            'total_amount' => $total,
        ];
    }

    /**
     * Tambah item ke keranjang belanja
     */
    public function addItem(User $user, int $productId, ?int $variantId = null, int $quantity = 1): CartItem
    {
        $product = Product::findOrFail($productId);
        $variant = $variantId ? ProductVariant::findOrFail($variantId) : null;

        if (!$variant && $product->variants()->exists()) {
            $variant = $product->variants()->first();
        }

        $availableStock = $variant ? $variant->stock : $product->stock;
        if ($availableStock < $quantity) {
            throw new Exception("Stok produk tidak mencukupi. Tersisa: {$availableStock}");
        }

        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->first();

        if ($cartItem) {
            $newQty = $cartItem->quantity + $quantity;
            if ($newQty > $availableStock) {
                throw new Exception("Maksimum stok yang dapat dibeli adalah {$availableStock}");
            }
            $cartItem->update(['quantity' => $newQty]);
        } else {
            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'quantity' => $quantity,
            ]);
        }

        return $cartItem->load(['product', 'variant']);
    }

    /**
     * Update quantity item keranjang
     */
    public function updateQuantity(User $user, int $itemId, int $quantity): CartItem
    {
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);
        $cartItem = CartItem::where('cart_id', $cart->id)->where('id', $itemId)->firstOrFail();

        $availableStock = $cartItem->variant ? $cartItem->variant->stock : ($cartItem->product?->stock ?? 0);
        if ($quantity > $availableStock) {
            throw new Exception("Stok produk tidak mencukupi. Tersisa: {$availableStock}");
        }

        $cartItem->update(['quantity' => $quantity]);
        return $cartItem;
    }

    /**
     * Hapus satu item dari keranjang
     */
    public function removeItem(User $user, int $itemId): bool
    {
        $cart = Cart::where('user_id', $user->id)->first();
        if ($cart) {
            return (bool) CartItem::where('cart_id', $cart->id)->where('id', $itemId)->delete();
        }
        return false;
    }

    /**
     * Terapkan kode promo
     */
    public function applyPromo(User $user, string $code): array
    {
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);
        $promo = Promotion::where('code', strtoupper($code))->where('is_active', true)->first();

        if (!$promo) {
            throw new Exception('Kode promo tidak valid atau sudah kedaluwarsa.');
        }

        $now = now();
        if ($now->lt($promo->start_date) || $now->gt($promo->end_date)) {
            throw new Exception('Masa berlaku kode promo ini telah berakhir.');
        }

        if ($promo->quota > 0 && $promo->used_count >= $promo->quota) {
            throw new Exception('Kuota kode promo ini sudah habis.');
        }

        // Cek subtotal
        $cartItems = CartItem::where('cart_id', $cart->id)->with(['product', 'variant'])->get();
        $subtotal = 0.0;
        foreach ($cartItems as $item) {
            $unitPrice = $item->variant ? $item->variant->final_price : ($item->product?->final_price ?? 0);
            $subtotal += ($unitPrice * $item->quantity);
        }

        if ($subtotal < $promo->min_purchase) {
            throw new Exception('Minimal belanja untuk promo ini adalah Rp ' . number_format($promo->min_purchase, 0, ',', '.'));
        }

        $cart->update(['applied_promo_code' => $promo->code]);
        $discount = $promo->calculateDiscount($subtotal);

        return [
            'code' => $promo->code,
            'title' => $promo->title,
            'discount_amount' => $discount,
        ];
    }

    /**
     * Hapus kode promo
     */
    public function removePromo(User $user): bool
    {
        $cart = Cart::where('user_id', $user->id)->first();
        if ($cart) {
            $cart->update(['applied_promo_code' => null]);
            return true;
        }
        return false;
    }

    /**
     * Kosongkan keranjang
     */
    public function clear(User $user): bool
    {
        $cart = Cart::where('user_id', $user->id)->first();
        if ($cart) {
            $cart->items()->delete();
            $cart->update(['applied_promo_code' => null]);
            return true;
        }
        return false;
    }
}

