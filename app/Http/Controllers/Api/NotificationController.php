<?php

namespace App\Http\Controllers\Api;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends BaseApiController
{
    /**
     * Daftar notifikasi user dengan pagination dan unread counter
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $type = $request->query('type'); // order, promo, payment, delivery, pickup, membership, reward

        $query = Notification::where('user_id', $user->id);

        if ($type) {
            $query->where('type', $type);
        }

        $perPage = (int) $request->query('per_page', 20);
        $notifications = $query->latest()->paginate($perPage);

        $unreadCount = Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return $this->sendResponse([
            'unread_count' => $unreadCount,
            'notifications' => $notifications,
        ], 'Daftar notifikasi pengguna.');
    }

    /**
     * Tandai satu notifikasi sebagai telah dibaca
     */
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $notification = Notification::where('user_id', $request->user()->id)->find($id);

        if (!$notification) {
            return $this->sendError('Notifikasi tidak ditemukan.', [], 404);
        }

        $notification->update(['read_at' => now()]);

        return $this->sendResponse($notification, 'Notifikasi ditandai telah dibaca.');
    }

    /**
     * Tandai seluruh notifikasi user sebagai telah dibaca
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        Notification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return $this->sendResponse(null, 'Semua notifikasi telah ditandai dibaca.');
    }
}

