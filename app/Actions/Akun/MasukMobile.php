<?php

namespace App\Actions\Akun;

use App\Models\User;
use App\Services\AuditLogger;
use App\Services\KunciLogin;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Login aplikasi mobile (FR-60, BR-59, BR-60, NFR-47, K-29/K-30/K-33):
 * 1) email + password → `challenge_token` (selalu, karena 2FA wajib untuk admin); belum ada token akses.
 * 2) challenge + kode TOTP/recovery → token Sanctum per perangkat, berlaku 12 jam.
 * Aturan gagal/kunci akun sama dengan web (KunciLogin per email). Hanya Admin SILAU & Admin Keuangan.
 */
class MasukMobile
{
    public const ROLE = ['admin_silau', 'admin_keuangan'];

    public const JAM_TOKEN = 12;

    public const MENIT_TANTANGAN = 5;

    public function __construct(private KunciLogin $kunci, private AuditLogger $audit, private CekKodeDuaFaktor $cekKode) {}

    /** @return array{challenge_token: string, kedaluwarsa_pada: Carbon} */
    public function tantangan(string $email, string $password, string $perangkat): array
    {
        $email = mb_strtolower(trim($email));

        if ($this->kunci->terkunci($email)) {
            $this->audit->catat('login', 'user', null, 'ditolak', alasan: 'akun_terkunci');
            throw $this->galatTerkunci($email);
        }

        $user = User::where('email', $email)->first();
        if (! $user || ! Hash::check($password, $user->password)) {
            $this->kunci->gagal($email);
            $this->audit->catat('login', 'user', $user?->id, 'gagal', pelaku: $user, alasan: 'kredensial_salah');
            throw $this->kunci->terkunci($email) ? $this->galatTerkunci($email) : ValidationException::withMessages(['email' => __('auth.failed')]);
        }
        if (! $user->is_active) {
            $this->audit->catat('login', 'user', $user->id, 'ditolak', pelaku: $user, alasan: 'akun_nonaktif');
            throw ValidationException::withMessages(['email' => 'Akun Anda dinonaktifkan. Hubungi Super Admin.']);
        }
        if (! $user->hasAnyRole(self::ROLE)) { // AC-30.2
            $this->audit->catat('login', 'user', $user->id, 'ditolak', pelaku: $user, alasan: 'role_tidak_diizinkan_mobile');
            throw new AuthorizationException('Aplikasi mobile hanya untuk Admin SILAU dan Admin Keuangan.');
        }
        if (! $user->two_factor_confirmed_at) {
            throw ValidationException::withMessages(['email' => 'Autentikator belum diaktifkan. Masuk lewat web terlebih dahulu untuk mengaktifkannya.']);
        }

        $token = Str::random(64);
        $kedaluwarsa = now()->addMinutes(self::MENIT_TANTANGAN);
        Cache::put($this->kunciTantangan($token), ['user_id' => $user->id, 'perangkat' => $perangkat], $kedaluwarsa);

        return ['challenge_token' => $token, 'kedaluwarsa_pada' => $kedaluwarsa];
    }

    /** @return array{token: string, kedaluwarsa_pada: Carbon, user: User} */
    public function selesaikan(string $tantangan, ?string $kode, ?string $cadangan): array
    {
        $data = Cache::get($this->kunciTantangan($tantangan));
        $user = is_array($data) ? User::find($data['user_id']) : null;

        if (! $user || ! $user->is_active || ! $user->hasAnyRole(self::ROLE)) {
            throw ValidationException::withMessages(['challenge_token' => 'Sesi login kedaluwarsa. Ulangi login dengan email dan password.']);
        }
        if ($this->kunci->terkunci($user->email)) {
            Cache::forget($this->kunciTantangan($tantangan));
            throw $this->galatTerkunci($user->email);
        }
        if (! ($this->cekKode)($user, $kode, $cadangan)) {
            $this->kunci->gagal($user->email);
            $this->audit->catat('login', 'user', $user->id, 'gagal', pelaku: $user, alasan: 'kode_2fa_salah');
            throw ValidationException::withMessages(trim((string) $cadangan) !== ''
                ? ['recovery_code' => 'Recovery code tidak valid.'] : ['code' => 'Kode autentikator tidak valid.']);
        }

        Cache::forget($this->kunciTantangan($tantangan));
        $this->kunci->bersihkan($user->email);

        // Satu token per perangkat: login ulang di perangkat yang sama mencabut token lamanya.
        $user->tokens()->where('name', $data['perangkat'])->delete();
        $kedaluwarsa = now()->addHours(self::JAM_TOKEN);
        $token = $user->createToken($data['perangkat'], ['*'], $kedaluwarsa)->plainTextToken;
        $this->audit->catat('login', 'user', $user->id, pelaku: $user);

        return ['token' => $token, 'kedaluwarsa_pada' => $kedaluwarsa, 'user' => $user];
    }

    private function kunciTantangan(string $token): string
    {
        return 'tantangan-mobile:'.hash('sha256', $token);
    }

    private function galatTerkunci(string $email): ValidationException
    {
        return ValidationException::withMessages(['email' => 'Akun dikunci sementara. Coba lagi dalam '.$this->kunci->sisaMenit($email).' menit.']);
    }
}
