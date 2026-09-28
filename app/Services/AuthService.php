<?php

namespace App\Services;

use App\Models\Membership;
use App\Models\Notification;
use App\Models\PointHistory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        protected WishlistService $wishlistService
    ) {}

    /**
     * Daftarkan user baru dengan paket onboarding lengkap (Silver membership + 50 welcome points + notifikasi)
     */
    public function registerUser(array $data, array $guestWishlist = []): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'birthdate' => $data['birthdate'] ?? null,
            'gender' => $data['gender'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? 'customer',
        ]);

        // Default Membership Silver + 50 welcome points
        Membership::create([
            'user_id' => $user->id,
            'tier' => 'Silver',
            'points' => 50,
            'total_spent' => 0,
            'joined_at' => now(),
        ]);

        // Point History
        PointHistory::create([
            'user_id' => $user->id,
            'type' => 'bonus',
            'points' => 50,
            'balance_after' => 50,
            'reference_type' => 'signup',
            'description' => 'Bonus Selamat Datang di Aroma Palace',
        ]);

        // Welcome Notification
        Notification::create([
            'user_id' => $user->id,
            'type' => 'membership',
            'title' => 'Selamat Datang di Aroma Palace!',
            'message' => 'Anda mendapatkan 50 Loyalty Points sebagai hadiah member baru.',
            'data' => ['points' => 50, 'tier' => 'Silver'],
        ]);

        // Sinkronisasi guest wishlist jika ada
        if (!empty($guestWishlist)) {
            $this->wishlistService->syncGuestWishlistToUser($user, $guestWishlist);
        }

        return $user;
    }

    /**
     * Verifikasi kredensial login user
     */
    public function attemptLogin(string $email, string $password): ?User
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }

    /**
     * Buat Bearer Token untuk otentikasi Mobile API (Sanctum)
     */
    public function createApiToken(User $user, string $deviceName = 'Mobile/Web Client'): string
    {
        return $user->createToken($deviceName)->plainTextToken;
    }

    /**
     * Sinkronisasi session/guest wishlist ke user database
     */
    public function syncWishlist(User $user, array $guestWishlist): int
    {
        return $this->wishlistService->syncGuestWishlistToUser($user, $guestWishlist);
    }
}

