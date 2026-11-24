<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PuprAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Jika terautentikasi sebagai PUPR, lanjutkan
        if (session()->has('pupr_id')) {
            return $next($request);
        }

        // 2. Jika request berupa AJAX/Fetch (misal polling notifikasi)
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi Anda telah berakhir. Silakan login kembali.',
            ], 401);
        }

        // 3. Jika user login sebagai peran lain, redirect ke dashboardnya
        if (session()->has('warga_id')) {
            return redirect()->route('warga.dashboard')->withErrors([
                'login' => 'Akses ditolak. Anda tidak memiliki izin untuk mengakses area PUPR.',
            ]);
        }

        if (session()->has('kecamatan_id')) {
            return redirect()->route('kecamatan.dashboard')->withErrors([
                'login' => 'Akses ditolak. Anda tidak memiliki izin untuk mengakses area PUPR.',
            ]);
        }

        // 4. Jika Guest (belum login), redirect ke login form dengan pesan error
        return redirect()->route('warga.login')->withErrors([
            'login' => 'Silakan login sebagai petugas PUPR terlebih dahulu.',
        ]);
    }
}
