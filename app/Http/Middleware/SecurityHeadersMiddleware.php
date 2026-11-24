<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // 1. Cegah MIME-sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // 2. Cegah Clickjacking (X-Frame-Options & CSP frame-ancestors)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // 3. Kontrol perayapan Referrer URL
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // 4. Proteksi XSS untuk browser lama
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // 5. Content Security Policy (izinkan resource peta GIS, fonts, dan CDN yang digunakan)
        $response->headers->set('Content-Security-Policy', "frame-ancestors 'self';");

        // 6. Hapus header versi PHP (Information Disclosure)
        $response->headers->remove('X-Powered-By');
        if (function_exists('header_remove')) {
            header_remove('X-Powered-By');
        }

        return $response;
    }
}
