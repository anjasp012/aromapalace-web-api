<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');
        $orders = $this->orderService->adminGetAllOrders($status, $search, 12)->withQueryString();

        $counts = [
            'all' => Order::count(),
            'pending_payment' => Order::where('order_status', 'pending_payment')->count(),
            'processing' => Order::where('order_status', 'processing')->count(),
            'shipped' => Order::where('order_status', 'shipped')->count(),
            'ready_for_pickup' => Order::where('order_status', 'ready_for_pickup')->count(),
            'completed' => Order::where('order_status', 'completed')->count(),
            'cancelled' => Order::where('order_status', 'cancelled')->count(),
        ];

        return view('admin.orders.index', compact('orders', 'counts', 'status', 'search'));
    }

    public function show(string|int $id): View
    {
        $order = $this->findOrder($id);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Terima Pesanan: Otomatis terbitkan resi pengiriman kurir via KiriminAja & ubah status pesanan
     */
    public function acceptOrder(string|int $id, \App\Services\KiriminAjaShippingService $kiriminAjaService): RedirectResponse
    {
        $order = $this->findOrder($id);

        $shipmentInfo = null;

        try {
            DB::transaction(function () use ($order, $kiriminAjaService, &$shipmentInfo) {
                $trackingNumber = $order->tracking_number;
                $orderStatus = 'processing';

                // 1. Jika penjemputan butik vs pengiriman kurir
                if (in_array($order->fulfillment_type, ['store_pickup', 'pickup'])) {
                    if (empty($order->pickup_code)) {
                        $order->pickup_code = 'PCK-' . strtoupper(Str::random(6));
                    }
                    $orderStatus = 'ready_for_pickup';
                } else {
                    // Pengiriman kurir (home_delivery): otomatis terbitkan resi via KiriminAja
                    if (empty($trackingNumber)) {
                        $shipmentInfo = $kiriminAjaService->createShipment($order);
                        $trackingNumber = $shipmentInfo['tracking_number'] ?? null;
                    }
                    $orderStatus = 'shipped';
                }

                // 2. Set status pesanan dan pembayaran (Lunas)
                $order->update([
                    'order_status' => $orderStatus,
                    'payment_status' => 'paid',
                    'tracking_number' => $trackingNumber,
                    'pickup_code' => $order->pickup_code ?? null,
                ]);

                if ($order->payment) {
                    $order->payment->update(['transaction_status' => 'settlement']);
                }

                // 3. Catat history log
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => $orderStatus,
                    'title' => 'Pesanan Diterima oleh Butik',
                    'description' => $trackingNumber
                        ? "Pesanan telah diterima. Resi kurir otomatis telah diterbitkan: {$trackingNumber}."
                        : "Pesanan telah diterima dan siap untuk diambil di butik.",
                ]);
            });

            $order->refresh();
            $msg = "Pesanan #{$order->order_number} berhasil DITERIMA!";
            if ($order->tracking_number) {
                $msg .= " Nomor Resi Otomatis: {$order->tracking_number} ({$order->shipping_courier})";
                if (!empty($shipmentInfo['adapted'])) {
                    $msg .= " - [Catatan: Kurir otomatis dialihkan ke {$order->shipping_courier} karena {$shipmentInfo['original_courier']} tidak aktif di KiriminAja]";
                }
            } elseif ($order->pickup_code) {
                $msg .= " Kode Pickup Butik: {$order->pickup_code}";
            }

            return back()->with('success', $msg);
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memproses penerimaan pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Tolak Pesanan: Batalkan pesanan dan otomatis kembalikan stok produk ke etalase
     */
    public function rejectOrder(Request $request, string|int $id, \App\Services\KiriminAjaShippingService $kiriminAjaService): RedirectResponse
    {
        $order = $this->findOrder($id);
        $reason = $request->input('reason', 'Pesanan ditolak oleh pihak butik / admin.');

        try {
            // Batalkan shipment di KiriminAja jika resi sudah pernah diterbitkan
            if (!empty($order->tracking_number) && $order->fulfillment_type === 'home_delivery') {
                $kiriminAjaService->cancelShipment($order->tracking_number, $reason);
            }

            DB::transaction(function () use ($order, $reason) {
                // 1. Kembalikan stok produk jika belum pernah dibatalkan
                if ($order->order_status !== 'cancelled') {
                    foreach ($order->items as $item) {
                        if ($item->variant) {
                            $item->variant->increment('stock', $item->quantity);
                        }
                        if ($item->product) {
                            $item->product->increment('stock', $item->quantity);
                        }
                    }
                }

                // 2. Tandai cancelled
                $order->update([
                    'order_status' => 'cancelled',
                    'cancelled_at' => now(),
                    'notes' => $order->notes ? ($order->notes . ' | Alasan Ditolak: ' . $reason) : ('Alasan Ditolak: ' . $reason),
                ]);

                // 3. Catat history
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => 'cancelled',
                    'title' => 'Pesanan Ditolak',
                    'description' => $reason,
                ]);
            });

            return back()->with('success', "Pesanan #{$order->order_number} telah DITOLAK. Stok produk berhasil dikembalikan.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal menolak pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Tandai pesanan selesai
     */
    public function completeOrder(string|int $id): RedirectResponse
    {
        $order = $this->findOrder($id);

        try {
            $order->update([
                'order_status' => 'completed',
                'payment_status' => 'paid',
                'completed_at' => now(),
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'completed',
                'title' => 'Pesanan Selesai',
                'description' => 'Pesanan telah selesai dan barang telah diterima pelanggan dengan baik.',
            ]);

            return back()->with('success', "Pesanan #{$order->order_number} telah selesai diproses.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal menyelesaikan pesanan: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, string|int $id): RedirectResponse
    {
        $validated = $request->validate([
            'order_status' => 'required|in:pending_payment,processing,shipped,ready_for_pickup,completed,cancelled',
            'payment_status' => 'nullable|in:unpaid,paid,refunded',
            'tracking_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $order = $this->findOrder($id);

        try {
            $this->orderService->adminUpdateStatus(
                $order,
                $validated['order_status'],
                $validated['tracking_number'] ?? null,
                $validated['notes'] ?? null
            );

            if (!empty($validated['payment_status']) && $validated['payment_status'] !== $order->payment_status) {
                $order->update(['payment_status' => $validated['payment_status']]);
                if ($order->payment) {
                    $order->payment->update([
                        'transaction_status' => $validated['payment_status'] === 'paid' ? 'settlement' : 'pending'
                    ]);
                }
            }

            return back()->with('success', "Status pesanan #{$order->order_number} berhasil diperbarui.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Request booking kurir dan terbitkan nomor resi otomatis via KiriminAja
     */
    public function generateKiriminAjaAwb(string|int $id, \App\Services\KiriminAjaShippingService $kiriminAjaService): RedirectResponse
    {
        $order = $this->findOrder($id);

        if ($order->fulfillment_type !== 'home_delivery') {
            return back()->with('error', 'Pesanan ini bertipe Click & Collect (Store Pickup), tidak memerlukan resi kurir.');
        }

        try {
            $res = $kiriminAjaService->createShipment($order);
            return back()->with('success', "Resi resmi {$res['courier']} berhasil diterbitkan via KiriminAja: {$res['tracking_number']}");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal menerbitkan resi KiriminAja: ' . $e->getMessage());
        }
    }

    /**
     * Cetak Thermal/PDF Shipping Label AWB via KiriminAja SDK
     */
    public function printKiriminAjaAwb(string|int $id, \App\Services\KiriminAjaShippingService $kiriminAjaService): RedirectResponse
    {
        $order = $this->findOrder($id);

        if (empty($order->tracking_number)) {
            return back()->with('error', 'Nomor resi belum diterbitkan untuk pesanan ini.');
        }

        $labelUrl = $kiriminAjaService->printShippingLabel($order->tracking_number);
        if ($labelUrl && filter_var($labelUrl, FILTER_VALIDATE_URL)) {
            return redirect()->away($labelUrl);
        }

        return back()->with('info', "Label pengiriman KiriminAja untuk resi {$order->tracking_number} siap dicetak via invoice butik.");
    }

    /**
     * Cari pesanan berdasarkan ID numerik atau Order Number
     */
    protected function findOrder(string|int $id): Order
    {
        return Order::with(['user', 'items.product', 'items.variant', 'store', 'address', 'payment', 'statusHistories'])
            ->where(function ($q) use ($id) {
                if (is_numeric($id)) {
                    $q->where('id', (int) $id)->orWhere('order_number', (string) $id);
                } else {
                    $q->where('order_number', (string) $id);
                }
            })
            ->firstOrFail();
    }
}

