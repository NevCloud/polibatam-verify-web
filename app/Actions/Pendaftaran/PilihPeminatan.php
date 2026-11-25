<?php

namespace App\Actions\Pendaftaran;

use App\Models\PeminatanPeriode;
use App\Models\Pendaftaran;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\NomorPendaftaranGenerator;
use App\States\Pendaftaran\Draf;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * HW-14 (FR-11, BR-07, BR-08, BR-09, BR-68): simpan pertama membuat Draf + nomor pendaftaran.
 * Selama masih Draf, pilihan boleh diganti (nomor tetap); setelah Diajukan terkunci (BR-09).
 */
class PilihPeminatan
{
    public function __construct(private NomorPendaftaranGenerator $nomor, private AuditLogger $audit) {}

    public function __invoke(User $user, int $peminatanPeriodeId): Pendaftaran
    {
        return DB::transaction(function () use ($user, $peminatanPeriodeId) {
            $pilihan = PeminatanPeriode::terbuka()->find($peminatanPeriodeId)
                ?? throw ValidationException::withMessages([
                    'peminatan_periode_id' => 'Peminatan ini tidak tersedia pada periode pendaftaran yang sedang dibuka. Pilih peminatan lain.',
                ]);

            // Serialkan permintaan dari akun yang sama (dua tab menekan Simpan bersamaan → tetap satu pendaftaran).
            User::whereKey($user->id)->lockForUpdate()->first();

            $aktif = Pendaftaran::where('user_id', $user->id)->belumAkhir()
                ->whereHas('peminatanPeriode', fn ($q) => $q->where('periode_pendaftaran_id', $pilihan->periode_pendaftaran_id))
                ->first();

            if ($aktif && ! $aktif->adalahDraf()) {
                throw ValidationException::withMessages([
                    'peminatan_periode_id' => "Anda sudah memiliki pendaftaran aktif ({$aktif->nomor_pendaftaran}) pada periode ini. Pilihan track dan peminatan tidak dapat diubah setelah formulir diajukan.",
                ]);
            }

            if ($aktif) {
                $aktif->update(['peminatan_periode_id' => $pilihan->id]);

                return $aktif;
            }

            $pendaftaran = Pendaftaran::create([
                'nomor_pendaftaran' => $this->nomor->berikutnya(),
                'user_id' => $user->id,
                'peminatan_periode_id' => $pilihan->id,
                'status' => Draf::class,
                'nama_lengkap' => $user->name,
            ]);

            $this->audit->catat('buat_draf_pendaftaran', 'pendaftaran', $pendaftaran->id,
                sesudah: ['nomor_pendaftaran' => $pendaftaran->nomor_pendaftaran, 'peminatan_periode_id' => $pilihan->id], pelaku: $user);

            return $pendaftaran;
        });
    }
}
