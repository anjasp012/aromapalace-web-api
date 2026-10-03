<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\PointHistory;
use Exception;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Cek status pembayaran pesanan
     */
    public function getStatus(string $orderNumber): array
    {
        $order = Order::with('payment')->where('order_number', $orderNumber)->firstOrFail();

        return [
            'order_number' => $order->order_number,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
            'order_status' => $order->order_status,
            'total_amount' => $order->total_amount,
            'payment' => $order->payment,
        ];
    }

    /**
     * Konfirmasi pembayaran berhasil (Settlement)
     */
    public function processSettlement(string $orderNumber, ?string $transactionId = null, ?string $paymentType = null): Order
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        if ($order->payment_status === 'paid') {
            return $order;
        }

        return DB::transaction(function () use ($order, $transactionId, $paymentType) {
            $newOrderStatus = ($order->fulfillment_type === 'store_pickup') ? 'ready_for_pickup' : 'processing';

            $order->update([
                'payment_status' => 'paid',
                'order_status' => $newOrderStatus,
                'paid_at' => now(),
            ]);

            $paymentUpdate = [
                'transaction_status' => 'settlement',
                'paid_at' => now(),
            ];
            if ($transactionId && empty($order->payment?->transaction_id)) {
                $paymentUpdate['transaction_id'] = $transactionId;
            }
            if ($paymentType && empty($order->payment?->payment_type)) {
                $paymentUpdate['payment_type'] = $paymentType;
            }

            $order->payment?->update($paymentUpdate);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $newOrderStatus,
                'title' => 'Pembayaran Berhasil',
                'description' => ($order->fulfillment_type === 'store_pickup')
                    ? 'Pembayaran berhasil dikonfirmasi. Pesanan siap diambil di toko pilihan Anda.'
                    : 'Pembayaran berhasil dikonfirmasi. Pesanan sedang dipersiapkan oleh tim gudang.',
            ]);

            // Loyalty points (1 poin per Rp 10.000 belanja)
            $earnedPoints = (int) floor($order->total_amount / 10000);
            if ($earnedPoints > 0) {
                $membership = Membership::firstOrCreate(['user_id' => $order->user_id]);
                $membership->increment('points', $earnedPoints);
                $membership->increment('total_spent', $order->total_amount);

                PointHistory::create([
                    'user_id' => $order->user_id,
                    'type' => 'earned',
                    'points' => $earnedPoints,
                    'balance_after' => $membership->points,
                    'reference_type' => 'order',
                    'reference_id' => $order->id,
                    'description' => "Poin transaksi pesanan #{$order->order_number}",
                ]);
            }

            Notification::create([
                'user_id' => $order->user_id,
                'type' => 'payment',
                'title' => "Pembayaran Diterima: #{$order->order_number}",
                'message' => 'Pembayaran pesanan Anda telah berhasil dikonfirmasi.' .
                    ($earnedPoints > 0 ? " Anda memperoleh {$earnedPoints} poin membership!" : ''),
                'data' => [
                    'order_number' => $order->order_number,
                    'earned_points' => $earnedPoints,
                ],
            ]);

            return $order->fresh(['payment', 'items', 'store']);
        });
    }

    /**
     * Konfirmasi pembayaran gagal / kedaluwarsa
     */
    public function processFailure(string $orderNumber): Order
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        return DB::transaction(function () use ($order) {
            $order->update([
                'payment_status' => 'failed',
                'order_status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            $order->payment?->update([
                'transaction_status' => 'failure',
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'cancelled',
                'title' => 'Pembayaran Gagal',
                'description' => 'Pembayaran pesanan tidak berhasil atau telah kedaluwarsa.',
            ]);

            Notification::create([
                'user_id' => $order->user_id,
                'type' => 'payment',
                'title' => "Pembayaran Gagal: #{$order->order_number}",
                'message' => 'Pembayaran pesanan Anda tidak berhasil diproses.',
                'data' => ['order_number' => $order->order_number],
            ]);

            return $order;
        });
    }
}

