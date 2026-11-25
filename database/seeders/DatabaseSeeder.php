<?php

namespace Database\Seeders;

use App\Support\Database\BerkasSql;
use Illuminate\Database\Seeder;

/**
 * Data awal wajib semua lingkungan, persis database/paket-db/sql/04_seed_data_awal.sql:
 * 6 role, 26 permission, pengaturan bisnis (NFR-40), kriteria checklist, contoh track & peminatan.
 * Jalankan: php artisan db:seed --database=mysql_migrator. Akun pengguna TIDAK dibuat di sini.
 * Data uji lokal: jalankan 06_seed_dummy_lokal.sql sebagai pv_dba (paket-db README §1 langkah 5).
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Koneksi = --database. Bukan idempoten (INSERT biasa, sama dengan 04): jalankan sekali pada DB baru.
        BerkasSql::jalankan('04_seed_data_awal.sql');
    }
}
