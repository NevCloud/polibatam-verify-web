<?php

namespace App\Support\Database;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Menjalankan berkas paket SQL (`database/paket-db/sql/*.sql`) dari migration/seeder,
 * sehingga paket SQL tetap satu-satunya sumber kebenaran skema (PRD §3, §6 P00).
 *
 * - Dipecah per statement; blok `DELIMITER $$ … $$` (03_trigger) menjadi satu statement per trigger.
 * - Dilewati: `USE …` (koneksi sudah menentukan DB), SELECT/SHOW bukti di akhir berkas,
 *   dan `CREATE TABLE migrations` (sudah dibuat oleh migrator Laravel).
 */
final class BerkasSql
{
    public static function jalankan(string $nama, ?string $koneksi = null): int
    {
        $db = DB::connection($koneksi);
        $statement = self::statement($nama);

        foreach ($statement as $sql) {
            $db->unprepared($sql);
        }

        return count($statement);
    }

    /** @return list<string> */
    public static function statement(string $nama): array
    {
        $jalur = base_path("database/paket-db/sql/{$nama}");

        if (! is_file($jalur)) {
            throw new RuntimeException("Berkas paket SQL tidak ditemukan: {$jalur}");
        }

        $hasil = [];
        $pembatas = ';';
        $buffer = '';

        foreach (preg_split('/\R/', (string) file_get_contents($jalur)) ?: [] as $baris) {
            $rapi = trim($baris);

            if ($buffer === '' && ($rapi === '' || str_starts_with($rapi, '--'))) {
                continue;
            }

            if (preg_match('/^DELIMITER\s+(\S+)$/i', $rapi, $m)) {
                $pembatas = $m[1];

                continue;
            }

            // Buang komentar ujung baris sebelum mencari pembatas, agar `;` di komentar tidak memotong statement.
            $kode = rtrim(preg_replace('/\s--\s.*$/', '', $baris) ?? $baris);
            $buffer .= $kode."\n";

            if (str_ends_with(rtrim($kode), $pembatas)) {
                $sql = trim(substr(rtrim($buffer), 0, -strlen($pembatas)));
                $buffer = '';

                if (! self::dilewati($sql)) {
                    $hasil[] = $sql;
                }
            }
        }

        if (trim($buffer) !== '') {
            throw new RuntimeException("Statement tanpa pembatas di akhir {$nama}.");
        }

        return $hasil;
    }

    private static function dilewati(string $sql): bool
    {
        return preg_match('/^(USE\s|SELECT\s|SHOW\s|CREATE\s+TABLE\s+migrations\b)/i', $sql) === 1;
    }
}
