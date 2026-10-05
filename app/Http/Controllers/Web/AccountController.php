<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use App\Services\AddressService;
use App\Services\MembershipService;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected MembershipService $membershipService,
        protected AddressService $addressService
    ) {}

    private function getAccountStats($user, array $membershipStatus): array
    {
        return [
            'total_orders' => $user->orders()->count(),
            'pending_orders' => $user->orders()->whereIn('order_status', ['pending_payment', 'processing', 'shipped', 'ready_for_pickup'])->count(),
            'point_history_count' => $user->pointHistories()->count(),
            'points' => $membershipStatus['points'] ?? 0,
        ];
    }

    public function index(): View
    {
        $user = auth()->user();
        $membershipStatus = $this->membershipService->getStatus($user);
        $recentOrders = $this->orderService->getUserOrders($user, null, 5);
        $primaryAddress = $user->primaryAddress;
        $stats = $this->getAccountStats($user, $membershipStatus);

        return view('web.account.index', compact('user', 'membershipStatus', 'recentOrders', 'primaryAddress', 'stats'));
    }

    public function orders(Request $request): View
    {
        $user = auth()->user();
        $membershipStatus = $this->membershipService->getStatus($user);
        $status = $request->query('status');
        $orders = $this->orderService->getUserOrders($user, null, 100)->withQueryString();
        $stats = $this->getAccountStats($user, $membershipStatus);

        $statusCounts = [
            'all' => $user->orders()->count(),
            'pending_payment' => $user->orders()->where('order_status', 'pending_payment')->count(),
            'processing' => $user->orders()->where('order_status', 'processing')->count(),
            'shipped' => $user->orders()->where('order_status', 'shipped')->count(),
            'delivered' => $user->orders()->where('order_status', 'delivered')->count(),
            'completed' => $user->orders()->where('order_status', 'completed')->count(),
            'cancelled' => $user->orders()->where('order_status', 'cancelled')->count(),
        ];

        return view('web.account.orders', compact('user', 'membershipStatus', 'orders', 'status', 'stats', 'statusCounts'));
    }

    public function orderShow(Request $request, string $orderNumber): View
    {
        $user = auth()->user();
        $order = $this->orderService->getOrderDetail($user, $orderNumber);
        $tracking = $this->orderService->getTrackingInfo($user, $orderNumber);

        if ($request->query('tracking_modal')) {
            return view('web.account.partials.tracking-modal-content', compact('order', 'tracking'));
        }

        if ($request->ajax() || $request->query('modal')) {
            return view('web.account.partials.transaction-modal-content', compact('order', 'tracking'));
        }

        return view('web.account.order-detail', compact('order', 'tracking'));
    }

    public function orderInvoice(string $orderNumber): View
    {
        $user = auth()->user();
        $order = $this->orderService->getOrderDetail($user, $orderNumber);
        $tracking = $this->orderService->getTrackingInfo($user, $orderNumber);

        return view('web.account.invoice', compact('order', 'tracking'));
    }

    public function orderCancel(string $orderNumber): RedirectResponse
    {
        try {
            $this->orderService->cancelOrder(auth()->user(), $orderNumber);
            return back()->with('success', 'Pesanan berhasil dibatalkan.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function orderCheckPayment(string $orderNumber, \App\Services\PakasirPaymentService $pakasirService): RedirectResponse
    {
        $user = auth()->user();
        $order = $this->orderService->getOrderDetail($user, $orderNumber);

        if ($order->payment_status === 'paid') {
            return back();
        }

        try {
            $result = $pakasirService->checkTransactionStatus($order);
            if ($result['paid']) {
                return back();
            }
            return back()->with('info', $result['message'] ?? 'Menunggu pembayaran diselesaikan.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memeriksa status pembayaran: ' . $e->getMessage());
        }
    }

    public function orderSimulatePayment(string $orderNumber, \App\Services\PaymentService $paymentService): RedirectResponse
    {
        $user = auth()->user();
        $order = $this->orderService->getOrderDetail($user, $orderNumber);

        if ($order->payment_status === 'paid') {
            return back();
        }

        try {
            $paymentService->processSettlement(
                $order->order_number,
                'PKS-SIM-' . strtoupper(\Illuminate\Support\Str::random(8)),
                $order->payment_method
            );
            return back();
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memproses simulasi: ' . $e->getMessage());
        }
    }

    public function orderRefreshTracking(string $orderNumber): RedirectResponse
    {
        $user = auth()->user();
        try {
            $tracking = $this->orderService->getTrackingInfo($user, $orderNumber);
            $courierStatus = $tracking['courier_tracking']['status_label'] ?? null;
            $location = $tracking['courier_tracking']['current_location'] ?? null;

            $msg = $courierStatus 
                ? "Status kurir terkini: {$courierStatus}" . ($location ? " ({$location})" : "")
                : "Informasi pelacakan kurir telah diperbarui.";

            return back()->with('success', $msg);
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memperbarui status kurir: ' . $e->getMessage());
        }
    }

    public function orderConfirmReceived(string $orderNumber): RedirectResponse
    {
        $user = auth()->user();
        $order = $this->orderService->getOrderDetail($user, $orderNumber);

        if (!in_array($order->order_status, ['shipped', 'delivered', 'ready_for_pickup'])) {
            return back()->with('error', 'Status pesanan belum memenuhi syarat konfirmasi penyelesaian.');
        }

        $order->update([
            'order_status' => 'completed',
            'completed_at' => now(),
        ]);

        \App\Models\OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => 'completed',
            'title' => 'Pesanan Diterima oleh Pelanggan',
            'description' => 'Pelanggan telah mengonfirmasi bahwa produk parfum telah diterima dalam kondisi baik.',
        ]);

        return back()->with('success', 'Terima kasih telah berbelanja di Aroma Palace! Pesanan Anda telah resmi selesai.');
    }

    public function rewards(): View
    {
        $user = auth()->user();
        $membershipStatus = $this->membershipService->getStatus($user);
        $rewards = $this->membershipService->getAvailableRewards();
        $pointHistories = $this->membershipService->getPointHistory($user, 10);
        $stats = $this->getAccountStats($user, $membershipStatus);

        return view('web.account.rewards', compact('user', 'membershipStatus', 'rewards', 'pointHistories', 'stats'));
    }

    public function redeemReward(int $id): RedirectResponse
    {
        try {
            $res = $this->membershipService->redeemReward(auth()->user(), $id);
            return back()->with('success', "Reward berhasil ditukarkan! Kode voucher Anda: {$res['voucher_code']}");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function points(): View
    {
        $user = auth()->user();
        $membershipStatus = $this->membershipService->getStatus($user);
        $pointHistories = $this->membershipService->getPointHistory($user, 15);
        $earnedPoints = (int) $user->pointHistories()->where('points', '>', 0)->sum('points');
        $redeemedPoints = (int) abs($user->pointHistories()->where('points', '<', 0)->sum('points'));
        $stats = $this->getAccountStats($user, $membershipStatus);

        return view('web.account.points', compact('user', 'membershipStatus', 'pointHistories', 'earnedPoints', 'redeemedPoints', 'stats'));
    }

    public function addresses(): View
    {
        $user = auth()->user();
        $membershipStatus = $this->membershipService->getStatus($user);
        $addresses = $this->addressService->getUserAddresses($user);
        $stats = $this->getAccountStats($user, $membershipStatus);

        return view('web.account.addresses', compact('user', 'membershipStatus', 'addresses', 'stats'));
    }

    public function storeAddress(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'label' => 'nullable|string|max:50',
            'recipient_name' => 'required|string|max:100',
            'phone_number' => 'required|string|max:20',
            'full_address' => 'required|string',
            'city' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'notes' => 'nullable|string|max:255',
            'is_primary' => 'nullable|boolean',
        ]);

        $address = $this->addressService->createAddress(auth()->user(), $validated);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Alamat baru berhasil disimpan.',
                'data' => $address,
            ]);
        }

        return back()->with('success', 'Alamat pengiriman baru berhasil disimpan.');
    }

    public function setPrimaryAddress(Request $request, int $id): JsonResponse|RedirectResponse
    {
        try {
            $address = $this->addressService->setPrimaryAddress(auth()->user(), $id);
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Alamat utama berhasil diperbarui.',
                    'data' => $address,
                ]);
            }
            return back()->with('success', 'Alamat utama berhasil diperbarui.');
        } catch (Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function deleteAddress(int $id): RedirectResponse
    {
        try {
            $this->addressService->deleteAddress(auth()->user(), $id);
            return back()->with('success', 'Alamat berhasil dihapus.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

