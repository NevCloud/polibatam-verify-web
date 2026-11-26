<?php

namespace App\Actions\Master;

use App\Models\Peminatan;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * HW-34 (FR-46, BR-43, BR-52, BR-53, BR-58): tambah/ubah peminatan, aktif/nonaktif, hapus hanya bila belum pernah dipakai.
 * Durasi baru hanya berlaku untuk pendaftar berikutnya karena pendaftaran menyimpan `durasi_semester_snapshot` (AC-21.5).
 */
class KelolaPeminatan
{
    public const PESAN_HAPUS = 'Peminatan ini sudah dipakai pendaftaran atau sudah memiliki tarif sehingga tidak dapat dihapus. Nonaktifkan saja.';

    public const PESAN_KODE = 'Kode peminatan tidak dapat diubah karena sudah dipakai pendaftaran (kode dipakai pada format NPD).';

    public function __construct(private AuditLogger $audit) {}

    /** @param  array{track_id: int, kode: string, nama: string, durasi_semester: int}  $data  sudah divalidasi (BR-52) */
    public function simpan(array $data, ?Peminatan $peminatan, User $admin): Peminatan
    {
        $data['kode'] = mb_strtoupper(trim($data['kode']));
        $data['nama'] = trim($data['nama']);

        return DB::transaction(function () use ($data, $peminatan, $admin) {
            if ($peminatan && $peminatan->kode !== $data['kode'] && $peminatan->pernahDipakai()) {
                throw ValidationException::withMessages(['kode' => self::PESAN_KODE]);
            }
            $sebelum = $peminatan?->only(['track_id', 'kode', 'nama', 'durasi_semester']);
            $peminatan ??= new Peminatan(['is_active' => true]);
            $peminatan->fill($data)->save();
            $this->audit->catat($sebelum === null ? 'tambah_peminatan' : 'ubah_peminatan', 'peminatan', $peminatan->id,
                sebelum: $sebelum, sesudah: $data, pelaku: $admin);

            return $peminatan;
        });
    }

    public function ubahStatus(Peminatan $peminatan, bool $aktif, User $admin): void
    {
        DB::transaction(function () use ($peminatan, $aktif, $admin) {
            $peminatan->update(['is_active' => $aktif]); // nonaktif: hilang dari katalog, peserta lama tetap berjalan (AC-21.3)
            $this->audit->catat($aktif ? 'aktifkan_peminatan' : 'nonaktifkan_peminatan', 'peminatan', $peminatan->id, pelaku: $admin);
        });
    }

    public function hapus(Peminatan $peminatan, User $admin): void
    {
        DB::transaction(function () use ($peminatan, $admin) {
            // Tarif tidak bisa dihapus (versi wajib disimpan, BR-26), jadi peminatan bertarif juga tidak bisa dihapus.
            if ($peminatan->pernahDipakai() || $peminatan->tarifs()->exists()) {
                throw ValidationException::withMessages(['peminatan' => self::PESAN_HAPUS]);
            }
            $this->audit->catat('hapus_peminatan', 'peminatan', $peminatan->id, sebelum: $peminatan->only(['kode', 'nama']), pelaku: $admin);
            $peminatan->peminatanPeriodes()->delete(); // kuota periode tanpa pendaftar
            $peminatan->delete();
        });
    }
}
