<?php

namespace App\Http\Controllers\Api;

use App\Services\OrderService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends BaseApiController
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderService->getUserOrders(
            $request->user(),
            $request->query('status'),
            (int) $request->query('per_page', 10)
        );
        return $this->sendResponse($orders, 'Daftar pesanan pengguna.');
    }

    public function show(Request $request, string $orderNumber): JsonResponse
    {
        try {
            $order = $this->orderService->getOrderDetail($request->user(), $orderNumber);
            return $this->sendResponse($order, 'Detail pesanan.');
        } catch (Exception $e) {
            return $this->sendError('Pesanan tidak ditemukan.', [], 404);
        }
    }

    public function tracking(Request $request, string $orderNumber): JsonResponse
    {
        try {
            $tracking = $this->orderService->getTrackingInfo($request->user(), $orderNumber);
            return $this->sendResponse($tracking, 'Informasi tracking pesanan.');
        } catch (Exception $e) {
            return $this->sendError('Pesanan tidak ditemukan.', [], 404);
        }
    }

    public function cancel(Request $request, string $orderNumber): JsonResponse
    {
        try {
            $order = $this->orderService->cancelOrder($request->user(), $orderNumber);
            return $this->sendResponse($order, 'Pesanan berhasil dibatalkan.');
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], 400);
        }
    }
}
