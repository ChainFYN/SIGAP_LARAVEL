<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WargaAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Jika terautentikasi sebagai Warga, lanjutkan
        if (session()->has('warga_id')) {
            return $next($request);
        }

        // 2. Jika request berupa AJAX/Fetch
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi Anda telah berakhir. Silakan login kembali.',
            ], 401);
        }

        // 3. Jika user login sebagai PUPR atau Kecamatan, arahkan ke dashboardnya
        if (session()->has('pupr_id')) {
            return redirect()->route('pupr.dashboard')->withErrors([
                'login' => 'Akses ditolak. Anda sedang login sebagai PUPR.',
            ]);
        }

        if (session()->has('kecamatan_id')) {
            return redirect()->route('kecamatan.dashboard')->withErrors([
                'login' => 'Akses ditolak. Anda sedang login sebagai Kecamatan.',
            ]);
        }

        // 4. Jika Guest, redirect ke login
        return redirect()->route('warga.login')->withErrors([
            'login' => 'Silakan login terlebih dahulu.',
        ]);
    }
}
