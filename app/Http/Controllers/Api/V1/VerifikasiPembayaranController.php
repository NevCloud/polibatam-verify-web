<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Tagihan\VerifikasiPembayaran;
use App\Exceptions\TransisiTidakSah;
use App\Models\BuktiPembayaran;
use App\Models\Tagihan;
use App\Services\CekVersiData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Verifikasi Pembayaran (Admin Keuangan)
 *
 * FR-31–FR-35, FR-61, BR-38, BR-39, BR-61, BR-66, BR-74. Aturan sama dengan web (AC-30.3): Action yang sama, kanal audit "mobile".
 */
class VerifikasiPembayaranController extends ApiController
{
    /**
     * Antrean verifikasi pembayaran
     *
     * Tagihan Menunggu Verifikasi, urut pengunggahan terlama. `siap=1` = hanya yang lolos gerbang ganda (Fast Verify, BR-74).
     *
     * @queryParam jenis string `formulir` atau `semester`. Example: formulir
     * @queryParam semester_ke integer Semester ke-n (untuk jenis semester).
     * @queryParam peminatan integer ID peminatan.
     * @queryParam periode integer ID periode pendaftaran.
     * @queryParam siap boolean Hanya tagihan yang dapat ditandai Lunas. Example: 1
     * @queryParam page integer Example: 1
     * @queryParam per_page integer 1–100, bawaan 20. Example: 20
     */
    public function index(Request $request): JsonResponse
    {
        $f = $request->query();
        $halaman = Tagihan::where('status', 'menunggu_verifikasi')
            ->with('pendaftaran.peminatanPeriode.peminatan', 'buktiTerakhir')
            ->when(in_array($f['jenis'] ?? null, ['formulir', 'semester'], true), fn (Builder $q) => $q->where('jenis', $f['jenis']))
            ->when(filled($f['semester_ke'] ?? null), fn (Builder $q) => $q->where('semester_ke', (int) $f['semester_ke']))
            ->when(filled($f['peminatan'] ?? null), fn (Builder $q) => $q->whereHas('pendaftaran.peminatanPeriode', fn (Builder $p) => $p->where('peminatan_id', (int) $f['peminatan'])))
            ->when(filled($f['periode'] ?? null), fn (Builder $q) => $q->whereHas('pendaftaran.peminatanPeriode', fn (Builder $p) => $p->where('periode_pendaftaran_id', (int) $f['periode'])))
            ->when($request->boolean('siap'), fn (Builder $q) => $q->lolosGerbang())
            ->orderBy('updated_at')->orderBy('id')
            ->paginate(self::perHalaman($request));

        return $this->daftar($halaman, 'Antrean verifikasi pembayaran berhasil dimuat.', fn (Tagihan $t) => [
            'kode_referensi' => $t->kode_referensi,
            'jenis' => $t->jenis,
            'semester_ke' => $t->semester_ke,
            'nominal_total' => $t->nominal_total,
            'status' => $t->status->getValue(),
            'nama' => (string) $t->pendaftaran->nama_lengkap,
            'nomor_pendaftaran' => $t->pendaftaran->nomor_pendaftaran,
            'peminatan' => $t->pendaftaran->peminatanPeriode->peminatan->nama,
            'lolos_gerbang' => $t->lolosGerbang(),
            'bukti_diunggah_pada' => self::iso($t->buktiTerakhir?->diunggah_pada),
        ]);
    }

