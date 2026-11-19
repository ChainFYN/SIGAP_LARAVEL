<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\MasterDataController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 1. PUBLIC ROUTES (Hanya untuk Tamu / Form Registrasi)
|--------------------------------------------------------------------------
*/

// Auth
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Wilayah untuk Form Register Calon Warga di Flutter
Route::get('/kecamatan', [MasterDataController::class, 'kecamatan']);
Route::get('/desa', [MasterDataController::class, 'desa']);

/*
|--------------------------------------------------------------------------
| 2. PROTECTED ROUTES (WAJIB LOGIN / Ada Bearer Token)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Profil & Logout
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Master Data Form Laporan
    Route::get('/kategori', [MasterDataController::class, 'kategori']);
    Route::get('/jalan', [MasterDataController::class, 'jalan']);

    // Manajemen Laporan Warga (Wajib Login)
    Route::get('/laporan', [LaporanController::class, 'index']);
    Route::post('/laporan', [LaporanController::class, 'store']);
    Route::get('/laporan/{id}', [LaporanController::class, 'show']);

    // Notifikasi Warga di Flutter
    Route::get('/notifications', function (Request $request) {
        $notifications = $request->user()->notifications()->take(20)->get();
        $unreadCount = $request->user()->unreadNotifications()->count();
        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
            'data' => $notifications,
        ]);
    });

    Route::post('/notifications/{id}/read', function (Request $request, $id) {
        $notification = $request->user()->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
        }
        return response()->json(['success' => true, 'message' => 'Notifikasi ditandai dibaca.']);
    });

    Route::post('/notifications/read-all', function (Request $request) {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true, 'message' => 'Semua notifikasi ditandai dibaca.']);
    });

});
