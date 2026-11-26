<?php

namespace App\Http\Controllers\Web\Publik;

use App\Http\Controllers\Controller;
use App\Models\Peminatan;
use App\Models\Track;
use App\Queries\KatalogQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/** HW-01–04 halaman publik tanpa login (FR-08, FR-09, BR-55, BR-75). */
class PublikController extends Controller
{
    public function beranda(): View
    {
        return view('publik.beranda', self::dataBeranda(false));
    }

    public function katalog(Request $request, KatalogQuery $katalog): View
    {
        $track = KatalogQuery::track();
        $pilih = array_key_exists((string) $request->query('track'), $track) ? (string) $request->query('track') : '';

        return view('publik.katalog', [
            'navAktif' => 'Program',
            'periode' => KatalogQuery::infoPeriode(),
            'trackTersedia' => $track,
            'track' => $pilih,
            'peminatan' => $katalog->daftar($pilih ?: null),
        ]);
    }

    public function peminatan(string $kode, KatalogQuery $katalog): View
    {
        $detail = $katalog->detail($kode);
        abort_if($detail === null, 404); // AC-01.3: peminatan nonaktif tidak tampil

        return view('publik.peminatan', [
            'navAktif' => 'Program',
            'peminatan' => $detail,
            'periode' => KatalogQuery::infoPeriode(),
            'tautanDaftar' => self::tautanDaftar(),
        ]);
    }

    /**
     * Dipakai juga oleh pratinjau CMS Super Admin ($draf = true, AC-29.2).
     *
     * @return array<string, mixed>
     */
    public static function dataBeranda(bool $draf): array
    {
        return [
            'navAktif' => 'Beranda',
            'pratinjau' => $draf,
            'periode' => KatalogQuery::infoPeriode(),
            'konten' => KatalogQuery::konten($draf),
            'track' => Track::where('is_active', true)->with(['peminatans' => fn ($q) => $q->where('is_active', true)->orderBy('nama')])->orderBy('nama')->get()
                ->filter(fn (Track $t) => $t->peminatans->isNotEmpty())
                ->map(fn (Track $t) => [
                    'kode' => $t->kode, 'nama' => $t->nama,
                    'peminatan' => $t->peminatans->map(fn (Peminatan $p) => $p->nama)->all(),
                    'href' => route('katalog', ['track' => $t->kode]),
                ])->values()->all(),
            'tautanDaftar' => self::tautanDaftar(),
        ];
    }

    /** Tamu → registrasi; peserta yang login → pilih peminatan. */
    private static function tautanDaftar(): string
    {
        return auth()->check() ? route('pendaftaran.baru') : route('register');
    }
}
