<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends BaseApiController
{
    /**
     * Tampilkan detail profil user
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load(['membership', 'addresses']);

        return $this->sendResponse($user, 'Profil pengguna.');
    }

    /**
     * Update informasi pribadi user
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:20|unique:users,phone,' . $user->id,
            'birthdate' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'avatar' => 'nullable|string', // URL string atau base64
        ]);

        $user->update($validated);

        return $this->sendResponse($user, 'Profil berhasil diperbarui.');
    }

    /**
     * Ganti password user
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->sendError('Password saat ini tidak cocok.', [], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return $this->sendResponse(null, 'Password berhasil diubah.');
    }

    /**
     * Riwayat transaksi singkat di halaman profil
     */
    public function transactionHistory(Request $request): JsonResponse
    {
        $orders = $request->user()
            ->orders()
            ->with(['items'])
            ->latest()
            ->paginate($request->input('per_page', 10));

        return $this->sendResponse($orders, 'Riwayat transaksi pengguna.');
    }
}

