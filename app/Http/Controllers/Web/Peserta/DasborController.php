<?php

namespace App\Http\Controllers\Web\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Dokumen;
use App\Models\Pendaftaran;
use App\Models\PeriodePendaftaran;
use App\Models\User;
use App\Services\NikHasher;
use App\Support\Ui\Format;
use App\Support\Ui\PortalContext;
use App\Support\Ui\TampilanPendaftaran;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** HW-13: dasbor peserta (FR-15, FR-53, FR-68) dan halaman status & riwayat pendaftaran (menu "Status"). */
class DasborController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $p = self::pendaftaranUtama($user);
        $dasar = [
            'portal' => PortalContext::dariPengguna($user, 'Dashboard'),
            'sapaan' => strtok($user->name, ' ') ?: $user->name,
            'notifikasi' => NotifikasiController::terbaru($user, 3),
        ];

        if (! $p) {
            $buka = PeriodePendaftaran::where('status', 'dibuka')->where('tanggal_mulai', '<=', now())->where('tanggal_selesai', '>', now())
                ->orderBy('tanggal_selesai')->first();

            return view('peserta.dasbor', $dasar + [
                'pendaftaran' => null,
                'periodeBuka' => $buka ? ['nama' => $buka->nama, 'tutup' => Format::tanggal($buka->tanggal_selesai)] : null,
            ]);
        }

        TampilanPendaftaran::muat($p);
        $kurang = $p->kekurangan();
        $wajib = count(array_filter(Dokumen::JENIS, fn (array $j) => $j['wajib']));

        return view('peserta.dasbor', $dasar + [
            'pendaftaran' => TampilanPendaftaran::ringkasan($p),
            'tracker' => TampilanPendaftaran::tracker($p),
            'banner' => TampilanPendaftaran::banner($p),
            'kartu' => [
                'kolom_terisi' => count(Pendaftaran::KOLOM_WAJIB) - count($kurang['data']),
                'kolom_total' => count(Pendaftaran::KOLOM_WAJIB),
                'berkas_terunggah' => $wajib - count($kurang['berkas']),
                'berkas_total' => $wajib,
            ],
            'biodata' => [
                'nama' => $p->nama_lengkap ?: $user->name,
                'nik' => $p->nik_terenkripsi ? NikHasher::samarkan($p->nik_terenkripsi) : null,
                'email' => $user->email,
                'no_hp' => $p->no_hp,
            ],
            'tautan' => [
                'formulir' => route('pendaftaran.formulir', $p),
                'berkas' => $p->adalahDraf() ? route('pendaftaran.formulir', $p).'?tab=berkas' : route('pendaftaran.berkas', $p),
                'kartu' => $p->adalahDraf() ? null : route('pendaftaran.kartu', $p),
            ],
        ]);
    }

    public function status(Request $request): View|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $p = self::pendaftaranUtama($user);

        if (! $p) {
            return to_route('pendaftaran.baru');
        }

        TampilanPendaftaran::muat($p);

        return view('peserta.status', [
            'portal' => PortalContext::dariPengguna($user, 'Status'),
            'pendaftaran' => TampilanPendaftaran::ringkasan($p),
            'banner' => TampilanPendaftaran::banner($p),
            'riwayat' => TampilanPendaftaran::riwayat($p),
            'diperbarui' => Format::tanggalJam($p->updated_at),
            'langkah' => TampilanPendaftaran::langkah($p),
            'tautan' => [
                'kartu' => $p->adalahDraf() ? null : route('pendaftaran.kartu', $p),
                'berkas' => $p->adalahDraf() ? null : route('pendaftaran.berkas', $p),
            ],
        ]);
    }

    /** Pendaftaran yang ditampilkan: yang masih berjalan, atau yang terakhir bila semuanya sudah berstatus akhir. */
    public static function pendaftaranUtama(User $user): ?Pendaftaran
    {
        return Pendaftaran::where('user_id', $user->id)->belumAkhir()->latest('id')->first()
            ?? Pendaftaran::where('user_id', $user->id)->latest('id')->first();
    }
}
