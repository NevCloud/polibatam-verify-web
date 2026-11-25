<?php

namespace App\Console\Commands;

use App\Services\AuditLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Periksa hash chain audit_logs (NFR-31, TST-36): setiap entri harus menunjuk hash entri sebelumnya
 * dan hash-nya harus sama dengan hasil hitung ulang. Terjadwal harian 02.00 WIB (routes/console.php).
 */
class VerifikasiHashAudit extends Command
{
    protected $signature = 'audit:verifikasi-hash';

    protected $description = 'Memeriksa keutuhan hash chain audit log';

    public function handle(): int
    {
        $sebelumnya = AuditLogger::PREV_AWAL;
        $jumlah = 0;
        $putus = [];

        foreach (DB::table('audit_logs')->orderBy('id')->lazyById(500) as $b) {
            $baris = (array) $b;
            $baris['nilai_sebelum'] = $baris['nilai_sebelum'] === null ? null : json_decode($baris['nilai_sebelum'], true);
            $baris['nilai_sesudah'] = $baris['nilai_sesudah'] === null ? null : json_decode($baris['nilai_sesudah'], true);

            if ($baris['prev_hash'] !== $sebelumnya) {
                $putus[] = "id {$b->id}: prev_hash tidak menunjuk entri sebelumnya (entri dihapus/disisipkan?)";
            } elseif (AuditLogger::hitungHash($baris) !== $b->hash) {
                $putus[] = "id {$b->id}: isi entri tidak cocok dengan hash (entri diubah?)";
            }

            $sebelumnya = $b->hash;
            $jumlah++;
        }

        if ($putus === []) {
            $this->info("Rantai audit utuh: {$jumlah} entri diperiksa.");

            return self::SUCCESS;
        }

        $this->error('Rantai putus pada '.$putus[0]);
        foreach (array_slice($putus, 1, 9) as $p) {
            $this->line(' - '.$p);
        }
        Log::critical('Hash chain audit log putus.', ['jumlah_putus' => count($putus), 'pertama' => $putus[0]]);

        return self::FAILURE;
    }
}
