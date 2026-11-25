<?php

namespace App\Actions\Periode;

use App\Events\PeriodeDitutup;
use App\Models\Pendaftaran;
use App\Models\PeriodePendaftaran;
use App\Services\AuditLogger;
use App\Services\Notifier;
use App\States\Pendaftaran\Dibatalkan;
use Illuminate\Support\Facades\DB;

/**
 * FR-25, BR-65 (`periode:tutup`, tiap 15 menit): periode dibuka yang sudah lewat tanggal selesai → ditutup;
 * Draf pada periode itu → Dibatalkan (formulir terkunci, tanpa tagihan/refund); lalu PeriodeDitutup (arsip P11).
 * Penandaan kuota minimum (BR-23) dihitung di evaluasi kuota FR-39, bukan disimpan di sini.
 * Idempoten (NFR-25): hanya periode berstatus `dibuka`, diperiksa ulang di dalam kunci baris.
 */
class TutupPeriode
{
    public const ALASAN_DRAF = 'Periode pendaftaran ditutup sebelum formulir diajukan';

    public function __construct(private AuditLogger $audit, private Notifier $notifier) {}

    /** @return int jumlah periode yang ditutup */
    public function __invoke(): int
    {
        $ids = PeriodePendaftaran::where('status', 'dibuka')->where('tanggal_selesai', '<=', now())->pluck('id');

        return $ids->sum(fn (int $id) => DB::transaction(function () use ($id) {
            $periode = PeriodePendaftaran::whereKey($id)->lockForUpdate()->first();
            if (! $periode || $periode->status !== 'dibuka') {
                return 0;
            }

            $periode->update(['status' => 'ditutup', 'ditutup_pada' => now()]);

            $draf = Pendaftaran::where('status', 'draf')
                ->whereHas('peminatanPeriode', fn ($q) => $q->where('periode_pendaftaran_id', $periode->id))
                ->lockForUpdate()->get();
            foreach ($draf as $p) {
                $p->forceFill(['alasan_batal' => self::ALASAN_DRAF]);
                $p->status->transitionTo(Dibatalkan::class);
                $this->notifier->kirim($p->user, 'status_pendaftaran', 'Draf pendaftaran dibatalkan',
                    "Draf {$p->nomor_pendaftaran} dibatalkan karena periode pendaftaran {$periode->nama} ditutup sebelum formulir diajukan.",
                    route('status'));
            }

            $this->audit->catat('tutup_periode', 'periode_pendaftaran', $periode->id,
                sebelum: ['status' => 'dibuka'], sesudah: ['status' => 'ditutup', 'draf_dibatalkan' => $draf->count()]);

            PeriodeDitutup::dispatch($periode);

            return 1;
        }));
    }
}
