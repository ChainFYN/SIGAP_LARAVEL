<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetOtp;
use App\Models\Pengguna;
use App\Services\BrevoMailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PasswordResetOtpController extends Controller
{
    /**
     * Tampilkan formulir permintaan kode OTP reset kata sandi.
     */
    public function showForgotForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Kirimkan 6 digit kode OTP ke alamat email terdaftar.
     */
    public function sendOtp(Request $request, BrevoMailService $brevoMail): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email|exists:pengguna,email',
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.exists' => 'Alamat email tidak terdaftar dalam sistem.',
        ]);

        $otp = sprintf('%06d', random_int(0, 999999));

        // Hapus OTP aktif sebelumnya untuk email ini jika ada
        PasswordResetOtp::where('email', $request->email)->delete();

        // Simpan OTP yang ter-hash dengan masa berlaku 10 menit
        PasswordResetOtp::create([
            'email' => $request->email,
            'otp' => Hash::make($otp),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
        ]);

        try {
            $brevoMail->sendOtp($request->email, $otp, 10);
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->withErrors([
                'email' => 'Gagal mengirimkan email OTP. Silakan periksa koneksi atau konfigurasi Brevo.',
            ]);
        }

        // Simpan email ke session untuk memudahkan prefill di halaman verifikasi
        session(['password_reset_email' => $request->email]);

        return redirect()->route('password.reset')->with('status', 'Kode OTP 6 digit berhasil dikirim ke email Anda. Silakan cek kotak masuk atau spam.');
    }

    /**
     * Tampilkan formulir verifikasi OTP dan input password baru.
     */
    public function showResetForm(Request $request): View
    {
        $email = $request->query('email', session('password_reset_email', ''));

        return view('auth.reset-password', [
            'email' => $email,
        ]);
    }

    /**
     * Verifikasi kode OTP dan perbarui kata sandi akun pengguna.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email|exists:pengguna,email',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.exists' => 'Alamat email tidak terdaftar dalam sistem.',
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.size' => 'Kode OTP harus berupa 6 digit angka.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $otpRecord = PasswordResetOtp::where('email', $request->email)->latest()->first();

        if (! $otpRecord) {
            return back()->withInput($request->only('email'))->withErrors([
                'otp' => 'Permintaan OTP tidak ditemukan atau telah kedaluwarsa. Silakan ajukan lupa kata sandi kembali.',
            ]);
        }

        // Cek masa kedaluwarsa OTP
        if ($otpRecord->expires_at->isPast()) {
            $otpRecord->delete();

            return back()->withInput($request->only('email'))->withErrors([
                'otp' => 'Kode OTP telah kedaluwarsa. Silakan minta kode OTP baru.',
            ]);
        }

        // Cek apakah sudah melebihi 5 kali percobaan salah
        if ($otpRecord->attempts >= 5) {
            $otpRecord->delete();

            return back()->withInput($request->only('email'))->withErrors([
                'otp' => 'Batas percobaan memasukkan OTP telah habis (maksimal 5 kali). Silakan minta kode baru.',
            ]);
        }

        // Verifikasi keabsahan kode OTP
        if (! Hash::check($request->otp, $otpRecord->otp)) {
            $otpRecord->increment('attempts');
            $remaining = 5 - $otpRecord->attempts;

            if ($remaining <= 0) {
                $otpRecord->delete();

                return back()->withInput($request->only('email'))->withErrors([
                    'otp' => 'Kode OTP salah. Batas percobaan habis (5 kali). Silakan ajukan kode baru.',
                ]);
            }

            return back()->withInput($request->only('email'))->withErrors([
                'otp' => "Kode OTP salah. Sisa percobaan: {$remaining} kali.",
            ]);
        }

        $user = Pengguna::where('email', $request->email)->first();

        if (! $user) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'Akun pengguna tidak ditemukan.',
            ]);
        }

        // Update kata sandi pengguna
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Hapus OTP yang sudah sukses digunakan & bersihkan session
        $otpRecord->delete();
        session()->forget('password_reset_email');

        return redirect()->route('warga.login')->with('success', 'Kata sandi berhasil diperbarui! Silakan masuk dengan kata sandi baru Anda.');
    }
}
