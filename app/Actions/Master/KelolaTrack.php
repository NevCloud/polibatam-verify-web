<?php

namespace App\Actions\Master;

use App\Models\Track;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** HW-33 (FR-45, BR-52, BR-53, BR-58): tambah/ubah track, aktif/nonaktif, hapus hanya bila belum punya peminatan. */
class KelolaTrack
{
    public const PESAN_HAPUS = 'Track ini sudah memiliki peminatan sehingga tidak dapat dihapus. Nonaktifkan saja.';

    public function __construct(private AuditLogger $audit) {}

    /** @param  array{kode: string, nama: string}  $data  kode unik sudah divalidasi (BR-52) */
    public function simpan(array $data, ?Track $track, User $admin): Track
    {
        $data = ['kode' => mb_strtoupper(trim($data['kode'])), 'nama' => trim($data['nama'])];

        return DB::transaction(function () use ($data, $track, $admin) {
            $sebelum = $track?->only(['kode', 'nama']);
            $track ??= new Track(['is_active' => true]);
            $track->fill($data)->save();
            $this->audit->catat($sebelum === null ? 'tambah_track' : 'ubah_track', 'track', $track->id, sebelum: $sebelum, sesudah: $data, pelaku: $admin);

            return $track;
        });
    }

    public function ubahStatus(Track $track, bool $aktif, User $admin): void
    {
        DB::transaction(function () use ($track, $aktif, $admin) {
            $track->update(['is_active' => $aktif]);
            // BR-53: peminatan di track nonaktif ikut hilang dari katalog (scope PeminatanPeriode::terbuka), data peserta tetap.
            $this->audit->catat($aktif ? 'aktifkan_track' : 'nonaktifkan_track', 'track', $track->id, pelaku: $admin);
        });
    }

    public function hapus(Track $track, User $admin): void
    {
        DB::transaction(function () use ($track, $admin) {
            if ($track->peminatans()->exists()) {
                throw ValidationException::withMessages(['track' => self::PESAN_HAPUS]);
            }
            $this->audit->catat('hapus_track', 'track', $track->id, sebelum: $track->only(['kode', 'nama']), pelaku: $admin);
            $track->delete();
        });
    }
}
