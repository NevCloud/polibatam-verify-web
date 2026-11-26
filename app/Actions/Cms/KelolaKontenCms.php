<?php

namespace App\Actions\Cms;

use App\Models\KontenCms;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * HW-46 (FR-10, BR-55, BR-58, AC-29.1/29.2): Super Admin menyimpan draf, mempratinjau, lalu menerbitkan per bagian.
 * Tidak ada DELETE (pv_app tanpa DELETE pada konten_cms): baris draf & terbit diperbarui di tempat.
 */
class KelolaKontenCms
{
    public function __construct(private AuditLogger $audit) {}

    public function simpanDraf(string $bagian, string $judul, string $isi, User $admin): KontenCms
    {
        $this->pastikanBagian($bagian);

        return DB::transaction(function () use ($bagian, $judul, $isi, $admin) {
            $draf = KontenCms::firstOrNew(['bagian' => $bagian, 'status' => 'draf']);
            $draf->fill(['judul' => trim($judul), 'isi' => trim($isi), 'diubah_oleh' => $admin->id])->save();
            $this->audit->catat('simpan_konten_cms', 'konten_cms', $draf->id, sesudah: ['bagian' => $bagian, 'judul' => $draf->judul], pelaku: $admin);

            return $draf;
        });
    }

    public function terbitkan(string $bagian, User $admin): KontenCms
    {
        $this->pastikanBagian($bagian);

        return DB::transaction(function () use ($bagian, $admin) {
            $draf = KontenCms::where('bagian', $bagian)->where('status', 'draf')->lockForUpdate()->first()
                ?? throw ValidationException::withMessages(['bagian' => 'Simpan draf terlebih dahulu sebelum menerbitkan.']);
            $terbit = KontenCms::firstOrNew(['bagian' => $bagian, 'status' => 'terbit']);
            $sebelum = $terbit->exists ? ['judul' => $terbit->judul, 'isi' => $terbit->isi] : null;
            $terbit->fill(['judul' => $draf->judul, 'isi' => $draf->isi, 'diubah_oleh' => $admin->id, 'diterbitkan_pada' => now()])->save();
            $this->audit->catat('terbitkan_konten_cms', 'konten_cms', $terbit->id, sebelum: $sebelum,
                sesudah: ['bagian' => $bagian, 'judul' => $terbit->judul, 'isi' => $terbit->isi], pelaku: $admin);

            return $terbit;
        });
    }

    private function pastikanBagian(string $bagian): void
    {
        if (! array_key_exists($bagian, KontenCms::BAGIAN)) {
            throw ValidationException::withMessages(['bagian' => 'Bagian konten tidak dikenal.']);
        }
    }
}
