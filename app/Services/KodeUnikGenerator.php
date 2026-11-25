<?php

namespace App\Services;

use App\Models\Tagihan;
use RuntimeException;

/**
 * BR-29: kode unik 3 digit acak (rentang dari pengaturan `kode_unik_rentang`), tidak kembar dengan tagihan TERBUKA
 * bernominal dasar sama. Penerbitan bersamaan dijaga UNIQUE `kunci_kode_unik` di tabel; TerbitkanTagihan mencoba ulang.
 */
class KodeUnikGenerator
{
    public function __construct(private Pengaturan $pengaturan) {}

    public function untuk(int $nominalDasar): int
    {
        $rentang = $this->pengaturan->get('kode_unik_rentang');
        $dipakai = Tagihan::where('nominal_dasar', $nominalDasar)->whereIn('status', Tagihan::TERBUKA)->pluck('kode_unik')->all();
        $tersedia = array_values(array_diff(range((int) $rentang['min'], (int) $rentang['maks']), $dipakai));

        if ($tersedia === []) {
            throw new RuntimeException("Kode unik habis untuk nominal dasar {$nominalDasar}: semua kode sedang dipakai tagihan terbuka.");
        }

        return $tersedia[random_int(0, count($tersedia) - 1)]; // CSPRNG
    }
}
