<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\Pemberitahuan;

/**
 * Pintu tunggal notifikasi (FR-53 inti, BR-56, NFR-26):
 * in-app ditulis langsung (tetap ada walau email gagal), email lewat antrean dengan 3 percobaan.
 */
class Notifier
{
    public function kirim(User $penerima, string $jenis, string $judul, string $isi, ?string $url = null): void
    {
        $penerima->notifyNow(new Pemberitahuan('database', $jenis, $judul, $isi, $url));
        $penerima->notify(new Pemberitahuan('mail', $jenis, $judul, $isi, $url));
    }

    /**
     * Peringatan ke semua akun aktif dengan salah satu role (mis. BR-34, BR-71: tarif/rekening belum ada).
     *
     * @param  list<string>  $role
     */
    public function kirimKePeran(array $role, string $jenis, string $judul, string $isi, ?string $url = null): void
    {
        User::role($role)->where('is_active', true)->get()->each(fn (User $u) => $this->kirim($u, $jenis, $judul, $isi, $url));
    }
}
