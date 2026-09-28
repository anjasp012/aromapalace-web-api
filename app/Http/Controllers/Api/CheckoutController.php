<?php

namespace App\Http\Controllers\Api;

use App\Services\CheckoutService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends BaseApiController
{
    public function __construct(
        protected CheckoutService $checkoutService
    ) {}

    public function preview(Request $request): JsonResponse
    {
        try {
            $preview = $this->checkoutService->previewCheckout($request->user(), $request->all());
            return $this->sendResponse($preview, 'Ringkasan checkout pesanan.');
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], 400);
        }
    }

    public function process(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fulfillment_type' => 'required|in:home_delivery,store_pickup',
            'address_id' => 'required_if:fulfillment_type,home_delivery|nullable|exists:user_addresses,id',
            'store_id' => 'required_if:fulfillment_type,store_pickup|nullable|exists:stores,id',
            'shipping_courier' => 'nullable|string',
            'shipping_service' => 'nullable|string',
            'payment_method' => 'required|string|in:bca_va,mandiri_va,qris,credit_card,cod',
            'promo_code' => 'nullable|string',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $order = $this->checkoutService->placeOrder($request->user(), $validated);
            return $this->sendResponse(['order' => $order], 'Pesanan berhasil dikonfirmasi. Silakan lakukan pembayaran.', 201);
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }
    }
}
