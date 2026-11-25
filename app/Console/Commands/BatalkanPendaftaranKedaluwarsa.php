<?php

namespace App\Console\Commands;

use App\Actions\Pendaftaran\BatalkanKedaluwarsa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/** BR-21, FR-24: terjadwal tiap jam (routes/console.php). Setiap eksekusi tercatat di log (NFR-25). */
class BatalkanPendaftaranKedaluwarsa extends Command
{
    protected $signature = 'pendaftaran:batalkan-kedaluwarsa';

    protected $description = 'Membatalkan pendaftaran Masuk Kuota yang tagihan formulirnya tidak dilunasi dalam batas waktu';

    public function handle(BatalkanKedaluwarsa $batalkan): int
    {
        $jumlah = $batalkan();
        Log::info('pendaftaran:batalkan-kedaluwarsa selesai', ['dibatalkan' => $jumlah]);
        $this->info("{$jumlah} pendaftaran dibatalkan.");

        return self::SUCCESS;
    }
}
