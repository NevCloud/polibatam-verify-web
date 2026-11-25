<?php

use App\Support\Database\BerkasSql;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 4 VIEW laporan untuk pv_report, persis database/paket-db/sql/02_view.sql (DEFINER = pv_migrator).
 */
return new class extends Migration
{
    public function up(): void
    {
        BerkasSql::jalankan('02_view.sql', $this->getConnection());
    }

    public function down(): void
    {
        foreach (['v_antrean_verifikasi_berkas', 'v_rekap_pembayaran', 'v_status_refund', 'v_data_pendaftaran'] as $view) {
            DB::connection($this->getConnection())->statement("DROP VIEW IF EXISTS `{$view}`");
        }
    }
};
