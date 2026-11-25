<?php

use App\Support\Database\BerkasSql;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * BR-71 snapshot rekening tujuan + BR-72 data pengiriman, persis database/paket-db/sql/01d_tagihan_snapshot_pengiriman.sql (T-16).
 */
return new class extends Migration
{
    public function up(): void
    {
        BerkasSql::jalankan('01d_tagihan_snapshot_pengiriman.sql', $this->getConnection());
    }

    public function down(): void
    {
        $db = DB::connection($this->getConnection());
        $db->statement('ALTER TABLE tagihans DROP FOREIGN KEY tagihans_rekening_foreign, DROP COLUMN rekening_institusi_id, DROP COLUMN rekening_bank, DROP COLUMN rekening_nomor, DROP COLUMN rekening_atas_nama');
        $db->statement('ALTER TABLE bukti_pembayarans DROP COLUMN tanggal_transfer, DROP COLUMN nominal_transfer, DROP COLUMN bank_pengirim, DROP COLUMN nama_pengirim');
    }
};
