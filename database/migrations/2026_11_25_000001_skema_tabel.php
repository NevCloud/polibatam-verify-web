<?php

use App\Support\Database\BerkasSql;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 35 tabel domain + framework, persis database/paket-db/sql/01_skema_tabel.sql.
 * Jalankan: php artisan migrate --database=mysql_migrator (AE-13). GRANT tetap di 05 oleh pv_dba.
 */
return new class extends Migration
{
    public function up(): void
    {
        BerkasSql::jalankan('01_skema_tabel.sql', $this->getConnection());
    }

    /** Hanya untuk reset lokal; di produksi pv_migrator terkunci di luar jendela deploy. */
    public function down(): void
    {
        $db = DB::connection($this->getConnection());
        $db->statement('SET FOREIGN_KEY_CHECKS = 0');

        foreach (array_reverse(BerkasSql::statement('01_skema_tabel.sql')) as $sql) {
            if (preg_match('/^CREATE\s+TABLE\s+`?(\w+)`?/i', $sql, $m)) {
                $db->statement("DROP TABLE IF EXISTS `{$m[1]}`");
            }
        }

        $db->statement('SET FOREIGN_KEY_CHECKS = 1');
    }
};
