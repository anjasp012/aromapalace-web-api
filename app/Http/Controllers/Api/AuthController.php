<?php

namespace App\Http\Controllers\Api;

use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends BaseApiController
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Registrasi user baru
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'birthdate' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'device_name' => 'nullable|string',
        ]);

        $user = $this->authService->registerUser($validated);

        $deviceName = $request->input('device_name', 'Mobile/Web Client');
        $token = $this->authService->createApiToken($user, $deviceName);

        return $this->sendResponse([
            'user' => $user->load('membership'),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Registrasi berhasil. Selamat datang di Aroma Palace!', 201);
    }

    /**
     * Login user
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        $user = $this->authService->attemptLogin($request->email, $request->password);

        if (!$user) {
            return $this->sendError('Email atau password tidak sesuai.', [], 401);
        }

        $deviceName = $request->input('device_name', 'Mobile/Web Client');
        $token = $this->authService->createApiToken($user, $deviceName);

        return $this->sendResponse([
            'user' => $user->load(['membership', 'primaryAddress']),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Login berhasil.');
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->sendResponse(null, 'Logout berhasil.');
    }

    /**
     * Get authenticated user profile
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['membership', 'primaryAddress']);
        
        $membershipInfo = null;
        if ($user->membership) {
            $membershipInfo = array_merge(
                $user->membership->toArray(),
                ['next_tier_info' => $user->membership->next_tier_info]
            );
        }

        return $this->sendResponse([
            'user' => $user,
            'membership' => $membershipInfo,
        ], 'Data profil pengguna.');
    }
}
