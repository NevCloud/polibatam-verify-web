<?php

namespace App\Services;

use Illuminate\Support\Facades\RateLimiter;

/**
 * BR-03, FR-04: akun dikunci N menit setelah M gagal login berturut-turut (pengaturan `login_batas_gagal`,
 * `login_kunci_menit`). Kunci per email, bukan per IP, sehingga berlaku di web dan mobile.
 */
class KunciLogin
{
    public function __construct(private Pengaturan $pengaturan) {}

    public function terkunci(string $email): bool
    {
        return RateLimiter::tooManyAttempts($this->kunci($email), $this->pengaturan->int('login_batas_gagal'));
    }

    /** Sisa menit penguncian (dibulatkan ke atas). */
    public function sisaMenit(string $email): int
    {
        return max(1, (int) ceil(RateLimiter::availableIn($this->kunci($email)) / 60));
    }

    public function gagal(string $email): void
    {
        RateLimiter::hit($this->kunci($email), $this->pengaturan->int('login_kunci_menit') * 60);
    }

    public function bersihkan(string $email): void
    {
        RateLimiter::clear($this->kunci($email));
    }

    private function kunci(string $email): string
    {
        return 'kunci-login:'.mb_strtolower(trim($email));
    }
}
