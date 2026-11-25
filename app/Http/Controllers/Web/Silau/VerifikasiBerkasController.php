<?php

namespace App\Http\Controllers\Web\Silau;

use App\Actions\Pendaftaran\SelesaikanVerifikasi;
use App\Actions\Pendaftaran\UnggahDokumen;
use App\Actions\Pendaftaran\VerifikasiDokumen;
use App\Http\Controllers\Controller;
use App\Models\Dokumen;
use App\Models\DokumenVersi;
use App\Models\KriteriaChecklist;
use App\Models\Pendaftaran;
use App\Models\User;
use App\Queries\AntreanVerifikasiQuery;
use App\Services\CekVersiData;
use App\Services\NikHasher;
use App\Support\Ui\Format;
use App\Support\Ui\PortalContext;
use App\Support\Ui\TampilanPendaftaran;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * HW-25 antrean & HW-26 detail verifikasi berkas (FR-18–21, FR-61, BR-13, BR-61, BR-64, BR-66). Admin SILAU & Super Admin.
 * Satu form per pendaftaran: setiap dokumen diputuskan sendiri-sendiri (VerifikasiDokumen) lalu diselesaikan, dalam satu transaksi.
 */
class VerifikasiBerkasController extends Controller
{
    public function index(Request $request, AntreanVerifikasiQuery $query): View
    {
        return view('admin.silau.verifikasi-berkas.index', $query->halaman($request->query()) + [
            'portal' => PortalContext::dariPengguna($request->user(), 'Verifikasi Berkas'),
        ]);
    }

    public function show(Request $request, Pendaftaran $pendaftaran): View
    {
        abort_if($pendaftaran->adalahDraf(), 404); // BR-64: Draf tidak pernah terlihat admin
        TampilanPendaftaran::muat($pendaftaran);
        $pp = $pendaftaran->peminatanPeriode;
        $ringkasan = TampilanPendaftaran::ringkasan($pendaftaran);
        $kriteria = KriteriaChecklist::where('is_active', true)->orderBy('urutan')->get()->groupBy('jenis_dokumen');

        return view('admin.silau.verifikasi-berkas.show', [
            'portal' => PortalContext::dariPengguna($request->user(), 'Verifikasi Berkas'),
            'pendaftar' => $ringkasan + [
                'inisial' => PortalContext::inisial($ringkasan['nama']),
                'diajukan' => $pendaftaran->waktu_pengajuan ? Format::tanggalJam($pendaftaran->waktu_pengajuan) : '—',
            ],
            'kuota' => [ // BR-75: informasi kuota hanya untuk admin
                'minimum' => (int) $pp->kuota_min,
                'npd' => Pendaftaran::where('peminatan_periode_id', $pp->id)->where('status', 'npd_terbit')->count(),
            ],
            'biodata' => [
                'Nama lengkap' => $pendaftaran->nama_lengkap ?: '—',
                'NIK' => NikHasher::samarkan($pendaftaran->nik_terenkripsi), // BR-62, NFR-12
                'Tempat, tanggal lahir' => TampilanPendaftaran::dataDiri($pendaftaran)['Tempat, tanggal lahir'],
                'Asal sekolah' => ($pendaftaran->asal_sekolah ?: '—').($pendaftaran->tahun_lulus ? ' · lulus '.$pendaftaran->tahun_lulus : ''),
                'Email' => $pendaftaran->user->email,
                'No. HP' => $pendaftaran->no_hp ?: '—',
            ],
            'dokumen' => $pendaftaran->dokumens->whereNotNull('versi_aktif_id')
                ->sortBy(fn (Dokumen $d) => array_search($d->jenis, array_keys(Dokumen::JENIS), true))
                ->map(fn (Dokumen $d) => $this->dokumen($pendaftaran, $d, $kriteria->get($d->jenis, collect())))->values()->all(),
            'bisaDiputuskan' => $pendaftaran->status->getValue() === 'diajukan',
        ]);
    }

