<?php

namespace App\Providers;

use App\Actions\Akun\BuatAkunPeserta;
use App\Actions\Akun\PeriksaKredensial;
use App\Actions\Akun\ResetPasswordPengguna;
use App\Support\Http\ArahPortal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Fortify;

/** P02: registrasi, login, verifikasi email, reset password lewat Fortify dengan aturan proyek. */
class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->instance(LoginResponse::class, new class implements LoginResponse
        {
            public function toResponse($request): RedirectResponse
            {
                return redirect()->intended(ArahPortal::untuk($request->user()));
            }
        });

        // AC-02.1/FR-02: setelah registrasi, tampilkan HW-06 (cek email).
        $this->app->instance(RegisterResponse::class, new class implements RegisterResponse
        {
            public function toResponse($request): RedirectResponse
            {
                return redirect()->route('verification.notice');
            }
        });
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(BuatAkunPeserta::class);
        Fortify::resetUserPasswordsUsing(ResetPasswordPengguna::class);
        Fortify::authenticateThrough(fn () => [PeriksaKredensial::class, PrepareAuthenticatedSession::class]);

        Fortify::loginView(fn () => view('auth.login'));
        Fortify::registerView(fn () => view('auth.register'));
        Fortify::verifyEmailView(fn () => view('auth.verify-email'));
        Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
        Fortify::resetPasswordView(fn ($request) => view('auth.reset-password', [
            'token' => $request->route('token'),
            'email' => (string) $request->query('email'),
        ]));
    }
}
