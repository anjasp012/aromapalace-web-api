<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Store;
use App\Services\KiriminAjaShippingService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryController extends BaseApiController
{
    public function __construct(
        protected KiriminAjaShippingService $kiriminAjaService
    ) {}

    /**
     * Pilihan Delivery & Kurir yang tersedia (KiriminAja Aggregator)
     */
    public function options(Request $request): JsonResponse
    {
        $city = $request->query('city', 'Jakarta Selatan');
        $weight = (int) $request->query('weight', 1000);

        $courierRates = $this->kiriminAjaService->getShippingRates($city, $weight);

        // Daftar Toko yang menyediakan Store Pickup (Click & Collect)
        $pickupStores = Store::withCoordinates()
            ->where('is_pickup_available', true)
            ->get();

        return $this->sendResponse([
            'home_delivery' => [
                'is_available' => true,
                'provider' => 'KiriminAja',
                'courier_rates' => $courierRates,
            ],
            'store_pickup' => [
                'is_available' => true,
                'cost' => 0,
                'lead_time' => '2 Jam Kerja setelah pembayaran',
                'stores' => $pickupStores,
            ],
        ], 'Opsi pengiriman KiriminAja dan store pickup.');
    }

    /**
     * Cek status store pickup pesanan tertentu
     */
    public function pickupStatus(Request $request, string $orderNumber): JsonResponse
    {
        $order = Order::with('store')
            ->where('user_id', $request->user()->id)
            ->where('order_number', $orderNumber)
            ->where('fulfillment_type', 'store_pickup')
            ->first();

        if (!$order) {
            return $this->sendError('Pesanan store pickup tidak ditemukan.', [], 404);
        }

        $statusLabel = match ($order->order_status) {
            'pending_payment' => 'Menunggu Pembayaran',
            'processing' => 'Sedang Dipersiapkan oleh Toko',
            'ready_for_pickup' => 'Siap Diambil di Toko',
            'completed' => 'Sudah Selesai Diambil',
            'cancelled' => 'Dibatalkan',
            default => $order->order_status,
        };

        return $this->sendResponse([
            'order_number' => $order->order_number,
            'pickup_code' => $order->pickup_code,
            'pickup_status' => $statusLabel,
            'store' => $order->store,
            'is_ready_to_collect' => ($order->order_status === 'ready_for_pickup'),
            'instructions' => 'Tunjukkan QR code atau kode pickup ini kepada staf toko Aroma Palace saat pengambilan barang.',
        ], 'Status store pickup pesanan.');
    }

    /**
     * Webhook tracking event dari KiriminAja
     */
    public function kiriminajaWebhook(Request $request): JsonResponse
    {
        try {
            $result = $this->kiriminAjaService->handleTrackingWebhook($request->all());
            return response()->json($result);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }
}
