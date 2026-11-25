<?php

namespace App\Http\Controllers\Web\Akun;

use App\Actions\Akun\AturanPassword;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\CabutAkses;
use App\Support\Ui\Format;
use App\Support\Ui\PortalContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;

/** HW-12 (FR-03, FR-59, K-39): data akun, ganti password, recovery code (admin), sesi aktif. */
class ProfilController extends Controller
{
    use AturanPassword;

    public function show(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $sesiIni = $request->session()->getId();

        $sesi = DB::table('sessions')->where('user_id', $user->id)->orderByDesc('last_activity')->get()
            ->map(fn ($s) => [
                'perangkat' => self::perangkat((string) $s->user_agent),
                'ip' => (string) $s->ip_address,
                'terakhir' => Format::tanggalJam(now()->setTimestamp((int) $s->last_activity)),
                'ini' => $s->id === $sesiIni,
            ])->all();

        // Entri login terbaru = sesi ini; yang ditampilkan adalah login sebelumnya.
        $loginLalu = AuditLog::where('user_id', $user->id)->where('aksi', 'login')->where('hasil', 'berhasil')
            ->latest('id')->skip(1)->first();

        return view('akun.profil', [
            'portal' => PortalContext::dariPengguna($user, 'Profil'),
            'akun' => [
                'nama' => $user->name,
                'inisial' => PortalContext::inisial($user->name),
                'email' => $user->email,
                'peran' => $user->labelRole(),
                'bergabung' => $user->created_at ? Format::tanggal($user->created_at) : '—',
                'email_terverifikasi' => $user->email_verified_at !== null,
                'admin' => $user->adalahAdmin(),
            ],
            'loginTerakhir' => $loginLalu ? Format::tanggalJam($loginLalu->created_at).' · IP '.$loginLalu->ip_address : null,
            'sisaKode' => $user->adalahAdmin() && $user->two_factor_recovery_codes ? count($user->recoveryCodes()) : null,
            'kode' => session('kode_pemulihan'),
            'sesi' => $sesi,
        ]);
    }

    public function perbaruiProfil(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:150']]);
        $request->user()->update(['name' => $data['name']]);

        return back()->with('status', 'Profil berhasil disimpan.');
    }

    public function gantiPassword(Request $request, AuditLogger $audit, CabutAkses $cabut): RedirectResponse
    {
        $request->validateWithBag('sandi', [
            'current_password' => ['required', 'current_password'],
            'password' => $this->aturanPassword(),
        ]);

        /** @var User $user */
        $user = $request->user();
        DB::transaction(function () use ($request, $user, $audit, $cabut) {
            $user->forceFill(['password' => $request->input('password')])->save();
            $cabut->semua($user, $request->session()->getId()); // sesi & token di perangkat lain diakhiri
            $audit->catat('ubah_password', 'user', $user->id);
        });

        return back()->with('status', 'Password berhasil diperbarui. Sesi di perangkat lain telah diakhiri.');
    }

    /** Lihat ulang atau buat ulang recovery code (admin); keduanya meminta password lagi. */
    public function kodePemulihan(Request $request, GenerateNewRecoveryCodes $buatUlang, AuditLogger $audit): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($user->adalahAdmin(), 403);
        $request->validateWithBag('kode', ['current_password' => ['required', 'current_password'], 'aksi' => ['required', 'in:lihat,buat_ulang']]);

        if ($request->input('aksi') === 'buat_ulang') {
            $buatUlang($user);
            $audit->catat('buat_ulang_kode_pemulihan', 'user', $user->id);
        }

        return back()->with('kode_pemulihan', $user->fresh()?->recoveryCodes());
    }

    public function akhiriSesiLain(Request $request, CabutAkses $cabut, AuditLogger $audit): RedirectResponse
    {
        $request->validateWithBag('sesi', ['current_password' => ['required', 'current_password']]);
        /** @var User $user */
        $user = $request->user();
        $cabut->semua($user, $request->session()->getId());
        $audit->catat('akhiri_sesi_lain', 'user', $user->id);

        return back()->with('status', 'Semua sesi lain telah diakhiri.');
    }

    private static function perangkat(string $agen): string
    {
        $peramban = collect(['Edg' => 'Edge', 'Chrome' => 'Chrome', 'Firefox' => 'Firefox', 'Safari' => 'Safari'])
            ->first(fn ($nama, $kunci) => str_contains($agen, $kunci)) ?? 'Peramban';
        $os = collect(['Windows' => 'Windows', 'Android' => 'Android', 'iPhone' => 'iOS', 'Mac OS' => 'macOS', 'Linux' => 'Linux'])
            ->first(fn ($nama, $kunci) => str_contains($agen, $kunci)) ?? 'perangkat lain';

        return "{$peramban} · {$os}";
    }
}
