<?php

use App\Actions\Akun\KelolaAkunAdmin;
use App\Models\AuditLog;
use App\Models\User;
use App\Notifications\Akun\UndanganAktivasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use PragmaRX\Google2FA\Google2FA;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Notification::fake();
    $this->actingAs($super = buatPengguna('super_admin'));
    $this->admin = app(KelolaAkunAdmin::class)->buat(['name' => 'Bayu Prasetyo', 'email' => 'bayu.prasetyo@polibatam.ac.id', 'role' => 'admin_track']);
    Notification::assertSentTo($this->admin, UndanganAktivasi::class, function (UndanganAktivasi $n) {
        $this->token = $n->token;

        return true;
    });
    auth()->logout();
});

function aturPassword(string $token, string $sandi = 'AdminBaru1')
{
    return test()->post(route('aktivasi.store'), [
        'token' => $token, 'email' => 'bayu.prasetyo@polibatam.ac.id', 'password' => $sandi, 'password_confirmation' => $sandi,
    ]);
}

it('FR-06/FR-59/AC-27.5: aktivasi 3 langkah — password → QR TOTP → recovery code, baru setelah itu sesi dibuat', function () {
    $this->get(route('aktivasi.create', ['token' => $this->token, 'email' => $this->admin->email]))->assertOk()->assertSee('Atur password');

    aturPassword($this->token)->assertRedirect(route('aktivasi.autentikator'));
    $this->assertGuest();
    expect(Hash::check('AdminBaru1', $this->admin->fresh()->password))->toBeTrue()
        ->and($this->admin->fresh()->email_verified_at)->not->toBeNull();

    $this->get(route('aktivasi.autentikator'))->assertOk()->assertSee('<svg', false);
    $rahasia = decrypt($this->admin->fresh()->two_factor_secret);

    $this->post(route('aktivasi.autentikator.store'), ['code' => '000000'])->assertSessionHasErrors('code');
    $this->assertGuest();

    $this->post(route('aktivasi.autentikator.store'), ['code' => app(Google2FA::class)->getCurrentOtp($rahasia)])
        ->assertRedirect(route('aktivasi.kode-pemulihan'));
    $this->assertAuthenticatedAs($this->admin);
    expect($this->admin->fresh()->two_factor_confirmed_at)->not->toBeNull();

    $kode = $this->admin->fresh()->recoveryCodes();
    $this->get(route('aktivasi.kode-pemulihan'))->assertOk()->assertSee($kode[0]);
    $this->post(route('aktivasi.selesai'))->assertRedirect(route('admin.dasbor'));
    $this->get(route('aktivasi.kode-pemulihan'))->assertRedirect(route('akun.profil'));

    expect(AuditLog::where('aksi', 'aktivasi_akun')->where('entitas_id', $this->admin->id)->pluck('alasan')->all())
        ->toBe(['password_diatur', '2fa_terdaftar']);
});

it('A-76: tautan aktivasi sekali pakai', function () {
    aturPassword($this->token)->assertRedirect(route('aktivasi.autentikator'));
    $this->flushSession();

    aturPassword($this->token, 'LainLagi22')->assertSessionHasErrors('password');
    $this->get(route('aktivasi.create', ['token' => $this->token, 'email' => $this->admin->email]))->assertSee('sudah kedaluwarsa');
});

it('A-76: tautan aktivasi kedaluwarsa setelah 72 jam', function () {
    $this->travel(73)->hours();

    $this->get(route('aktivasi.create', ['token' => $this->token, 'email' => $this->admin->email]))->assertSee('sudah kedaluwarsa');
    aturPassword($this->token)->assertSessionHasErrors('password');
});

it('NFR-02: password aktivasi mengikuti kebijakan password', function () {
    aturPassword($this->token, 'lemah')->assertSessionHasErrors('password');
});

it('admin yang belum aktivasi tidak bisa login tanpa mendaftarkan TOTP', function () {
    aturPassword($this->token);
    $this->flushSession();

    $this->post('/login', ['email' => $this->admin->email, 'password' => 'AdminBaru1'])->assertRedirect(route('aktivasi.autentikator'));
    $this->assertGuest();
    $this->get(route('admin.dasbor'))->assertRedirect(route('login'));
});

it('tautan aktivasi tidak berlaku untuk akun peserta', function () {
    $peserta = buatPengguna('peserta');
    $token = Password::broker('aktivasi')->createToken($peserta);

    $this->post(route('aktivasi.store'), ['token' => $token, 'email' => $peserta->email, 'password' => 'Rahasia123', 'password_confirmation' => 'Rahasia123'])
        ->assertSessionHasErrors('password');
    expect(User::find($peserta->id)->two_factor_secret)->toBeNull();
});
