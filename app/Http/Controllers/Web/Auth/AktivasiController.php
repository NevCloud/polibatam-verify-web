<?php

namespace App\Http\Controllers\Web\Auth;

use App\Actions\Akun\AturanPassword;
use App\Actions\Akun\PeriksaKredensial;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;

/**
 * HW-11, FR-06, FR-59, AC-27.5: aktivasi akun admin dalam 3 langkah —
 * 1) atur password (tautan undangan 72 jam, sekali pakai), 2) pindai QR & konfirmasi kode TOTP,
 * 3) simpan recovery code. Admin lama tanpa TOTP (A-75) masuk langsung ke langkah 2 setelah login.
 */
class AktivasiController extends Controller
{
    use AturanPassword;

    public const SESI_KODE = 'aktivasi.tampilkan_kode';

    public function create(Request $request, string $token): View
    {
        $email = (string) $request->query('email');
        $user = User::where('email', $email)->first();
        /** @var PasswordBroker $broker */
        $broker = Password::broker('aktivasi');
        $sah = $user && $user->adalahAdmin() && $user->is_active && $broker->tokenExists($user, $token);

        return view('auth.aktivasi', ['langkah' => 1, 'token' => $token, 'email' => $email, 'kedaluwarsa' => ! $sah]);
    }

    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        $request->validate(['token' => ['required'], 'email' => ['required', 'email'], 'password' => $this->aturanPassword()]);

        $target = User::where('email', $request->string('email'))->first();
        if (! $target || ! $target->adalahAdmin() || ! $target->is_active) {
            throw ValidationException::withMessages(['password' => __('passwords.token')]);
        }

        $status = Password::broker('aktivasi')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => $password, 'email_verified_at' => $user->email_verified_at ?? now()])->save();
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['password' => __('passwords.token')]);
        }

        $audit->catat('aktivasi_akun', 'user', $target->id, pelaku: $target, alasan: 'password_diatur');
        $request->session()->put(PeriksaKredensial::SESI_MENUNGGU, $target->id);

        return redirect()->route('aktivasi.autentikator');
    }

    public function autentikator(Request $request, EnableTwoFactorAuthentication $aktifkan): View|RedirectResponse
    {
        $user = MasukDuaFaktor::penggunaMenunggu($request);
        if (! $user) {
            return redirect()->route('login');
        }
        if ($user->two_factor_confirmed_at) {
            return redirect()->route('two-factor.login');
        }

        $aktifkan($user); // rahasia TOTP dibuat sekali; muat ulang halaman tidak menggantinya

        return view('auth.aktivasi', [
            'langkah' => 2,
            'qr' => $user->twoFactorQrCodeSvg(),
            'kunciManual' => decrypt((string) $user->two_factor_secret),
        ]);
    }

    public function konfirmasi(Request $request, ConfirmTwoFactorAuthentication $konfirmasi, AuditLogger $audit): RedirectResponse
    {
        $user = MasukDuaFaktor::penggunaMenunggu($request);
        if (! $user || $user->two_factor_confirmed_at) {
            return redirect()->route('login');
        }

        $request->validate(['code' => ['required', 'string']]);

        try {
            $konfirmasi($user, preg_replace('/\s+/', '', (string) $request->input('code')));
        } catch (ValidationException) {
            throw ValidationException::withMessages(['code' => 'Kode autentikator tidak valid. Periksa jam perangkat Anda lalu coba lagi.']);
        }

        $audit->catat('aktivasi_akun', 'user', $user->id, pelaku: $user, alasan: '2fa_terdaftar');
        $request->session()->put(self::SESI_KODE, true);

        return MasukDuaFaktor::selesaikan($request, $user)->setTargetUrl(route('aktivasi.kode-pemulihan'));
    }

    public function kodePemulihan(Request $request): View|RedirectResponse
    {
        if (! $request->session()->get(self::SESI_KODE)) {
            return redirect()->route('akun.profil');
        }

        /** @var User $user */
        $user = $request->user();

        return view('auth.aktivasi', ['langkah' => 3, 'kode' => $user->recoveryCodes()]);
    }

    public function selesai(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESI_KODE);

        return redirect()->route('admin.dasbor');
    }
}
