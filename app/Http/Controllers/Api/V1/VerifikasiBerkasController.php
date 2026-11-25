<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Pendaftaran\SelesaikanVerifikasi;
use App\Actions\Pendaftaran\VerifikasiDokumen;
use App\Models\Dokumen;
use App\Models\KriteriaChecklist;
use App\Models\Pendaftaran;
use App\Queries\AntreanVerifikasiQuery;
use App\Services\CekVersiData;
use App\Services\NikHasher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Verifikasi Berkas (Admin SILAU)
 *
 * FR-18–FR-21, FR-61, BR-13, BR-61, BR-64, BR-66, BR-74. Aturan sama dengan web (AC-30.3): Action yang sama, kanal audit "mobile".
 * Tidak ada endpoint keputusan massal; Fast Verify = keputusan satu per satu lewat endpoint yang sama (BR-74).
 */
class VerifikasiBerkasController extends ApiController
{
    /**
     * Antrean verifikasi berkas
     *
     * Pendaftaran berstatus Diajukan (tanpa Draf), urut pengajuan terlama.
     *
     * @queryParam periode integer ID periode pendaftaran.
     * @queryParam track string Kode track. Example: TI
     * @queryParam peminatan integer ID peminatan.
     * @queryParam jenis string `baru` atau `revisi` (revisi masuk). Example: revisi
     * @queryParam page integer Example: 1
     * @queryParam per_page integer 1–100, bawaan 20. Example: 20
     */
    public function index(Request $request): JsonResponse
    {
        $f = $request->query();
        $revisi = AntreanVerifikasiQuery::pernahDitolak();

        $halaman = Pendaftaran::untukAdmin()->where('status', 'diajukan')
            ->with('peminatanPeriode.peminatan.track')
            ->select('pendaftarans.*')->selectSub(fn ($q) => $revisi($q)->limit(1), 'revisi')
            ->when(filled($f['periode'] ?? null), fn (Builder $q) => $q->whereHas('peminatanPeriode', fn (Builder $t) => $t->where('periode_pendaftaran_id', (int) $f['periode'])))
            ->when(filled($f['peminatan'] ?? null), fn (Builder $q) => $q->whereHas('peminatanPeriode', fn (Builder $t) => $t->where('peminatan_id', (int) $f['peminatan'])))
            ->when(filled($f['track'] ?? null), fn (Builder $q) => $q->whereHas('peminatanPeriode.peminatan.track', fn (Builder $t) => $t->where('kode', (string) $f['track'])))
            ->when(($f['jenis'] ?? null) === 'revisi', fn (Builder $q) => $q->whereExists($revisi))
            ->when(($f['jenis'] ?? null) === 'baru', fn (Builder $q) => $q->whereNotExists($revisi))
            ->orderBy('waktu_pengajuan')->orderBy('id')
            ->paginate(self::perHalaman($request));

        return $this->daftar($halaman, 'Antrean verifikasi berkas berhasil dimuat.', fn (Pendaftaran $p) => [
            'id' => $p->id,
            'nomor_pendaftaran' => $p->nomor_pendaftaran,
            'nama' => (string) $p->nama_lengkap,
            'nik' => NikHasher::samarkan($p->nik_terenkripsi), // BR-62
            'track' => $p->peminatanPeriode->peminatan->track->nama,
            'peminatan' => $p->peminatanPeriode->peminatan->nama,
            'revisi' => (bool) $p->getAttribute('revisi'),
            'diajukan_pada' => self::iso($p->waktu_pengajuan),
        ]);
    }

