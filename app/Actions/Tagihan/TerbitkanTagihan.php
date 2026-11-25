<?php

namespace App\Actions\Tagihan;

use App\Models\Pendaftaran;
use App\Models\PeriodeAkademik;
use App\Models\PesertaDidik;
use App\Models\RekeningInstitusi;
use App\Models\Tagihan;
use App\Services\AuditLogger;
use App\Services\KodeUnikGenerator;
use App\Services\Notifier;
use App\Services\Pengaturan;
use App\Services\Penghitung;
use App\Services\TarifResolver;
use App\States\Tagihan\BelumBayar;
use App\Support\Ui\Format;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * FR-26, FR-27, FR-28, BR-25–BR-35, BR-71: terbitkan tagihan formulir / semester ke-n.
 * - Idempoten: tagihan yang sudah ada dikembalikan, tidak dibuat dua kali (BR-33).
 * - Snapshot nominal dasar (BR-28) dan rekening tujuan (BR-71).
 * - Tarif atau rekening aktif belum ada → tagihan tidak terbit + peringatan ke admin terkait (BR-34, BR-71).
 */
class TerbitkanTagihan
{
    public const PERCOBAAN_KODE_UNIK = 5;

    public function __construct(
        private TarifResolver $tarif,
        private KodeUnikGenerator $kodeUnik,
        private Penghitung $penghitung,
        private Pengaturan $pengaturan,
        private AuditLogger $audit,
        private Notifier $notifier,
    ) {}

    public function formulir(Pendaftaran $p): ?Tagihan
    {
        return DB::transaction(function () use ($p) {
            $ada = Tagihan::berlaku()->where('pendaftaran_id', $p->id)->where('jenis', 'formulir')->first();

            return $ada ?? $this->terbitkan($p, 'formulir', null, null);
        });
    }

    public function semester(PesertaDidik $pd, int $semesterKe): ?Tagihan
    {
        return DB::transaction(function () use ($pd, $semesterKe) {
            $ada = Tagihan::where('peserta_didik_id', $pd->id)->where('semester_ke', $semesterKe)->first(); // BR-33 (UNIQUE)

            return $ada ?? $this->terbitkan($pd->pendaftaran, 'semester', $semesterKe, $pd);
        });
    }

    private function terbitkan(Pendaftaran $p, string $jenis, ?int $semesterKe, ?PesertaDidik $pd): ?Tagihan
    {
        $peminatan = $p->peminatanPeriode->peminatan;
        $label = $jenis === 'formulir' ? 'formulir' : "Semester {$semesterKe}";

        $tarif = $this->tarif->untuk($peminatan->id, $jenis, $semesterKe);
        if (! $tarif) {
            $this->notifier->kirimKePeran(['admin_track', 'super_admin'], 'peringatan_tarif', "Tarif {$label} belum diatur",
                "Tagihan {$label} untuk {$p->nomor_pendaftaran} ({$peminatan->nama}) tidak diterbitkan karena tarifnya belum diatur (BR-34).");

            return null;
        }

        $rekening = RekeningInstitusi::where('jenis_tagihan', $jenis)->where('status', 'aktif')->first();
        if (! $rekening) {
            $this->notifier->kirimKePeran(['admin_keuangan', 'super_admin'], 'peringatan_rekening', "Rekening {$jenis} aktif belum ada",
                "Tagihan {$label} untuk {$p->nomor_pendaftaran} tidak diterbitkan karena belum ada rekening institusi aktif untuk tagihan {$jenis} (BR-71).",
                route('admin.rekening.index'));

            return null;
        }

        [$periodeAkademik, $jatuhTempo] = $jenis === 'semester' ? $this->jatuhTempoSemester() : [null, null];
        $tahun = Format::wib(now())->year;

        $data = [
            'kode_referensi' => sprintf('TG-%d-%06d', $tahun, $this->penghitung->berikutnya("urut_tagihan_{$tahun}", "Penghitung kode referensi tagihan {$tahun} (BR-31)")),
            'user_id' => $p->user_id,
            'pendaftaran_id' => $p->id,
            'peserta_didik_id' => $pd?->id,
            'periode_akademik_id' => $periodeAkademik?->id,
            'tarif_id' => $tarif->id,
            'rekening_institusi_id' => $rekening->id,
            'rekening_bank' => $rekening->nama_bank,
            'rekening_nomor' => $rekening->nomor_rekening,
            'rekening_atas_nama' => $rekening->atas_nama,
            'jenis' => $jenis,
            'semester_ke' => $semesterKe,
            'nominal_dasar' => $tarif->nominal,
            'jatuh_tempo' => $jatuhTempo,
            'status' => BelumBayar::class,
        ];

        // BR-29: kode unik acak; tabrakan bersamaan ditolak UNIQUE kunci_kode_unik → coba kode lain.
        for ($i = 1; ; $i++) {
            $kode = $this->kodeUnik->untuk($tarif->nominal);
            try {
                $tagihan = Tagihan::create($data + ['kode_unik' => $kode, 'nominal_total' => $tarif->nominal + $kode]);
                break;
            } catch (UniqueConstraintViolationException $e) {
                if ($i >= self::PERCOBAAN_KODE_UNIK || ! str_contains($e->getMessage(), 'kunci_kode_unik')) {
                    throw $e;
                }
            }
        }

        $this->audit->catat('terbitkan_tagihan', 'tagihan', $tagihan->id,
            sesudah: ['kode_referensi' => $tagihan->kode_referensi, 'jenis' => $jenis, 'semester_ke' => $semesterKe, 'nominal_total' => $tagihan->nominal_total]);
        $this->notifier->kirim($p->user, 'tagihan', "Tagihan {$tagihan->labelJenis()} terbit",
            "Tagihan {$tagihan->kode_referensi} sebesar ".Format::rupiah($tagihan->nominal_total).' sudah terbit. Transfer tepat sesuai nominal (termasuk kode unik).',
            route('tagihan.show', $tagihan));

        if ($jenis === 'semester' && $jatuhTempo === null) { // FR-57
            $this->notifier->kirimKePeran(['super_admin'], 'tagihan_tanpa_jatuh_tempo', 'Tagihan semester tanpa jatuh tempo',
                "Tagihan {$tagihan->kode_referensi} ({$tagihan->labelJenis()}) terbit tanpa jatuh tempo. Tetapkan jatuh tempo secara manual.",
                route('admin.tagihan.tanpa-jatuh-tempo'));
        }

        return $tagihan;
    }

    /**
     * BR-35: jatuh tempo = H-n sebelum periode akademik berikutnya mulai; bila sudah lewat (atau belum ada periode) → NULL, diatur Super Admin.
     *
     * @return array{0: PeriodeAkademik|null, 1: string|null}
     */
    private function jatuhTempoSemester(): array
    {
        $hariIni = Format::wib(now())->toDateString();
        $periode = PeriodeAkademik::where('tanggal_mulai', '>', $hariIni)->orderBy('tanggal_mulai')->first();
        if (! $periode) {
            return [null, null];
        }

        $jatuh = $periode->tanggal_mulai->copy()->subDays($this->pengaturan->int('jatuh_tempo_semester_hari_sebelum'))->toDateString();

        return [$periode, $jatuh > $hariIni ? $jatuh : null];
    }
}