    public function simpan(Request $request, Pendaftaran $pendaftaran, VerifikasiDokumen $verifikasi, SelesaikanVerifikasi $selesaikan): RedirectResponse
    {
        abort_if($pendaftaran->adalahDraf(), 404);
        /** @var User $admin */
        $admin = $request->user();

        $hasil = DB::transaction(function () use ($request, $pendaftaran, $verifikasi, $selesaikan, $admin) {
            $galat = [];
            $menunggu = Dokumen::where('pendaftaran_id', $pendaftaran->id)->whereNotNull('versi_aktif_id')
                ->where('status', 'menunggu_verifikasi')->get();

            foreach ($menunggu as $d) {
                $isian = (array) $request->input("dokumen.{$d->id}", []);
                if (! in_array($isian['keputusan'] ?? null, VerifikasiDokumen::KEPUTUSAN, true)) {
                    $galat["dokumen.{$d->id}.keputusan"] = "Pilih Valid atau Ditolak untuk {$d->label()}.";

                    continue;
                }
                try {
                    $verifikasi($d, $isian['versi'] ?? null, $isian['keputusan'], (array) ($isian['kriteria'] ?? []), $isian['catatan'] ?? null, $admin);
                } catch (ValidationException $e) {
                    foreach ($e->errors() as $kunci => $pesan) {
                        $galat["dokumen.{$d->id}.{$kunci}"] = $d->label().': '.$pesan[0];
                    }
                }
            }

            if ($galat) {
                throw ValidationException::withMessages($galat); // rollback: tidak ada keputusan yang tersimpan sebagian
            }

            return $selesaikan($pendaftaran, $admin);
        });

        return to_route('admin.verifikasi-berkas.index')
            ->with('status', "{$hasil->nomor_pendaftaran} selesai diverifikasi: {$hasil->status->label()}.");
    }

    /** Document viewer admin (NFR-07c, NFR-12): URL bertanda tangan ≤ 15 menit; Draf tidak pernah dapat dibuka (BR-64). */
    public function lihat(Pendaftaran $pendaftaran, DokumenVersi $versi): StreamedResponse
    {
        abort_if($pendaftaran->adalahDraf(), 404);
        abort_unless($versi->dokumen()->where('pendaftaran_id', $pendaftaran->id)->exists(), 404);
        abort_unless(Storage::disk(UnggahDokumen::DISK)->exists($versi->path), 404);

        return Storage::disk(UnggahDokumen::DISK)->response($versi->path, $versi->nama_asli, [
            'Content-Type' => $versi->mime, 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store',
        ], 'inline');
    }

    /**
     * @param  Collection<int, KriteriaChecklist>  $kriteria
     * @return array<string, mixed>
     */
    private function dokumen(Pendaftaran $p, Dokumen $d, $kriteria): array
    {
        $v = $d->versiAktif;

        return [
            'id' => $d->id,
            'jenis' => $d->jenis,
            'label' => $d->label().($d->wajib ? '' : ' (opsional)'),
            'status' => $d->status->label(),
            'berkas' => [
                'nama' => $v?->nama_asli,
                'info' => $v ? strtoupper(pathinfo($v->nama_asli, PATHINFO_EXTENSION)).' · '.Format::tanggal($v->diunggah_pada) : '',
                'gambar' => $v && str_starts_with($v->mime, 'image/'),
                'url' => $v ? URL::temporarySignedRoute('admin.verifikasi-berkas.berkas', now()->addMinutes(TampilanPendaftaran::MENIT_URL), ['pendaftaran' => $p, 'versi' => $v->id]) : '#',
            ],
            'versi' => CekVersiData::versi($d),
            'bisa_diputuskan' => $d->status->getValue() === 'menunggu_verifikasi',
            'kriteria' => $kriteria->map(fn (KriteriaChecklist $k) => ['id' => $k->id, 'teks' => $k->teks])->values()->all(),
            // AC-08.4: keputusan versi sebelumnya (mis. alasan penolakan saat revisi)
            'riwayat' => $d->versis->flatMap(fn (DokumenVersi $dv) => $dv->verifikasi)->sortByDesc('id')->take(3)
                ->map(fn ($k) => ['waktu' => Format::tanggalJam($k->created_at), 'keputusan' => $k->keputusan === 'valid' ? 'Valid' : 'Ditolak', 'alasan' => $k->alasan])
                ->values()->all(),
        ];
    }
}
