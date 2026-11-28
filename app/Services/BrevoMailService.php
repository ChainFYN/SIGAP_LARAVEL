<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class BrevoMailService
{
    /**
     * Kirim kode OTP via API Brevo (Sendinblue).
     */
    public function sendOtp(string $recipientEmail, string $otp, int $expiryMinutes = 10): void
    {
        $senderEmail = config('services.brevo.sender_email') ?: config('mail.from.address');
        $senderName = config('services.brevo.sender_name') ?: config('app.name', 'SIGAP');
        $apiKey = config('services.brevo.api_key');

        if (empty($apiKey)) {
            Log::error('Brevo API key belum dikonfigurasi di services.brevo.api_key atau .env');
            throw new RuntimeException('Konfigurasi BREVO_API_KEY belum diisi di sistem.');
        }

        // Gunakan view template email SIGAP jika tersedia
        if (view()->exists('emails.reset_password_otp')) {
            $htmlContent = view('emails.reset_password_otp', [
                'otp' => $otp,
                'expiresInMinutes' => $expiryMinutes,
            ])->render();
        } else {
            $htmlContent = "
                <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                    <p>Halo,</p>
                    <p>Gunakan kode OTP berikut untuk mereset password akun kamu:</p>
                    <h2 style='letter-spacing: 6px; color: #111;'>{$otp}</h2>
                    <p>Kode ini berlaku selama <strong>{$expiryMinutes} menit</strong>. Abaikan email ini jika kamu tidak merasa memintanya.</p>
                </div>
            ";
        }

        $request = Http::withHeaders([
            'api-key' => $apiKey,
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ]);

        // Cegah error cURL 60 pada environment Windows/Laragon yang belum memasang cacert.pem
        if (app()->environment('local', 'testing') || empty(ini_get('curl.cainfo'))) {
            $request = $request->withoutVerifying();
        }

        $response = $request->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => $senderName,
                'email' => $senderEmail,
            ],
            'to' => [
                [
                    'email' => $recipientEmail,
                ],
            ],
            'subject' => 'Kode OTP Reset Kata Sandi - SIGAP',
            'htmlContent' => $htmlContent,
        ]);

        if ($response->failed()) {
            Log::error('Gagal mengirim email OTP via Brevo', [
                'email' => $recipientEmail,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('Gagal mengirim kode OTP ke email via Brevo. Silakan coba lagi.');
        }
    }
}