    /**
     * Detail verifikasi berkas
     *
     * Biodata (NIK tersamar), dokumen + checklist per jenis, kuota minimum peminatan. `versi` tiap dokumen dikirim kembali saat memutuskan.
     */
    public function show(Pendaftaran $pendaftaran): JsonResponse
    {
        abort_if($pendaftaran->adalahDraf(), 404); // BR-64
        $pendaftaran->load('user', 'peminatanPeriode.peminatan.track', 'dokumens.versiAktif');
        $pp = $pendaftaran->peminatanPeriode;
        $kriteria = KriteriaChecklist::where('is_active', true)->orderBy('urutan')->get()->groupBy('jenis_dokumen');

        return $this->ok('Detail verifikasi berkas berhasil dimuat.', [
            'id' => $pendaftaran->id,
            'nomor_pendaftaran' => $pendaftaran->nomor_pendaftaran,
            'status' => $pendaftaran->status->getValue(),
            'bisa_diputuskan' => $pendaftaran->status->getValue() === 'diajukan',
            'diajukan_pada' => self::iso($pendaftaran->waktu_pengajuan),
            'track' => $pp->peminatan->track->nama,
            'peminatan' => $pp->peminatan->nama,
            'kuota' => [ // BR-75 (K-42): hanya kuota minimum + jumlah NPD Terbit
                'minimum' => (int) $pp->kuota_min,
                'npd_terbit' => Pendaftaran::where('peminatan_periode_id', $pp->id)->where('status', 'npd_terbit')->count(),
            ],
            'biodata' => [
                'nama_lengkap' => $pendaftaran->nama_lengkap,
                'nik' => NikHasher::samarkan($pendaftaran->nik_terenkripsi),
                'tempat_lahir' => $pendaftaran->tempat_lahir,
                'tanggal_lahir' => $pendaftaran->tanggal_lahir?->toDateString(),
                'jenis_kelamin' => $pendaftaran->jenis_kelamin,
                'asal_sekolah' => $pendaftaran->asal_sekolah,
                'tahun_lulus' => $pendaftaran->tahun_lulus,
                'email' => $pendaftaran->user->email,
                'no_hp' => $pendaftaran->no_hp,
            ],
            'dokumen' => $pendaftaran->dokumens->whereNotNull('versi_aktif_id')
                ->sortBy(fn (Dokumen $d) => array_search($d->jenis, array_keys(Dokumen::JENIS), true))
                ->map(fn (Dokumen $d) => [
                    'id' => $d->id,
                    'jenis' => $d->jenis,
                    'label' => $d->label(),
                    'wajib' => $d->wajib,
                    'status' => $d->status->getValue(),
                    'versi' => CekVersiData::versi($d),
                    'berkas' => $d->versiAktif ? [
                        'id' => $d->versiAktif->id, 'nama' => $d->versiAktif->nama_asli, 'mime' => $d->versiAktif->mime,
                        'ukuran' => $d->versiAktif->ukuran, 'diunggah_pada' => self::iso($d->versiAktif->diunggah_pada),
                    ] : null,
                    'kriteria' => $kriteria->get($d->jenis, collect())->map(fn (KriteriaChecklist $k) => ['id' => $k->id, 'teks' => $k->teks])->values()->all(),
                ])->values()->all(),
        ]);
    }

    /**
     * Keputusan per dokumen
     *
     * Valid hanya bila semua kriteria checklist dicentang; Ditolak wajib kriteria tak terpenuhi dan/atau catatan (A-64). Versi usang → 409 `SUDAH_DIVERIFIKASI`.
     *
     * @bodyParam keputusan string required `valid` atau `ditolak`. Example: valid
     * @bodyParam versi string required Nilai `versi` dokumen dari detail. Example: 2026-11-26 03:00:00.000000
     * @bodyParam kriteria integer[] ID kriteria yang terpenuhi (dicentang).
     * @bodyParam catatan string Catatan penolakan.
     */
    public function putuskan(Request $request, Pendaftaran $pendaftaran, Dokumen $dokumen, VerifikasiDokumen $verifikasi): JsonResponse
    {
        abort_if($pendaftaran->adalahDraf() || $dokumen->pendaftaran_id !== $pendaftaran->id || $dokumen->versi_aktif_id === null, 404);
        $data = $request->validate([
            'keputusan' => ['required', 'string'],
            'versi' => ['required', 'string'],
            'kriteria' => ['array'],
            'kriteria.*' => ['integer'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);
        $verifikasi($dokumen, $data['versi'], $data['keputusan'], $data['kriteria'] ?? [], $data['catatan'] ?? null, self::admin($request));
        $dokumen->refresh();

        return $this->ok('Keputusan dokumen tersimpan.', [
            'id' => $dokumen->id, 'status' => $dokumen->status->getValue(), 'versi' => CekVersiData::versi($dokumen),
        ]);
    }

    /**
     * Selesaikan verifikasi
     *
     * Semua dokumen harus sudah diputuskan → Perlu Revisi (ada Ditolak) atau Masuk Kuota (semua Valid, K-42).
     */
    public function selesai(Request $request, Pendaftaran $pendaftaran, SelesaikanVerifikasi $selesaikan): JsonResponse
    {
        abort_if($pendaftaran->adalahDraf(), 404);
        $hasil = $selesaikan($pendaftaran, self::admin($request));

        return $this->ok("{$hasil->nomor_pendaftaran} selesai diverifikasi: {$hasil->status->label()}.", [
            'id' => $hasil->id, 'nomor_pendaftaran' => $hasil->nomor_pendaftaran, 'status' => $hasil->status->getValue(),
        ]);
    }
}
