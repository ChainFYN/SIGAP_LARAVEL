<?php

namespace Tests\Feature;

use App\Models\PasswordResetOtp;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PasswordResetOtpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            'https://api.brevo.com/*' => Http::response(['messageId' => '<mocked-message-id>'], 201),
        ]);

        config()->set('services.brevo.api_key', 'test-api-key');
        config()->set('services.brevo.sender_email', 'noreply@example.com');
        config()->set('services.brevo.sender_name', 'SIGAP Test');

        DB::table('peran')->insertOrIgnore([
            ['id_peran' => 1, 'nama_peran' => 'Warga', 'created_at' => now(), 'updated_at' => now()],
            ['id_peran' => 2, 'nama_peran' => 'Kecamatan', 'created_at' => now(), 'updated_at' => now()],
            ['id_peran' => 3, 'nama_peran' => 'PUPR', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    private function getOrCreateTestUser(string $email = 'otp_user@example.com'): Pengguna
    {
        return Pengguna::updateOrCreate(
            ['email' => $email],
            [
                'nik' => '3515099999999999',
                'password' => Hash::make('passwordlama123'),
                'nama_lengkap' => 'Pengguna Uji OTP',
                'telepon' => '081234567899',
                'id_peran' => 1,
            ]
        );
    }

    public function test_forgot_password_page_is_accessible(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertStatus(200);
        $response->assertSee('Lupa Kata Sandi');
        $response->assertDontSee('Dashboard Warga');
        $response->assertDontSee('Call Center Kab. Jember');
    }

    public function test_send_otp_validation_fails_when_email_not_registered(): void
    {
        $response = $this->post(route('password.email'), [
            'email' => 'tidak_ada@example.com',
        ]);

        $response->assertSessionHasErrors('email');
        Http::assertNothingSent();
    }

    public function test_send_otp_success_creates_hashed_otp_and_sends_email(): void
    {
        $user = $this->getOrCreateTestUser();

        $response = $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        $response->assertRedirect(route('password.reset'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('password_reset_otps', [
            'email' => $user->email,
            'attempts' => 0,
        ]);

        $record = PasswordResetOtp::where('email', $user->email)->first();
        $this->assertNotNull($record);
        $this->assertNotEquals('123456', $record->otp); // pastikan ter-hash
        $this->assertTrue($record->expires_at->isFuture());

        Http::assertSent(function ($request) use ($user) {
            return $request->url() === 'https://api.brevo.com/v3/smtp/email'
                && isset($request['to'][0]['email'])
                && $request['to'][0]['email'] === $user->email;
        });

        // Cleanup
        $record->delete();
        $user->delete();
    }

    public function test_sending_new_otp_removes_old_otp(): void
    {
        $user = $this->getOrCreateTestUser();

        PasswordResetOtp::create([
            'email' => $user->email,
            'otp' => Hash::make('111111'),
            'attempts' => 2,
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        $records = PasswordResetOtp::where('email', $user->email)->get();
        $this->assertCount(1, $records);
        $this->assertEquals(0, $records->first()->attempts);

        // Cleanup
        $records->first()->delete();
        $user->delete();
    }

    public function test_reset_password_page_is_accessible(): void
    {
        $response = $this->get(route('password.reset', ['email' => 'user@example.com']));

        $response->assertStatus(200);
        $response->assertSee('Atur Ulang Kata Sandi');
        $response->assertDontSee('Dashboard Warga');
        $response->assertDontSee('Call Center Kab. Jember');
    }

    public function test_reset_password_fails_with_invalid_otp(): void
    {
        $user = $this->getOrCreateTestUser();

        PasswordResetOtp::create([
            'email' => $user->email,
            'otp' => Hash::make('123456'),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->post(route('password.update'), [
            'email' => $user->email,
            'otp' => '654321',
            'password' => 'passwordbaru123',
            'password_confirmation' => 'passwordbaru123',
        ]);

        $response->assertSessionHasErrors('otp');

        $record = PasswordResetOtp::where('email', $user->email)->first();
        $this->assertEquals(1, $record->attempts);

        // Cleanup
        $record->delete();
        $user->delete();
    }

    public function test_reset_password_locks_out_after_max_attempts(): void
    {
        $user = $this->getOrCreateTestUser();

        PasswordResetOtp::create([
            'email' => $user->email,
            'otp' => Hash::make('123456'),
            'attempts' => 4,
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->post(route('password.update'), [
            'email' => $user->email,
            'otp' => '999999',
            'password' => 'passwordbaru123',
            'password_confirmation' => 'passwordbaru123',
        ]);

        $response->assertSessionHasErrors('otp');

        // Harus dihapus karena melebihi batas 5 kali percobaan
        $record = PasswordResetOtp::where('email', $user->email)->first();
        $this->assertNull($record);

        // Cleanup
        $user->delete();
    }

    public function test_reset_password_fails_when_otp_expired(): void
    {
        $user = $this->getOrCreateTestUser();

        PasswordResetOtp::create([
            'email' => $user->email,
            'otp' => Hash::make('123456'),
            'attempts' => 0,
            'expires_at' => now()->subMinute(),
        ]);

        $response = $this->post(route('password.update'), [
            'email' => $user->email,
            'otp' => '123456',
            'password' => 'passwordbaru123',
            'password_confirmation' => 'passwordbaru123',
        ]);

        $response->assertSessionHasErrors('otp');

        $record = PasswordResetOtp::where('email', $user->email)->first();
        $this->assertNull($record);

        // Cleanup
        $user->delete();
    }

    public function test_reset_password_success_updates_password_and_deletes_otp(): void
    {
        $user = $this->getOrCreateTestUser();

        PasswordResetOtp::create([
            'email' => $user->email,
            'otp' => Hash::make('123456'),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->post(route('password.update'), [
            'email' => $user->email,
            'otp' => '123456',
            'password' => 'passwordbaru123',
            'password_confirmation' => 'passwordbaru123',
        ]);

        $response->assertRedirect(route('warga.login'));
        $response->assertSessionHas('success');

        // Pastikan password di database terupdate
        $user->refresh();
        $this->assertTrue(Hash::check('passwordbaru123', $user->password));

        // Pastikan OTP terhapus
        $record = PasswordResetOtp::where('email', $user->email)->first();
        $this->assertNull($record);

        // Cleanup
        $user->delete();
    }
}
