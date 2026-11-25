<?php

namespace App\Http\Controllers\Web\Auth;

use App\Actions\Akun\PeriksaKredensial;
use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Services\KunciLogin;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;

/** HW-08, FR-59, BR-59: kode TOTP atau recovery code sebelum sesi admin dibuat. */
class TantanganDuaFaktorController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $user = MasukDuaFaktor::penggunaMenunggu($request);
        if (! $user || ! $user->two_factor_confirmed_at) {
            return redirect()->route('login');
        }

        return view('auth.two-factor', ['email' => $user->email]);
    }

    public function store(Request $request, TwoFactorAuthenticationProvider $totp, KunciLogin $kunci, AuditLogger $audit): RedirectResponse
    {
        $user = MasukDuaFaktor::penggunaMenunggu($request);
        if (! $user || ! $user->two_factor_confirmed_at) {
            return redirect()->route('login');
        }

        if ($kunci->terkunci($user->email)) {
            $request->session()->forget(PeriksaKredensial::SESI_MENUNGGU);

            return redirect()->route('login')->withErrors(['email' => 'Akun dikunci sementara. Coba lagi dalam '.$kunci->sisaMenit($user->email).' menit.']);
        }

        $kode = preg_replace('/\s+/', '', (string) $request->input('code'));
        $cadangan = trim((string) $request->input('recovery_code'));

        $sah = false;
        if ($cadangan !== '' && in_array($cadangan, $user->recoveryCodes(), true)) {
            $user->replaceRecoveryCode($cadangan);
            $sah = true;
        } elseif ($kode !== '') {
            $sah = $totp->verify(Crypt::decrypt((string) $user->two_factor_secret), $kode);
        }

        if (! $sah) {
            $kunci->gagal($user->email);
            $audit->catat('login', 'user', $user->id, 'gagal', pelaku: $user, alasan: 'kode_2fa_salah');

            throw ValidationException::withMessages([
                $cadangan !== '' ? 'recovery_code' : 'code' => $cadangan !== '' ? 'Recovery code tidak valid.' : 'Kode autentikator tidak valid.',
            ]);
        }

        return MasukDuaFaktor::selesaikan($request, $user);
    }
}