    /**
     * Detail verifikasi pembayaran
     *
     * Nominal + kode unik, pemilik, bukti & data pengiriman, status gerbang ganda. `versi` dikirim kembali saat memutuskan.
     */
    public function show(Tagihan $tagihan): JsonResponse
    {
        abort_if($tagihan->status->getValue() === 'dibatalkan', 404);
        $tagihan->load('pendaftaran.peminatanPeriode.peminatan.track', 'pendaftaran.user', 'buktis');
        $p = $tagihan->pendaftaran;

        return $this->ok('Detail tagihan berhasil dimuat.', [
            'kode_referensi' => $tagihan->kode_referensi,
            'jenis' => $tagihan->jenis,
            'semester_ke' => $tagihan->semester_ke,
            'label' => $tagihan->labelJenis(),
            'nominal_dasar' => $tagihan->nominal_dasar,
            'kode_unik' => $tagihan->kode_unik,
            'nominal_total' => $tagihan->nominal_total,
            'status' => $tagihan->status->getValue(),
            'jatuh_tempo' => $tagihan->jatuh_tempo?->toDateString(),
            'versi' => CekVersiData::versi($tagihan),
            'bisa_diputuskan' => $tagihan->status->getValue() === 'menunggu_verifikasi',
            'gerbang' => [ // BR-39
                'lolos' => $tagihan->lolosGerbang(),
                'status_pendaftaran' => $p->status->getValue(),
                'pesan' => $tagihan->lolosGerbang() ? null : VerifikasiPembayaran::PESAN_GERBANG,
            ],
            'pemilik' => [
                'nama' => (string) $p->nama_lengkap, 'email' => $p->user->email, 'nomor_pendaftaran' => $p->nomor_pendaftaran,
                'track' => $p->peminatanPeriode->peminatan->track->nama, 'peminatan' => $p->peminatanPeriode->peminatan->nama,
            ],
            'rekening_tujuan' => ['bank' => $tagihan->rekening_bank, 'nomor' => $tagihan->rekening_nomor, 'atas_nama' => $tagihan->rekening_atas_nama],
            'bukti' => $tagihan->buktis->map(fn (BuktiPembayaran $b) => [
                'id' => $b->id,
                'mime' => $b->mime,
                'diunggah_pada' => self::iso($b->diunggah_pada),
                'tanggal_transfer' => $b->tanggal_transfer?->toDateString(),
                'nominal_transfer' => $b->nominal_transfer,
                'selisih_nominal' => $b->nominal_transfer !== null && $b->nominal_transfer !== $tagihan->nominal_total, // BR-72: hanya peringatan
                'bank_pengirim' => $b->bank_pengirim,
                'nama_pengirim' => $b->nama_pengirim,
                'keputusan' => $b->keputusan,
                'alasan' => $b->alasan,
            ])->values()->all(),
            'alasan_baku' => VerifikasiPembayaran::ALASAN_BAKU,
        ]);
    }

    /**
     * Tandai Lunas
     *
     * Formulir → NPD terbit + tagihan Semester 1; semester → peserta Aktif. Belum lolos gerbang ganda → 409; versi usang → 409 `SUDAH_DIVERIFIKASI`.
     *
     * @bodyParam versi string required Nilai `versi` dari detail. Example: 2026-11-26 03:00:00.000000
     */
    public function lunas(Request $request, Tagihan $tagihan, VerifikasiPembayaran $verifikasi): JsonResponse
    {
        $versi = $request->validate(['versi' => ['required', 'string']])['versi'];
        abort_if($tagihan->status->getValue() === 'dibatalkan', 404);
        if (! $tagihan->lolosGerbang()) {
            throw new TransisiTidakSah(VerifikasiPembayaran::PESAN_GERBANG); // §6.3: gerbang ganda = 409 di API
        }
        $verifikasi($tagihan, $versi, 'lunas', [], null, self::admin($request));

        return $this->ok("{$tagihan->kode_referensi} ditandai Lunas.", ['kode_referensi' => $tagihan->kode_referensi, 'status' => $tagihan->status->getValue()]);
    }

    /**
     * Tolak pembayaran
     *
     * Wajib alasan baku dan/atau catatan (BR-38, A-64). Peserta dapat mengunggah ulang bukti.
     *
     * @bodyParam versi string required Nilai `versi` dari detail. Example: 2026-11-26 03:00:00.000000
     * @bodyParam alasan string[] Kunci alasan baku: mutasi_tidak_ditemukan, nominal_tidak_sesuai, bukti_tidak_terbaca.
     * @bodyParam catatan string Catatan penolakan.
     */
    public function tolak(Request $request, Tagihan $tagihan, VerifikasiPembayaran $verifikasi): JsonResponse
    {
        $data = $request->validate([
            'versi' => ['required', 'string'],
            'alasan' => ['array'],
            'alasan.*' => ['string'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);
        abort_if($tagihan->status->getValue() === 'dibatalkan', 404);
        $verifikasi($tagihan, $data['versi'], 'tolak', $data['alasan'] ?? [], $data['catatan'] ?? null, self::admin($request));

        return $this->ok("{$tagihan->kode_referensi} ditolak.", ['kode_referensi' => $tagihan->kode_referensi, 'status' => $tagihan->status->getValue()]);
    }
}
