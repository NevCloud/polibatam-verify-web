<?php

namespace App\Actions\Akun;

use App\Models\User;
use App\Services\AuditLogger;
use App\Services\KunciLogin;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Tahap pipeline login Fortify (FR-03, FR-04, BR-03, BR-59, AC-27.2):
 * pesan gagal umum, kunci akun setelah gagal berturut-turut, akun nonaktif ditolak,
 * admin tidak mendapat sesi sebelum TOTP valid (tantangan 2FA atau pendaftaran autentikator).
 */
class PeriksaKredensial
{
    public const SESI_MENUNGGU = 'login.id';

    public function __construct(private KunciLogin $kunci, private AuditLogger $audit) {}

    public function handle(Request $request, Closure $next): mixed
    {
        $email = mb_strtolower(trim((string) $request->input('email')));

        if ($this->kunci->terkunci($email)) {
            $this->audit->catat('login', 'user', null, 'ditolak', alasan: 'akun_terkunci');
            throw $this->galatTerkunci($email);
        }

        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check((string) $request->input('password'), $user->password)) {
            $this->kunci->gagal($email);
            $this->audit->catat('login', 'user', $user?->id, 'gagal', pelaku: $user, alasan: 'kredensial_salah');

            throw $this->kunci->terkunci($email)
                ? $this->galatTerkunci($email)
                : ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        if (! $user->is_active) {
            $this->audit->catat('login', 'user', $user->id, 'ditolak', pelaku: $user, alasan: 'akun_nonaktif');
            throw ValidationException::withMessages(['email' => 'Akun Anda dinonaktifkan. Hubungi Super Admin.']);
        }

        if ($user->adalahAdmin()) {
            // BR-59: sesi belum dibuat; id disimpan sementara sampai TOTP valid.
            $request->session()->put(self::SESI_MENUNGGU, $user->id);

            return redirect()->route($user->two_factor_confirmed_at ? 'two-factor.login' : 'aktivasi.autentikator');
        }

        $this->kunci->bersihkan($email);
        Auth::guard('web')->login($user);
        $this->audit->catat('login', 'user', $user->id, pelaku: $user);

        return $next($request);
    }

    private function galatTerkunci(string $email): ValidationException
    {
        return ValidationException::withMessages([
            'email' => 'Akun dikunci sementara. Coba lagi dalam '.$this->kunci->sisaMenit($email).' menit.',
        ]);
    }
}
