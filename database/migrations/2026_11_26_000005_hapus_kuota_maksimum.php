<?php

use App\Support\Database\BerkasSql;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * K-42: hapus kuota maksimum, slot terpakai, dan nomor urut Daftar Tunggu, persis
 * database/paket-db/sql/01c_hapus_kuota_maksimum.sql; lalu VIEW laporan dibuat ulang (02_view.sql).
 */
return new class extends Migration
{
    public function up(): void
    {
        BerkasSql::jalankan('01c_hapus_kuota_maksimum.sql', $this->getConnection());
        BerkasSql::jalankan('02_view.sql', $this->getConnection());
    }

    public function down(): void
    {
        $db = DB::connection($this->getConnection());
        $db->statement('ALTER TABLE peminatan_periodes ADD COLUMN kuota_maks INT UNSIGNED NOT NULL DEFAULT 0 AFTER kuota_min, ADD COLUMN slot_terpakai INT UNSIGNED NOT NULL DEFAULT 0 AFTER kuota_maks');
        $db->statement('ALTER TABLE pendaftarans ADD COLUMN nomor_urut_tunggu INT UNSIGNED NULL AFTER waktu_masuk_kuota');
        $db->statement("ALTER TABLE refunds MODIFY sebab VARCHAR(40) NOT NULL COMMENT 'peminatan_dibatalkan / tidak_tertampung (BR-47)'");
    }
};
