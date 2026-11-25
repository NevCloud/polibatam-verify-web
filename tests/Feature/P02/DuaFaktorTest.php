<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

function adminMenunggu2fa(string $role = 'admin_silau'): array
{
    $user = buatPengguna($role);
    $rahasia = pasang2fa($user);
    test()->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect(route('two-factor.login'));

    return [$user, $rahasia];
}

it('FR-59/HW-08: kode TOTP valid membuat sesi admin dan mengarah ke dasbor admin', function () {
    [$user, $rahasia] = adminMenunggu2fa();

    $this->get(route('two-factor.login'))->assertOk()->assertSee($user->email);
    $this->post(route('two-factor.login.store'), ['code' => kodeTotp($rahasia)])->assertRedirect(route('admin.dasbor'));
    $this->assertAuthenticatedAs($user);
});

it('BR-59: kode TOTP salah ditolak, tanpa sesi', function () {
    adminMenunggu2fa();

    $this->post(route('two-factor.login.store'), ['code' => '000000'])->assertSessionHasErrors(['code' => 'Kode autentikator tidak valid.']);
    $this->assertGuest();
});

it('FR-59: recovery code dapat dipakai sekali saja', function () {
    [$user] = adminMenunggu2fa();
    $kode = $user->recoveryCodes()[0];

    $this->post(route('two-factor.login.store'), ['recovery_code' => $kode])->assertRedirect(route('admin.dasbor'));
    expect(User::find($user->id)->recoveryCodes())->not->toContain($kode);

    $this->post('/logout');
    $this->post('/login', ['email' => $user->email, 'password' => 'password']);
    $this->post(route('two-factor.login.store'), ['recovery_code' => $kode])->assertSessionHasErrors('recovery_code');
    $this->assertGuest();
});

it('BR-03: gagal kode 2FA ikut dihitung ke penguncian akun', function () {
    [$user, $rahasia] = adminMenunggu2fa();

    foreach (range(1, 5) as $_) {
        $this->post(route('two-factor.login.store'), ['code' => '000000']);
    }
    $this->post(route('two-factor.login.store'), ['code' => kodeTotp($rahasia)])->assertRedirect(route('login'));
    $this->assertGuest();
});

it('HW-08 tanpa tahap password diarahkan ke login', function () {
    $this->get(route('two-factor.login'))->assertRedirect(route('login'));
    $this->post(route('two-factor.login.store'), ['code' => '123456'])->assertRedirect(route('login'));
});

it('peserta tidak melewati 2FA (BR-59 hanya admin)', function () {
    $user = buatPengguna('peserta');

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect(route('dasbor'));
});
