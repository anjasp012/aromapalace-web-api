<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function show(int $id): View
    {
        $order = Order::with(['user', 'items.product', 'items.variant', 'store', 'address', 'payment', 'statusHistories'])
            ->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'order_status' => 'required|in:processing,shipped,ready_for_pickup,completed,cancelled',
            'tracking_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $order = Order::findOrFail($id);

        try {
            $this->orderService->adminUpdateStatus(
                $order,
                $validated['order_status'],
                $validated['tracking_number'] ?? null,
                $validated['notes'] ?? null
            );

            return back()->with('success', "Status pesanan #{$order->order_number} berhasil diperbarui.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Request booking kurir dan terbitkan nomor resi otomatis via KiriminAja
     */
    public function generateKiriminAjaAwb(int $id, \App\Services\KiriminAjaShippingService $kiriminAjaService): RedirectResponse
    {
        $order = Order::findOrFail($id);

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
}

