<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\Pengaturan;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * NFR-03, AC-27.2: akun nonaktif langsung keluar; sesi berakhir setelah tidak aktif
 * (`sesi_admin_menit` untuk admin, `sesi_peserta_menit` untuk peserta).
 */
class JagaSesi
{
    public const KUNCI = 'terakhir_aktif';

    public function __construct(private Pengaturan $pengaturan) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User) {
            $sekarang = now()->getTimestamp();
            $terakhir = (int) $request->session()->get(self::KUNCI, $sekarang);
            $batas = $this->pengaturan->int($user->adalahAdmin() ? 'sesi_admin_menit' : 'sesi_peserta_menit') * 60;

            if (! $user->is_active) {
                return $this->keluar($request, 'Akun Anda dinonaktifkan. Hubungi Super Admin.');
            }
            if ($sekarang - $terakhir > $batas) {
                return $this->keluar($request, 'Sesi berakhir karena tidak aktif. Silakan masuk kembali.');
            }

            $request->session()->put(self::KUNCI, $sekarang);
        }

        return $next($request);
    }

    private function keluar(Request $request, string $pesan): Response
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $request->expectsJson()
            ? response()->json(['success' => false, 'message' => $pesan, 'code' => 'TIDAK_TERAUTENTIKASI'], 401)
            : redirect()->route('login')->with('status', $pesan);
    }
}
