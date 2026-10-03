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
        $orders = $this->orderService->getUserOrders($user, $status, 8)->withQueryString();
        $stats = $this->getAccountStats($user, $membershipStatus);

        return view('web.account.orders', compact('user', 'membershipStatus', 'orders', 'status', 'stats'));
    }

    public function orderShow(string $orderNumber): View
    {
        $user = auth()->user();
        $order = $this->orderService->getOrderDetail($user, $orderNumber);
        $tracking = $this->orderService->getTrackingInfo($user, $orderNumber);

        return view('web.account.order-detail', compact('order', 'tracking'));
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

