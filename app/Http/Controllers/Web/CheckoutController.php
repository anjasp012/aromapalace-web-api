<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\CheckoutService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        protected CheckoutService $checkoutService
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $user = auth()->user();

        try {
            $preview = $this->checkoutService->previewCheckout($user, $request->all());
            $addresses = $user->addresses()->get();
            $stores = Store::withCoordinates()->where('is_pickup_available', true)->get();

            return view('web.checkout.index', compact('preview', 'addresses', 'stores'));
        } catch (Exception $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Endpoint API kalkulasi dinamis untuk Alpine.js di halaman Checkout
     */
    public function calculate(Request $request): JsonResponse
    {
        try {
            $preview = $this->checkoutService->previewCheckout(auth()->user(), $request->all());
            return response()->json([
                'success' => true,
                'data' => $preview,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function process(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'fulfillment_type' => 'required|in:home_delivery,store_pickup',
            'address_id' => 'required_if:fulfillment_type,home_delivery|nullable|exists:user_addresses,id',
            'store_id' => 'required_if:fulfillment_type,store_pickup|nullable|exists:stores,id',
            'shipping_courier' => 'nullable|string',
            'shipping_service' => 'nullable|string',
            'payment_method' => 'required|string|in:qris,bca_va,mandiri_va,bni_va,bri_va,permata_va,maybank_va,bnc_va,credit_card,cod',
            'promo_code' => 'nullable|string',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $order = $this->checkoutService->placeOrder(auth()->user(), $validated);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'order' => $order,
                    'redirect_url' => route('account.orders.show', $order->order_number),
                ]);
            }

            return redirect()->route('account.orders.show', $order->order_number)
                ->with('success', "Pesanan #{$order->order_number} berhasil dibuat via Pakasir! Silakan selesaikan pembayaran.");
        } catch (Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}
