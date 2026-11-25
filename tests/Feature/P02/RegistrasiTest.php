<?php

use App\Models\User;
use App\Notifications\Akun\VerifikasiEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Notification::fake();
});

function dataRegistrasi(array $timpa = []): array
{
    return $timpa + [
        'name' => 'Nabila Putri Ramadhani',
        'email' => 'nabila.ramadhani@mail.com',
        'password' => 'Rahasia123',
        'password_confirmation' => 'Rahasia123',
        'persetujuan_privasi' => '1',
    ];
}

it('FR-01/AC-02.1: registrasi membuat akun role peserta dan mengirim tautan verifikasi (FR-02)', function () {
    $this->post('/register', dataRegistrasi())->assertRedirect(route('verification.notice'));

    $user = User::where('email', 'nabila.ramadhani@mail.com')->firstOrFail();
    expect($user->namaRole())->toBe('peserta')
        ->and($user->labelRole())->toBe('Calon Peserta')
        ->and($user->email_verified_at)->toBeNull()
        ->and($user->is_active)->toBeTrue();
    Notification::assertSentTo($user, VerifikasiEmail::class);
    $this->assertAuthenticatedAs($user);
});

it('NFR-11: persetujuan kebijakan privasi wajib dan dicatat bersama waktunya', function () {
    $this->post('/register', dataRegistrasi(['persetujuan_privasi' => null]))->assertSessionHasErrors('persetujuan_privasi');
    expect(User::where('email', 'nabila.ramadhani@mail.com')->exists())->toBeFalse();

    $this->travelTo('2026-11-25 03:00:00');
    $this->post('/register', dataRegistrasi());
    expect(User::where('email', 'nabila.ramadhani@mail.com')->first()->privacy_consented_at->toDateTimeString())->toBe('2026-11-25 03:00:00');
});

it('BR-01/AC-02.2: email duplikat ditolak dengan pesan "Email sudah terdaftar" (tidak peka huruf besar)', function () {
    buatPengguna('peserta', ['email' => 'nabila.ramadhani@mail.com']);

    $this->post('/register', dataRegistrasi(['email' => 'Nabila.Ramadhani@mail.com']))
        ->assertSessionHasErrors(['email' => 'Email sudah terdaftar']);
});

it('NFR-02/AC-02.3: password lemah ditolak (minimal 8, huruf dan angka)', function (string $sandi) {
    $this->post('/register', dataRegistrasi(['password' => $sandi, 'password_confirmation' => $sandi]))
        ->assertSessionHasErrors('password');
})->with(['terlalu pendek' => ['Abc123'], 'tanpa angka' => ['RahasiaSekali'], 'tanpa huruf' => ['1234567890']]);

it('NFR-02: password disimpan sebagai hash bcrypt, bukan teks asli; cost produksi 12', function () {
    $this->post('/register', dataRegistrasi());
    $hash = User::where('email', 'nabila.ramadhani@mail.com')->value('password');

    expect($hash)->not->toBe('Rahasia123')
        ->and(Hash::info($hash)['algoName'])->toBe('bcrypt')
        ->and(Hash::check('Rahasia123', $hash))->toBeTrue()
        ->and(file_get_contents(base_path('.env.example')))->toContain('BCRYPT_ROUNDS=12');
});

it('FR-02: tautan verifikasi menandai email terverifikasi', function () {
    $user = buatPengguna('peserta', ['email_verified_at' => null]);
    $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->email)]);

    $this->actingAs($user)->get($url)->assertRedirect();
    expect($user->fresh()->email_verified_at)->not->toBeNull();
});

it('BR-02/AC-02.4: akun belum terverifikasi diarahkan ke HW-06 pada rute bertanda verified (formulir P03)', function () {
    Route::middleware(['web', 'auth', 'verified'])->get('/_uji/formulir', fn () => 'formulir');
    $user = buatPengguna('peserta', ['email_verified_at' => null]);

    $this->actingAs($user)->get('/_uji/formulir')->assertRedirect(route('verification.notice'));
    $this->actingAs($user)->get(route('verification.notice'))->assertOk()->assertSee('Cek email Anda')->assertSee('Kirim ulang tautan');
});

it('FR-02: kirim ulang tautan verifikasi', function () {
    $user = buatPengguna('peserta', ['email_verified_at' => null]);

    $this->actingAs($user)->post(route('verification.send'))->assertRedirect();
    Notification::assertSentTo($user, VerifikasiEmail::class);
});
