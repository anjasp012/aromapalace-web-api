<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Pastikan pengguna yang login memiliki role 'admin'
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->guest(route('admin.login'))->with('error', 'Silakan login terlebih dahulu.');
        }

        if (!auth()->user()->isAdmin()) {
            auth()->logout();
            return redirect()->route('admin.login')->with('error', 'Akses ditolak. Anda tidak memiliki izin administrator.');
        }

        return $next($request);
    }
}

