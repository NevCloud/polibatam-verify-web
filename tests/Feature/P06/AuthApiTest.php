<?php

use App\Actions\Akun\KelolaAkunAdmin;
use App\Actions\Akun\MasukMobile;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
});

it('AC-30.1/NFR-47: login → challenge_token → TOTP → token per perangkat 12 jam; /me; audit kanal mobile', function () {
    $silau = buatPengguna('admin_silau');
    pasang2fa($silau);

    $r = $this->postJson('/api/v1/auth/login', ['email' => $silau->email, 'password' => 'password', 'nama_perangkat' => 'Pixel Uji'])
        ->assertOk()->assertJsonPath('success', true)->assertJsonMissingPath('data.token');
    expect($r->json('data.challenge_token'))->toHaveLength(64);

    $token = masukApi($silau);
    $pat = PersonalAccessToken::findToken($token);
    expect($pat->name)->toBe('Pixel Uji')
        ->and($pat->expires_at->diffInMinutes(now()->addHours(MasukMobile::JAM_TOKEN), true))->toBeLessThan(2)
        ->and($silau->tokens()->count())->toBe(1) // login ulang di perangkat sama mengganti token lama
        ->and(AuditLog::where('aksi', 'login')->where('user_id', $silau->id)->where('hasil', 'berhasil')->latest('id')->value('kanal'))->toBe('mobile');

    $this->getJson('/api/v1/me', bearer($token))->assertOk()
        ->assertJsonPath('data.email', $silau->email)->assertJsonPath('data.role', 'admin_silau');
    $this->app['auth']->forgetGuards();
    $this->getJson('/api/v1/me')->assertUnauthorized()->assertJsonPath('code', 'UNAUTHENTICATED');
});

it('AC-30.2: role selain Admin SILAU & Keuangan ditolak 403; kredensial / kode salah 422', function (string $role) {
    $u = buatPengguna($role);
    pasang2fa($u);

    $this->postJson('/api/v1/auth/login', ['email' => $u->email, 'password' => 'password', 'nama_perangkat' => 'X'])
        ->assertForbidden()->assertJsonPath('code', 'FORBIDDEN')
        ->assertJsonPath('message', 'Aplikasi mobile hanya untuk Admin SILAU dan Admin Keuangan.');
})->with(['peserta', 'super_admin', 'admin_track']);

it('422: password salah, challenge tidak dikenal, kode TOTP salah; field wajib', function () {
    $k = buatPengguna('admin_keuangan');
    pasang2fa($k);

    $this->postJson('/api/v1/auth/login', ['email' => $k->email, 'password' => 'salah', 'nama_perangkat' => 'X'])
        ->assertUnprocessable()->assertJsonPath('code', 'VALIDATION_ERROR')->assertJsonValidationErrors('email');
    $this->postJson('/api/v1/auth/login', [])->assertUnprocessable()->assertJsonValidationErrors(['email', 'password', 'nama_perangkat']);
    $this->postJson('/api/v1/auth/two-factor', ['challenge_token' => 'ngawur', 'code' => '123456'])->assertUnprocessable()->assertJsonValidationErrors('challenge_token');

    $tantangan = $this->postJson('/api/v1/auth/login', ['email' => $k->email, 'password' => 'password', 'nama_perangkat' => 'X'])->json('data.challenge_token');
    $this->postJson('/api/v1/auth/two-factor', ['challenge_token' => $tantangan, 'code' => '000000'])->assertUnprocessable()->assertJsonValidationErrors('code');
    expect($k->tokens()->count())->toBe(0);
});

it('Uji wajib: token dicabut saat logout, akun dinonaktifkan, role berubah, dan setelah 12 jam → 401', function () {
    $sa = buatPengguna('super_admin');
    $akun = app(KelolaAkunAdmin::class);

    $a = buatPengguna('admin_silau');
    $t = masukApi($a);
    $this->postJson('/api/v1/auth/logout', [], bearer($t))->assertNoContent();
    $this->getJson('/api/v1/me', bearer($t))->assertUnauthorized();

    $b = buatPengguna('admin_keuangan');
    $t = masukApi($b);
    $this->actingAs($sa);
    $akun->ubahStatus($b, false, 'Uji pencabutan token');
    $this->getJson('/api/v1/me', bearer($t))->assertUnauthorized();

    $c = buatPengguna('admin_silau');
    $t = masukApi($c);
    $akun->ubahRole($c, 'admin_track');
    $this->getJson('/api/v1/me', bearer($t))->assertUnauthorized();

    $d = buatPengguna('admin_keuangan');
    $t = masukApi($d);
    $this->travel(12)->hours();
    $this->travel(1)->minutes();
    $this->getJson('/api/v1/me', bearer($t))->assertUnauthorized();
});

it('Uji wajib: Admin SILAU tidak dapat membuka antrean pembayaran dan sebaliknya (403)', function () {
    $silau = masukApi(buatPengguna('admin_silau'));
    $keuangan = masukApi(buatPengguna('admin_keuangan'));

    $this->getJson('/api/v1/verifikasi-pembayaran', bearer($silau))->assertForbidden()->assertJsonPath('code', 'FORBIDDEN');
    $this->getJson('/api/v1/verifikasi-berkas', bearer($keuangan))->assertForbidden();
    $this->getJson('/api/v1/verifikasi-berkas', bearer($silau))->assertOk();
    $this->getJson('/api/v1/verifikasi-pembayaran', bearer($keuangan))->assertOk();
});

it('NFR-08: login API dibatasi 10 permintaan/menit per IP → 429 + Retry-After', function () {
    foreach (range(1, 10) as $i) {
        $this->postJson('/api/v1/auth/login', ['email' => "x{$i}@contoh.test", 'password' => 'salah', 'nama_perangkat' => 'X'])->assertUnprocessable();
    }
    $this->postJson('/api/v1/auth/login', ['email' => 'x@contoh.test', 'password' => 'salah', 'nama_perangkat' => 'X'])
        ->assertStatus(429)->assertJsonPath('code', 'TOO_MANY_REQUESTS')->assertHeader('Retry-After');
});
