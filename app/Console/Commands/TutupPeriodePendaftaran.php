<?php

namespace App\Console\Commands;

use App\Actions\Periode\TutupPeriode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/** FR-25, BR-65: terjadwal tiap 15 menit (routes/console.php). Setiap eksekusi tercatat di log (NFR-25). */
class TutupPeriodePendaftaran extends Command
{
    protected $signature = 'periode:tutup';

    protected $description = 'Menutup periode pendaftaran yang sudah berakhir dan membatalkan Draf di dalamnya';

    public function handle(TutupPeriode $tutup): int
    {
        $jumlah = $tutup();
        Log::info('periode:tutup selesai', ['periode_ditutup' => $jumlah]);
        $this->info("{$jumlah} periode ditutup.");

        return self::SUCCESS;
    }
}
