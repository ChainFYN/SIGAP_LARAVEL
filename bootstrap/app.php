<?php

use App\Http\Middleware\KecamatanAuthMiddleware;
use App\Http\Middleware\PuprAuthMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Http\Middleware\WargaAuthMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Pasang Security Headers secara global
        $middleware->append(SecurityHeadersMiddleware::class);

        // Konfigurasi redirect guest
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('api/*') ? null : route('warga.login'));

        $middleware->alias([
            'warga.auth' => WargaAuthMiddleware::class,
            'pupr.auth' => PuprAuthMiddleware::class,
            'kecamatan.auth' => KecamatanAuthMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
