<?php

namespace App\Services;

use App\Support\Ui\Format;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * BR-68: nomor `PV-{TAHUN}-{6 digit}`. Penghitung per tahun di `pengaturans` (`urut_nomor_pendaftaran_{TAHUN}`)
 * dikunci FOR UPDATE, sehingga simpan Draf bersamaan tetap mendapat nomor berbeda; UNIQUE di tabel = pengaman terakhir.
 * Wajib dipanggil di dalam transaksi yang sama dengan INSERT pendaftaran (kunci dilepas saat commit).
 */
class NomorPendaftaranGenerator
{
    public function berikutnya(): string
    {
        if (DB::transactionLevel() === 0) {
            throw new LogicException('NomorPendaftaranGenerator harus dipanggil di dalam DB::transaction.');
        }

        $tahun = Format::wib(now())->year;
        $kunci = "urut_nomor_pendaftaran_{$tahun}";

        // Tahun baru: baris penghitung belum ada (seed 04 hanya membuat tahun berjalan).
        DB::table('pengaturans')->insertOrIgnore([
            'kunci' => $kunci, 'nilai' => '0', 'keterangan' => "Penghitung nomor pendaftaran {$tahun} (BR-68)",
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $urut = 1 + (int) json_decode((string) DB::table('pengaturans')->where('kunci', $kunci)->lockForUpdate()->value('nilai'));
        DB::table('pengaturans')->where('kunci', $kunci)->update(['nilai' => (string) $urut, 'updated_at' => now()]);

        return sprintf('PV-%d-%06d', $tahun, $urut);
    }
}
