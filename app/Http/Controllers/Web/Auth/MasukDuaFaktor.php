<?php

namespace App\Http\Controllers\Web\Auth;

use App\Actions\Akun\PeriksaKredensial;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\KunciLogin;
use App\Support\Http\ArahPortal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Pembantu sesi untuk admin yang sudah lolos password tetapi belum punya sesi (BR-59, AC-03.6):
 * dipakai tantangan 2FA (HW-08) dan pendaftaran autentikator (HW-11 langkah 2).
 */
final class MasukDuaFaktor
{
    public static function penggunaMenunggu(Request $request): ?User
    {
        $id = $request->session()->get(PeriksaKredensial::SESI_MENUNGGU);
        $user = $id ? User::find($id) : null;

        return $user && $user->is_active && $user->adalahAdmin() ? $user : null;
    }

    public static function selesaikan(Request $request, User $user): RedirectResponse
    {
        $request->session()->forget(PeriksaKredensial::SESI_MENUNGGU);
        app(KunciLogin::class)->bersihkan($user->email);
        Auth::guard('web')->login($user);
        $request->session()->regenerate();
        app(AuditLogger::class)->catat('login', 'user', $user->id, pelaku: $user);

        return redirect()->intended(ArahPortal::untuk($user));
    }
}
