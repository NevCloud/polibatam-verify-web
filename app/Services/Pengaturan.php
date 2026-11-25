<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Baca parameter bisnis dari tabel `pengaturans` (AE-14, NFR-40), bukan dari angka di kode.
 * Kolom (ERD §3.19): kunci VARCHAR(80) UNIQUE, nilai JSON.
 * Hanya baca. Penulisan (UI Super Admin) di P12; penghitung nomor pendaftaran memakai FOR UPDATE di P03.
 */
class Pengaturan
{
    /** @var array<string, mixed>|null */
    private ?array $muat = null;

    /** Nilai parameter. Kunci yang tidak ada adalah galat konfigurasi, bukan alasan memakai angka bawaan. */
    public function get(string $kunci): mixed
    {
        $semua = $this->semua();

        if (! array_key_exists($kunci, $semua)) {
            throw new RuntimeException("Pengaturan [{$kunci}] belum ada di tabel pengaturans.");
        }

        return $semua[$kunci];
    }

    public function int(string $kunci): int
    {
        return (int) $this->get($kunci);
    }

    /** Muat ulang setelah Super Admin mengubah pengaturan. */
    public function segarkan(): void
    {
        $this->muat = null;
    }

    /** @return array<string, mixed> */
    private function semua(): array
    {
        // shortcut: cache per request saja; pindah ke Cache store bila beban baca pengaturan terukur tinggi (PN)
        return $this->muat ??= DB::table('pengaturans')
            ->pluck('nilai', 'kunci')
            ->map(fn (string $json) => json_decode($json, true, flags: JSON_THROW_ON_ERROR))
            ->all();
    }
}
