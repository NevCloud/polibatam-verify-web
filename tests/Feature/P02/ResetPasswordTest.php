<?php

use App\Notifications\Akun\TautanResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Notification::fake();
});

it('FR-05/AC-03.5/K-32: email tidak terdaftar dijawab "Email tidak terdaftar"', function () {
    $this->post('/forgot-password', ['email' => 'salah.ketik@contoh.test'])->assertSessionHasErrors(['email' => 'Email tidak terdaftar']);
});

it('FR-05/AC-03.4: email terdaftar menerima tautan reset', function () {
    $user = buatPengguna('peserta');

    $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('status');
    Notification::assertSentTo($user, TautanResetPassword::class);
});

it('BR-03: tautan reset sekali pakai, mengganti password, dan mencabut semua sesi & token', function () {
    $user = buatPengguna('peserta');
    $user->createToken('mobile');
    DB::table('sessions')->insert(['id' => 'sesi-lama', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);
    $token = Password::broker()->createToken($user);
    $data = ['token' => $token, 'email' => $user->email, 'password' => 'BaruSekali1', 'password_confirmation' => 'BaruSekali1'];

    $this->post('/reset-password', $data)->assertRedirect(route('login'));
    expect(Hash::check('BaruSekali1', $user->fresh()->password))->toBeTrue()
        ->and(DB::table('sessions')->where('user_id', $user->id)->count())->toBe(0)
        ->and($user->tokens()->count())->toBe(0);

    $this->post('/reset-password', ['password' => 'LagiBaru22', 'password_confirmation' => 'LagiBaru22'] + $data)
        ->assertSessionHasErrors('email');
    expect(Hash::check('BaruSekali1', $user->fresh()->password))->toBeTrue();
});

it('BR-03: tautan reset kedaluwarsa setelah 60 menit', function () {
    $user = buatPengguna('peserta');
    $token = Password::broker()->createToken($user);

    $this->travel(61)->minutes();
    $this->post('/reset-password', ['token' => $token, 'email' => $user->email, 'password' => 'BaruSekali1', 'password_confirmation' => 'BaruSekali1'])
        ->assertSessionHasErrors(['email' => 'Tautan tidak valid atau sudah kedaluwarsa. Minta tautan baru.']);
});

it('NFR-02: password baru dari reset mengikuti kebijakan password', function () {
    $user = buatPengguna('peserta');
    $token = Password::broker()->createToken($user);

    $this->post('/reset-password', ['token' => $token, 'email' => $user->email, 'password' => 'pendek', 'password_confirmation' => 'pendek'])
        ->assertSessionHasErrors('password');
});
