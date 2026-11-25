<?php

namespace App\Actions\Akun;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;

/** FR-59, BR-59: kode TOTP atau recovery code (sekali pakai). Dipakai tantangan 2FA web (HW-08) dan login mobile (P06). */
class CekKodeDuaFaktor
{
    public function __construct(private TwoFactorAuthenticationProvider $totp) {}

    public function __invoke(User $user, ?string $kode, ?string $cadangan): bool
    {
        $kode = preg_replace('/\s+/', '', (string) $kode);
        $cadangan = trim((string) $cadangan);

        if ($cadangan !== '' && in_array($cadangan, $user->recoveryCodes(), true)) {
            $user->replaceRecoveryCode($cadangan);

            return true;
        }

        return $kode !== '' && $user->two_factor_secret !== null
            && $this->totp->verify(Crypt::decrypt((string) $user->two_factor_secret), $kode);
    }
}
