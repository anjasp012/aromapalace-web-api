<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Promotion;
use App\Models\Store;
use App\Models\User;
use App\Models\UserAddress;
use App\Services\KiriminAjaShippingService;
use App\Services\PakasirPaymentService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    public function __construct(
        protected KiriminAjaShippingService $kiriminAjaService,
        protected PakasirPaymentService $pakasirService
    ) {}
    /**
     * Preview kalkulasi checkout (subtotal, ongkir, voucher, total)
     */
    public function previewCheckout(User $user, array $options = []): array
    {
        $cart = Cart::where('user_id', $user->id)
            ->with(['items.product.brand', 'items.variant'])
            ->first();
        if (!$cart || $cart->items->isEmpty()) {
            throw new Exception('Keranjang belanja Anda masih kosong.');
        }

        $fulfillmentType = $options['fulfillment_type'] ?? 'home_delivery';
        $addressId = $options['address_id'] ?? null;
        $storeId = $options['store_id'] ?? null;
        $shippingCourier = $options['shipping_courier'] ?? 'JNE';
        $shippingService = $options['shipping_service'] ?? 'REG';
        $promoCode = $options['promo_code'] ?? $cart->applied_promo_code;

        // Subtotal
        $subtotal = 0.0;
        $items = [];
        foreach ($cart->items as $item) {
            $unitPrice = $item->variant ? $item->variant->final_price : ($item->product?->final_price ?? 0);
            $subtotal += ($unitPrice * $item->quantity);
            $items[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name,
                'product_slug' => $item->product?->slug,
                'product_image' => $item->product?->primary_image,
                'brand_name' => $item->product?->brand?->name,
                'variant_id' => $item->product_variant_id,
                'variant_name' => $item->variant?->name,
                'price' => (float) $unitPrice,
                'unit_price' => (float) $unitPrice,
                'quantity' => (int) $item->quantity,
                'subtotal' => (float) ($unitPrice * $item->quantity),
                'weight' => (int) ($item->product?->weight_grams ?? 250),
            ];
        }

        // Resolusi Alamat & Toko
        $address = null;
        if ($fulfillmentType === 'home_delivery') {
            $address = $addressId ? $user->addresses()->find($addressId) : $user->primaryAddress;
        }

        $store = null;
        if ($fulfillmentType === 'store_pickup' && $storeId) {
            $store = Store::withCoordinates()->find($storeId);
        }

        // Ongkir via KiriminAja Aggregator (JNE, J&T, SiCepat, Anteraja)
        $shippingCost = 0.0;
        $estimatedDelivery = '1-3 Hari Kerja';
        $availableCouriers = [];

        if ($fulfillmentType === 'home_delivery') {
            $destinationCity = $address?->city ?? 'Jakarta Selatan';
            $availableCouriers = $this->kiriminAjaService->getShippingRates($destinationCity, 1000);

            // Cari rate yang dipilih pengguna atau gunakan default
            $matchedRate = collect($availableCouriers)->first(function ($rate) use ($shippingCourier, $shippingService) {
                return strcasecmp($rate['courier'], $shippingCourier) === 0 && strcasecmp($rate['service'], $shippingService) === 0;
            }) ?? ($availableCouriers[0] ?? null);

            if ($matchedRate) {
                $shippingCourier = $matchedRate['courier'];
                $shippingService = $matchedRate['service'];
                $shippingCost = (float) $matchedRate['cost'];
                $estimatedDelivery = $matchedRate['etd'];
            } else {
                $shippingCost = 15000.0;
                $estimatedDelivery = '2-3 Hari Kerja';
            }
        } else {
            $shippingCourier = 'STORE_PICKUP';
            $shippingService = 'INSTANT';
            $shippingCost = 0.0;
            $estimatedDelivery = 'Siap diambil dalam 2 jam setelah pembayaran terkonfirmasi';
        }

        // Diskon
        $discountAmount = 0.0;
        $validPromo = null;
        if ($promoCode) {
            $promo = Promotion::where('code', strtoupper($promoCode))->where('is_active', true)->first();
            if ($promo && $promo->isValidForAmount($subtotal)) {
                $discountAmount = $promo->calculateDiscount($subtotal);
                $validPromo = [
                    'code' => $promo->code,
                    'title' => $promo->title,
                    'discount_amount' => $discountAmount,
                ];
            }
        }

        $totalAmount = max(0, ($subtotal + $shippingCost) - $discountAmount);

        return [
            'items' => $items,
            'fulfillment_type' => $fulfillmentType,
            'selected_address' => $address,
            'selected_store' => $store,
            'shipping' => [
                'courier' => $shippingCourier,
                'service' => $shippingService,
                'cost' => $shippingCost,
                'estimated_delivery' => $estimatedDelivery,
            ],
            'available_couriers' => $availableCouriers,
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'discount_amount' => $discountAmount,
            'applied_promo' => $validPromo,
            'total_amount' => $totalAmount,
            'available_payment_methods' => [
                ['code' => 'qris', 'name' => 'QRIS Instant (via Pakasir)', 'provider' => 'Pakasir', 'icon' => 'qr'],
                ['code' => 'bri_va', 'name' => 'BRI Virtual Account (via Pakasir)', 'provider' => 'Pakasir', 'icon' => 'bank'],
                ['code' => 'bni_va', 'name' => 'BNI Virtual Account (via Pakasir)', 'provider' => 'Pakasir', 'icon' => 'bank'],
                ['code' => 'permata_va', 'name' => 'Permata Virtual Account (via Pakasir)', 'provider' => 'Pakasir', 'icon' => 'bank'],
                ['code' => 'cod', 'name' => 'Cash On Delivery (Bayar di Tempat)', 'provider' => 'COD', 'icon' => 'cash'],
            ],
        ];
    }

    /**
     * Eksekusi konfirmasi pesanan (Create Order) dalam database transaction
     */
    public function placeOrder(User $user, array $validated): Order
    {
        $cart = Cart::where('user_id', $user->id)->with(['items.product', 'items.variant'])->first();

        if (!$cart || $cart->items->isEmpty()) {
            throw new Exception('Keranjang belanja kosong. Tidak dapat memproses pesanan.');
        }

        return DB::transaction(function () use ($user, $cart, $validated) {
            // Verifikasi stok
            foreach ($cart->items as $item) {
                $stock = $item->variant ? $item->variant->stock : $item->product->stock;
                if ($stock < $item->quantity) {
                    throw new Exception("Stok produk '{$item->product->name}' tidak mencukupi untuk diproses.");
                }
            }

            // Subtotal
            $subtotal = 0.0;
            foreach ($cart->items as $item) {
                $unitPrice = $item->variant ? $item->variant->final_price : $item->product->final_price;
                $subtotal += ($unitPrice * $item->quantity);
            }

            // Ongkir & snapshot alamat
            $shippingCost = 0.0;
            $estimatedDelivery = null;
            $addressSnapshot = null;
            $courier = $validated['shipping_courier'] ?? 'JNE';
            $service = $validated['shipping_service'] ?? 'REG';

            if ($validated['fulfillment_type'] === 'home_delivery') {
                $address = UserAddress::findOrFail($validated['address_id']);
                $addressSnapshot = $address->toArray();
                $destinationCity = $address->city ?? 'Jakarta Selatan';

                // Hitung ongkir dinamis via KiriminAja agregator
                $rates = $this->kiriminAjaService->getShippingRates($destinationCity, 1000);
                $matchedRate = collect($rates)->first(function ($r) use ($courier, $service) {
                    return strcasecmp($r['courier'], $courier) === 0 && strcasecmp($r['service'], $service) === 0;
                }) ?? ($rates[0] ?? null);

                if ($matchedRate) {
                    $shippingCost = (float) $matchedRate['cost'];
                    $estimatedDelivery = $matchedRate['etd'] ?? '2-3 Hari Kerja';
                    $courier = $matchedRate['courier'] ?? $courier;
                    $service = $matchedRate['service'] ?? $service;
                } else {
                    $shippingCost = match ($service) {
                        'YES', 'EXPRESS' => 28000.0,
                        'SAMEDAY' => 35000.0,
                        default => 15000.0,
                    };
                    $estimatedDelivery = match ($service) {
                        'YES', 'EXPRESS' => '1 Hari Kerja',
                        'SAMEDAY' => 'Hari Ini',
                        default => '2-3 Hari Kerja',
                    };
                }
            } else {
                $courier = 'STORE_PICKUP';
                $service = 'INSTANT';
                $shippingCost = 0.0;
                $estimatedDelivery = 'Siap diambil di Toko';
            }

            // Diskon
            $discountAmount = 0.0;
            $promoCode = $validated['promo_code'] ?? $cart->applied_promo_code;
            if ($promoCode) {
                $promo = Promotion::where('code', strtoupper($promoCode))->where('is_active', true)->lockForUpdate()->first();
                if ($promo && $promo->isValidForAmount($subtotal)) {
                    $discountAmount = $promo->calculateDiscount($subtotal);
                    $promo->increment('used_count');
                }
            }

            $totalAmount = max(0, ($subtotal + $shippingCost) - $discountAmount);

            $orderNumber = 'AP-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            $pickupCode = ($validated['fulfillment_type'] === 'store_pickup') ? 'PCK-' . strtoupper(Str::random(6)) : null;

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $user->id,
                'fulfillment_type' => $validated['fulfillment_type'],
                'store_id' => $validated['store_id'] ?? null,
                'address_id' => $validated['address_id'] ?? null,
                'shipping_address_snapshot' => $addressSnapshot,
                'shipping_courier' => $courier,
                'shipping_service' => $service,
                'shipping_cost' => $shippingCost,
                'estimated_delivery' => $estimatedDelivery,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'promo_code' => $promoCode,
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'unpaid',
                'order_status' => 'pending_payment',
                'tracking_number' => null,
                'pickup_code' => $pickupCode,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Items & Pengurangan Stok
            foreach ($cart->items as $item) {
                $unitPrice = $item->variant ? $item->variant->final_price : $item->product->final_price;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'product_name' => $item->product->name,
                    'variant_name' => $item->variant?->name,
                    'product_image' => $item->product->primary_image,
                    'price' => $unitPrice,
                    'quantity' => $item->quantity,
                    'subtotal' => $unitPrice * $item->quantity,
                ]);

                if ($item->variant) {
                    $item->variant->decrement('stock', $item->quantity);
                }
                $item->product->decrement('stock', $item->quantity);
            }

            // History awal
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'pending_payment',
                'title' => 'Menunggu Pembayaran',
                'description' => 'Pesanan berhasil dibuat. Silakan selesaikan pembayaran.',
            ]);

            // Inisialisasi Transaksi Pembayaran via Pakasir Gateway
            $this->pakasirService->createPayment($order, $validated['payment_method']);

            // Bersihkan keranjang
            $cart->items()->delete();
            $cart->update(['applied_promo_code' => null]);

            // Notifikasi
            Notification::create([
                'user_id' => $user->id,
                'type' => 'order',
                'title' => 'Pesanan Dibuat: #' . $order->order_number,
                'message' => 'Pesanan Anda berhasil dibuat dengan total Rp ' . number_format($totalAmount, 0, ',', '.') . '.',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => $totalAmount,
                ],
            ]);

            return $order->load(['items', 'payment', 'store', 'address']);
        });
    }
}

