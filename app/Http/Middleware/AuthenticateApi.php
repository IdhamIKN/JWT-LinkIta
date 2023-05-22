<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateApi
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('api')->check()) {
            // Jika pengguna belum terotentikasi
            return response()->json(['message' => 'Silahkan login terlebih dahulu atau Anda tidak diizinkan mengakses halaman ini'], 401);
        }

        return $next($request);
    }
}
