<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Ambil pesanan milik user
     */
    public function getUserOrders(User $user, ?string $status = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = Order::with(['items.product', 'items.variant', 'store', 'payment'])->where('user_id', $user->id);

        if ($status) {
            if ($status === 'processing') {
                $query->whereIn('order_status', ['pending_payment', 'processing', 'shipped', 'ready_for_pickup']);
            } elseif ($status === 'history' || $status === 'completed') {
                $query->whereIn('order_status', ['completed', 'cancelled']);
            } else {
                $query->where('order_status', $status);
            }
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Detail pesanan milik user
     */
    public function getOrderDetail(User $user, string $orderNumber): Order
    {
        return Order::with(['items.product', 'items.variant', 'store', 'address', 'payment', 'statusHistories'])
            ->where('user_id', $user->id)
            ->where('order_number', $orderNumber)
            ->firstOrFail();
    }

    /**
     * Informasi tracking pengiriman pesanan
     */
    public function getTrackingInfo(User $user, string $orderNumber): array
    {
        $order = $this->getOrderDetail($user, $orderNumber);

        $timeline = [];
        foreach ($order->statusHistories as $history) {
            $timeline[] = [
                'status' => $history->status,
                'title' => $history->title,
                'description' => $history->description,
                'time' => $history->created_at->format('d M Y, H:i'),
            ];
        }

        return [
            'order_number' => $order->order_number,
            'fulfillment_type' => $order->fulfillment_type,
            'order_status' => $order->order_status,
            'courier' => $order->shipping_courier,
            'tracking_number' => $order->tracking_number ?? 'Belum diterbitkan',
            'pickup_code' => $order->pickup_code,
            'store' => $order->store,
            'estimated_delivery' => $order->estimated_delivery,
            'timeline' => $timeline,
        ];
    }

    /**
     * Batalkan pesanan oleh customer
     */
    public function cancelOrder(User $user, string $orderNumber): Order
    {
        $order = Order::where('user_id', $user->id)->where('order_number', $orderNumber)->firstOrFail();

        if (!in_array($order->order_status, ['pending_payment'])) {
            throw new Exception('Pesanan yang sedang diproses atau sudah dikirim tidak dapat dibatalkan.');
        }

        return DB::transaction(function () use ($order) {
            $order->update([
                'order_status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'cancelled',
                'title' => 'Pesanan Dibatalkan',
                'description' => 'Pesanan dibatalkan oleh pengguna.',
            ]);

            // Kembalikan stok
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    $item->variant?->increment('stock', $item->quantity);
                }
                $item->product?->increment('stock', $item->quantity);
            }

            Notification::create([
                'user_id' => $order->user_id,
                'type' => 'order',
                'title' => "Pesanan Dibatalkan: #{$order->order_number}",
                'message' => 'Pesanan Anda telah berhasil dibatalkan.',
                'data' => ['order_number' => $order->order_number],
            ]);

            return $order;
        });
    }

    /**
     * Admin: Daftar seluruh pesanan dari semua customer
     */
    public function adminGetAllOrders(?string $status = null, ?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = Order::with(['user', 'items.product', 'store', 'payment']);

        if ($status && $status !== 'all') {
            $query->where('order_status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'ilike', "%{$search}%")
                  ->orWhere('tracking_number', 'ilike', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'ilike', "%{$search}%")->orWhere('email', 'ilike', "%{$search}%"));
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Admin: Update status pesanan (e.g. processing -> shipped / ready_for_pickup -> completed)
     */
    public function adminUpdateStatus(Order $order, string $newStatus, ?string $trackingNumber = null, ?string $notes = null): Order
    {
        return DB::transaction(function () use ($order, $newStatus, $trackingNumber, $notes) {
            $updateData = ['order_status' => $newStatus];

            if ($trackingNumber) {
                $updateData['tracking_number'] = $trackingNumber;
            }

            if ($newStatus === 'completed') {
                $updateData['completed_at'] = now();
            } elseif ($newStatus === 'cancelled') {
                $updateData['cancelled_at'] = now();
            }

            $order->update($updateData);

            $statusTitles = [
                'processing' => 'Pesanan Diproses',
                'shipped' => 'Pesanan Telah Dikirim',
                'ready_for_pickup' => 'Siap Diambil di Toko',
                'completed' => 'Pesanan Selesai',
                'cancelled' => 'Pesanan Dibatalkan',
            ];

            $title = $statusTitles[$newStatus] ?? 'Pembaruan Status Pesanan';
            $description = $notes ?? match ($newStatus) {
                'processing' => 'Pesanan Anda sedang dipersiapkan dan dikemas dengan rapi oleh tim kami.',
                'shipped' => "Pesanan telah diserahkan ke kurir ({$order->shipping_courier}) dengan no resi: {$trackingNumber}.",
                'ready_for_pickup' => "Pesanan telah siap diambil di gerai {$order->store?->name}. Tunjukkan kode pickup: {$order->pickup_code}.",
                'completed' => 'Pesanan telah diterima. Terima kasih telah berbelanja di Aroma Palace!',
                'cancelled' => 'Pesanan telah dibatalkan.',
                default => "Status pesanan diubah menjadi {$newStatus}.",
            };

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $newStatus,
                'title' => $title,
                'description' => $description,
            ]);

            // Kirim notifikasi ke customer
            Notification::create([
                'user_id' => $order->user_id,
                'type' => 'order',
                'title' => "Update Pesanan: #{$order->order_number}",
                'message' => $description,
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'order_status' => $newStatus,
                ],
            ]);

            return $order->fresh(['items', 'user', 'store', 'statusHistories']);
        });
    }
}

