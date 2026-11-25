<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * Penghitung berurutan di `pengaturans` (nilai JSON integer) yang dikunci FOR UPDATE:
 * nomor pendaftaran (BR-68), kode referensi tagihan (BR-31), NPD (BR-41). UNIQUE di tabel tujuan = pengaman terakhir.
 * Wajib dipanggil di dalam transaksi yang sama dengan INSERT pemakainya (kunci dilepas saat commit).
 */
class Penghitung
{
    public function berikutnya(string $kunci, string $keterangan): int
    {
        if (DB::transactionLevel() === 0) {
            throw new LogicException("Penghitung [{$kunci}] harus dipanggil di dalam DB::transaction.");
        }

        // Baris belum ada (mis. tahun baru): dibuat bernilai 0 lebih dulu.
        DB::table('pengaturans')->insertOrIgnore([
            'kunci' => $kunci, 'nilai' => '0', 'keterangan' => $keterangan, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $urut = 1 + (int) json_decode((string) DB::table('pengaturans')->where('kunci', $kunci)->lockForUpdate()->value('nilai'));
        DB::table('pengaturans')->where('kunci', $kunci)->update(['nilai' => (string) $urut, 'updated_at' => now()]);

        return $urut;
    }
}
