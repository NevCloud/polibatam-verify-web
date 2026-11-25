<?php

use App\Support\Database\BerkasSql;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * 6 trigger integritas, persis database/paket-db/sql/03_trigger.sql: satu DB::unprepared per trigger,
 * tanpa DELIMITER. Butuh log_bin_trust_function_creators = ON (paket-db README, Peringatan 4).
 */
return new class extends Migration
{
    public function up(): void
    {
        BerkasSql::jalankan('03_trigger.sql', $this->getConnection());
    }

    public function down(): void
    {
        foreach (BerkasSql::statement('03_trigger.sql') as $sql) {
            if (str_starts_with($sql, 'DROP TRIGGER')) {
                DB::connection($this->getConnection())->unprepared($sql);
            }
        }
    }
};
