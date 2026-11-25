<?php

namespace App\Actions\Tagihan;

use App\Models\Pendaftaran;
use App\Models\PesertaDidik;
use App\Services\AuditLogger;
use App\Services\Notifier;
use App\Services\Pengaturan;
use App\Services\Penghitung;
use App\States\Pendaftaran\NpdTerbit;
use App\States\PesertaDidik\BelumAktif;
use App\Support\Ui\Format;
use Illuminate\Support\Facades\DB;

/**
 * FR-34, BR-41, BR-42 (A-72): atomik bersama keputusan Lunas — NPD unik (penghitung FOR UPDATE + UNIQUE),
 * pendaftaran NPD Terbit, peserta didik Belum Aktif Semester 1, tagihan Semester 1, notifikasi berisi NPD.
 * Role akun tetap `peserta`; label "Peserta Didik" berasal dari adanya baris peserta_didiks (A-72).
 */
class TerbitkanNpd
{
    public function __construct(
        private Penghitung $penghitung,
        private Pengaturan $pengaturan,
        private TerbitkanTagihan $tagihan,
        private AuditLogger $audit,
        private Notifier $notifier,
    ) {}

    public function __invoke(Pendaftaran $pendaftaran): PesertaDidik
    {
        return DB::transaction(function () use ($pendaftaran) {
            $p = Pendaftaran::whereKey($pendaftaran->id)->lockForUpdate()->with('peminatanPeriode.peminatan')->firstOrFail();
            $peminatan = $p->peminatanPeriode->peminatan;
            $tahun = Format::wib(now())->year;
            $urut = $this->penghitung->berikutnya("urut_npd_{$tahun}", "Penghitung NPD {$tahun} (BR-41)");
            $npd = strtr((string) $this->pengaturan->get('format_npd'), [
                '{KODE_PEMINATAN}' => $peminatan->kode, '{TAHUN}' => (string) $tahun, '{URUT4}' => sprintf('%04d', $urut),
            ]);

            $pd = PesertaDidik::create([
                'user_id' => $p->user_id,
                'pendaftaran_id' => $p->id,
                'npd' => $npd,
                'semester_berjalan' => 1,
                'durasi_semester' => $p->durasi_semester_snapshot ?? $peminatan->durasi_semester, // BR-43
                'status' => BelumAktif::class,
                'npd_terbit_pada' => now(),
            ]);
            $p->status->transitionTo(NpdTerbit::class);

            $this->audit->catat('terbitkan_npd', 'pendaftaran', $p->id, sesudah: ['npd' => $npd, 'peserta_didik_id' => $pd->id]);
            $this->notifier->kirim($p->user, 'status_pendaftaran', "NPD terbit · {$npd}",
                "Selamat, pembayaran formulir {$p->nomor_pendaftaran} disahkan. Nomor Peserta Didik Anda: {$npd}. Tagihan Semester 1 dapat dilihat di menu Tagihan.",
                route('status'));

            $this->tagihan->semester($pd, 1); // BR-32(b); bila tarif/rekening belum ada → peringatan ke admin, NPD tetap terbit

            return $pd;
        });
    }
}
