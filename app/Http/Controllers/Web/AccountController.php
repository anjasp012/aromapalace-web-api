<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use App\Services\AddressService;
use App\Services\MembershipService;
use App\Services\OrderService;
use Exception;
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

    public function index(): View
    {
        $user = auth()->user();
        $membershipStatus = $this->membershipService->getStatus($user);
        $recentOrders = $this->orderService->getUserOrders($user, null, 5);
        $primaryAddress = $user->primaryAddress;
        $stats = [
            'total_orders' => $user->orders()->count(),
            'pending_orders' => $user->orders()->whereIn('order_status', ['pending_payment', 'processing', 'shipped', 'ready_for_pickup'])->count(),
            'point_history_count' => $user->pointHistories()->count(),
            'points' => $membershipStatus['points'] ?? 0,
        ];

        return view('web.account.index', compact('user', 'membershipStatus', 'recentOrders', 'primaryAddress', 'stats'));
    }

    public function orders(Request $request): View
    {
        $status = $request->query('status');
        $orders = $this->orderService->getUserOrders(auth()->user(), $status, 8)->withQueryString();

        return view('web.account.orders', compact('orders', 'status'));
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

        return view('web.account.rewards', compact('membershipStatus', 'rewards', 'pointHistories'));
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

        return view('web.account.points', compact('user', 'membershipStatus', 'pointHistories', 'earnedPoints', 'redeemedPoints'));
    }

    public function addresses(): View
    {
        $addresses = $this->addressService->getUserAddresses(auth()->user());
        return view('web.account.addresses', compact('addresses'));
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'label' => 'nullable|string|max:50',
            'recipient_name' => 'required|string|max:100',
            'phone_number' => 'required|string|max:20',
            'full_address' => 'required|string',
            'city' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'notes' => 'nullable|string|max:255',
        ]);

        $this->addressService->createAddress(auth()->user(), $validated);

        return back()->with('success', 'Alamat pengiriman baru berhasil disimpan.');
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

