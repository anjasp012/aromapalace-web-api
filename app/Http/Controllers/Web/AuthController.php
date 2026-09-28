<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('web.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = $this->authService->attemptLogin($credentials['email'], $credentials['password']);

        if ($user) {
            Auth::login($user, $request->boolean('remember'));

            $guestWishlist = $request->session()->get('wishlist', []);
            $request->session()->regenerate();

            if (!empty($guestWishlist)) {
                $this->authService->syncWishlist($user, $guestWishlist);
                $request->session()->forget('wishlist');
            }

            return redirect()->intended(route('home'))->with('success', 'Berhasil masuk ke akun Anda.');
        }

        return back()->withErrors(['email' => 'Email atau password tidak sesuai.'])->onlyInput('email');
    }

    public function showRegisterForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('web.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        $guestWishlist = $request->session()->get('wishlist', []);
        $user = $this->authService->registerUser($validated, $guestWishlist);

        Auth::login($user);
        $request->session()->forget('wishlist');

        return redirect()->route('home')->with('success', 'Pendaftaran akun berhasil.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Berhasil keluar dari akun.');
    }
}
