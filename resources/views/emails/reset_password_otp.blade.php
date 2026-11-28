<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP Reset Kata Sandi - SIGAP</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; line-height: 1.6;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f8fafc; padding: 40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" max-width="520" style="max-width: 520px; background-color: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #0f172a; padding: 28px 32px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 22px; font-weight: 800; letter-spacing: -0.5px;">
                                SIGAP <span style="color: #38bdf8; font-weight: 400; font-size: 16px;">Jember</span>
                            </h1>
                            <p style="margin: 6px 0 0 0; color: #94a3b8; font-size: 12px;">Sistem Informasi Pelaporan Kerusakan Jalan</p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 36px 32px 28px 32px;">
                            <h2 style="margin: 0 0 16px 0; color: #0f172a; font-size: 18px; font-weight: 700;">Atur Ulang Kata Sandi</h2>
                            <p style="margin: 0 0 20px 0; color: #475569; font-size: 14px;">
                                Halo, kami menerima permintaan pengaturan ulang kata sandi untuk akun SIGAP Anda. Gunakan kode One-Time Password (OTP) berikut untuk memverifikasi permintaan Anda:
                            </p>

                            <!-- OTP Box -->
                            <div style="background-color: #f1f5f9; border: 1px dashed #cbd5e1; border-radius: 12px; padding: 20px; text-align: center; margin: 24px 0;">
                                <span style="font-family: 'Courier New', Courier, monospace; font-size: 34px; font-weight: 800; letter-spacing: 8px; color: #0f172a; display: inline-block;">
                                    {{ $otp }}
                                </span>
                            </div>

                            <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 12px 16px; border-radius: 6px; margin-bottom: 24px;">
                                <p style="margin: 0; color: #991b1b; font-size: 12px; line-height: 1.5;">
                                    <strong>Penting:</strong> Kode ini hanya berlaku selama <strong>{{ $expiresInMinutes }} menit</strong>. Jangan pernah membagikan kode OTP ini kepada siapa pun, termasuk pihak yang mengatasnamakan SIGAP.
                                </p>
                            </div>

                            <p style="margin: 0 0 8px 0; color: #64748b; font-size: 13px;">
                                Jika Anda tidak meminta pengaturan ulang kata sandi, Anda dapat mengabaikan email ini dengan aman. Akun Anda tetap terlindungi.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 32px; text-align: center;">
                            <p style="margin: 0; color: #94a3b8; font-size: 11px;">
                                &copy; {{ date('Y') }} SIGAP Pemerintah Kabupaten Jember. Seluruh hak cipta dilindungi.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
