<?php

use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

function masuk(string $email, string $sandi = 'password')
{
    return test()->post('/login', ['email' => $email, 'password' => $sandi]);
}

it('FR-03/AC-03.1: peserta login lalu diarahkan ke dasbor peserta; tercatat di audit (A-78)', function () {
    $user = buatPengguna('peserta');

    masuk($user->email)->assertRedirect(route('dasbor'));
    $this->assertAuthenticatedAs($user);
    expect(AuditLog::where('aksi', 'login')->where('hasil', 'berhasil')->where('user_id', $user->id)->exists())->toBeTrue();
});

it('FR-03/AC-03.2: pesan gagal umum, sama untuk email tidak terdaftar dan password salah', function () {
    $user = buatPengguna('peserta');

    masuk($user->email, 'salah')->assertSessionHasErrors(['email' => 'Email atau password salah']);
    masuk('tidak.ada@contoh.test')->assertSessionHasErrors(['email' => 'Email atau password salah']);
    $this->assertGuest();
    expect(AuditLog::where('aksi', 'login')->where('hasil', 'gagal')->count())->toBe(2);
});

it('FR-04/BR-03/AC-03.3: 5 kali gagal berturut-turut mengunci akun 15 menit, termasuk untuk password benar', function () {
    $user = buatPengguna('peserta');

    foreach (range(1, 4) as $_) {
        masuk($user->email, 'salah')->assertSessionHasErrors(['email' => 'Email atau password salah']);
    }
    masuk($user->email, 'salah')->assertSessionHasErrors(['email' => 'Akun dikunci sementara. Coba lagi dalam 15 menit.']);
    masuk($user->email)->assertSessionHasErrors('email');
    $this->assertGuest();

    $this->travel(16)->minutes();
    masuk($user->email)->assertRedirect(route('dasbor'));
});

it('BR-03: login berhasil mengosongkan hitungan gagal', function () {
    $user = buatPengguna('peserta');
    foreach (range(1, 4) as $_) {
        masuk($user->email, 'salah');
    }
    masuk($user->email)->assertRedirect(route('dasbor'));
    $this->post('/logout');

    foreach (range(1, 4) as $_) {
        masuk($user->email, 'salah')->assertSessionHasErrors(['email' => 'Email atau password salah']);
    }
});

it('AC-27.2: akun nonaktif tidak bisa login', function () {
    $user = buatPengguna('admin_silau', ['is_active' => false]);
    pasang2fa($user);

    masuk($user->email)->assertSessionHasErrors(['email' => 'Akun Anda dinonaktifkan. Hubungi Super Admin.']);
    $this->assertGuest();
});

it('BR-59/AC-03.6: admin dengan TOTP tidak mendapat sesi sebelum kode valid', function (string $role) {
    $user = buatPengguna($role);
    pasang2fa($user);

    masuk($user->email)->assertRedirect(route('two-factor.login'));
    $this->assertGuest();
    $this->get(route('admin.dasbor'))->assertRedirect(route('login'));
})->with(['admin_silau', 'admin_keuangan', 'admin_track', 'super_admin']);

it('BR-59/A-75: admin tanpa TOTP diarahkan mendaftarkan autentikator, tetap tanpa sesi', function () {
    $user = buatPengguna('admin_keuangan');

    masuk($user->email)->assertRedirect(route('aktivasi.autentikator'));
    $this->assertGuest();
    $this->get(route('aktivasi.autentikator'))->assertOk()->assertSee('Pindai QR');
});

it('NFR-08: lebih dari 10 permintaan login per menit dari satu IP dijawab 429', function () {
    foreach (range(1, 10) as $i) {
        masuk("orang{$i}@contoh.test", 'salah')->assertStatus(302);
    }
    masuk('orang11@contoh.test', 'salah')->assertStatus(429);
});

it('NFR-08: registrasi dan lupa password ikut dibatasi 10/menit/IP', function () {
    foreach (range(1, 10) as $_) {
        $this->post('/forgot-password', ['email' => 'tidak.ada@contoh.test']);
    }
    $this->post('/register', [])->assertStatus(429);
});

it('logout mengakhiri sesi dan tercatat di audit', function () {
    $user = buatPengguna('peserta');
    $this->actingAs($user)->post('/logout')->assertRedirect();

    $this->assertGuest();
    expect(AuditLog::where('aksi', 'logout')->where('user_id', $user->id)->exists())->toBeTrue();
});
