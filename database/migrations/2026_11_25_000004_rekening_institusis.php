<?php

use App\Support\Database\BerkasSql;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tabel rekening_institusis, persis database/paket-db/sql/01b_rekening_institusi.sql (BR-70, SRS §4.7; T-16).
 * GRANT pv_app ada di 05_grants_dan_kunci.sql.
 */
return new class extends Migration
{
    public function up(): void
    {
        BerkasSql::jalankan('01b_rekening_institusi.sql', $this->getConnection());
    }

    public function down(): void
    {
        DB::connection($this->getConnection())->statement('DROP TABLE IF EXISTS `rekening_institusis`');
    }
};
