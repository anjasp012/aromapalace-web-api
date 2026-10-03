<?php

namespace App\Http\Controllers\Api;

use App\Services\PaymentService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends BaseApiController
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function status(string $orderNumber): JsonResponse
    {
        try {
            $status = $this->paymentService->getStatus($orderNumber);
            return $this->sendResponse($status, 'Status pembayaran pesanan.');
        } catch (Exception $e) {
            return $this->sendError('Pesanan tidak ditemukan.', [], 404);
        }
    }

    public function simulatePay(Request $request, string $orderNumber): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:success,failed',
        ]);

        try {
            if ($validated['status'] === 'failed') {
                $order = $this->paymentService->processFailure($orderNumber);
                return $this->sendResponse([
                    'order_number' => $order->order_number,
                    'payment_status' => 'failed',
                    'order_status' => 'cancelled',
                ], 'Status pembayaran diperbarui menjadi GAGAL.');
            }

            $order = $this->paymentService->processSettlement($orderNumber);
            return $this->sendResponse([
                'order_number' => $order->order_number,
                'payment_status' => 'paid',
                'order_status' => $order->order_status,
            ], 'Pembayaran berhasil dikonfirmasi.');
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), [], 400);
        }
    }

    public function webhook(Request $request): JsonResponse
    {
        $orderNumber = $request->input('order_id') ?? $request->input('external_id');
        $transactionStatus = $request->input('transaction_status') ?? $request->input('status');

        if (!$orderNumber) {
            return $this->sendError('Missing order identifier.', [], 400);
        }

        try {
            if (in_array($transactionStatus, ['settlement', 'capture', 'COMPLETED', 'PAID'])) {
                $this->paymentService->processSettlement($orderNumber);
            } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire', 'FAILED'])) {
                $this->paymentService->processFailure($orderNumber);
            }
            return response()->json(['status' => 'ok']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Webhook settlement resmi dari Pakasir Payment Gateway
     */
    public function pakasirWebhook(Request $request, \App\Services\PakasirPaymentService $pakasirService): JsonResponse
    {
        try {
            $payload = $request->all();
            $signature = $request->header('X-Signature') ?? $request->header('x-signature');
            $secret = $request->header('X-Secret') ?? $request->header('x-secret');

            if (!$pakasirService->verifyWebhook($payload, $signature, $secret)) {
                return response()->json(['status' => 'error', 'message' => 'Invalid Pakasir secret or signature'], 403);
            }

            $result = $pakasirService->processWebhook($payload);
            return response()->json($result);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }
}
