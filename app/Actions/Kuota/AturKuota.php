<?php

namespace App\Actions\Kuota;

use App\Models\Peminatan;
use App\Models\PeminatanPeriode;
use App\Models\Pendaftaran;
use App\Models\PeriodePendaftaran;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * HW-36 (FR-48, BR-15, BR-20, BR-58, K-42): kuota minimum per peminatan per periode, ≥ 0, tanpa kuota maksimum.
 * Sebelum periode dimulai: Admin Track atau Super Admin. Setelah dimulai: hanya Super Admin dengan alasan wajib (AC-23.3).
 */
class AturKuota
{
    public const PESAN_TERKUNCI = 'Periode pendaftaran sudah dimulai: kuota minimum hanya dapat diubah Super Admin dengan alasan.';

    public const PESAN_ALASAN = 'Alasan wajib diisi (minimal 10 karakter) karena periode pendaftaran sudah dimulai.';

    public function __construct(private AuditLogger $audit) {}

    public static function sudahDimulai(PeriodePendaftaran $periode): bool
    {
        return now()->greaterThanOrEqualTo($periode->tanggal_mulai);
    }

    public static function bolehUbah(PeriodePendaftaran $periode, User $admin): bool
    {
        return $admin->hasRole('super_admin') || ($admin->hasRole('admin_track') && ! self::sudahDimulai($periode));
    }

    public function __invoke(PeriodePendaftaran $periode, Peminatan $peminatan, int $minimum, ?string $alasan, User $admin): ?PeminatanPeriode
    {
        if ($minimum < 0) {
            throw ValidationException::withMessages(['kuota_min' => 'Kuota minimum tidak boleh negatif.']); // BR-15
        }
        if (! self::bolehUbah($periode, $admin)) {
            throw ValidationException::withMessages(['kuota_min' => self::PESAN_TERKUNCI]);
        }
        $alasan = trim((string) $alasan) ?: null;
        if (self::sudahDimulai($periode) && mb_strlen((string) $alasan) < 10) {
            throw ValidationException::withMessages(['alasan' => self::PESAN_ALASAN]);
        }

        return DB::transaction(function () use ($periode, $peminatan, $minimum, $alasan, $admin) {
            $pp = PeminatanPeriode::where('periode_pendaftaran_id', $periode->id)->where('peminatan_id', $peminatan->id)->lockForUpdate()->first();
            if ($pp && $pp->kuota_min === $minimum) {
                return $pp; // tidak berubah
            }
            if (! $pp && ! $peminatan->is_active) {
                throw ValidationException::withMessages(['kuota_min' => "Peminatan {$peminatan->nama} nonaktif sehingga tidak dapat dibuka pada periode ini."]);
            }

            $sebelum = $pp ? ['kuota_min' => $pp->kuota_min] : null;
            $pp ??= new PeminatanPeriode(['periode_pendaftaran_id' => $periode->id, 'peminatan_id' => $peminatan->id, 'status' => 'berjalan']);
            $pp->kuota_min = $minimum;
            $pp->save();
            $this->audit->catat('atur_kuota', 'peminatan_periode', $pp->id, sebelum: $sebelum,
                sesudah: ['periode' => $periode->id, 'peminatan' => $peminatan->kode, 'kuota_min' => $minimum], alasan: $alasan, pelaku: $admin);

            return $pp;
        });
    }

    /** Lepas peminatan dari periode: hanya sebelum periode dimulai dan bila belum ada pendaftaran. */
    public function lepas(PeminatanPeriode $pp, User $admin): void
    {
        $periode = $pp->periode;
        if (self::sudahDimulai($periode) || ! self::bolehUbah($periode, $admin)) {
            throw ValidationException::withMessages(['kuota_min' => 'Peminatan hanya dapat dilepas dari periode sebelum periode dimulai.']);
        }

        DB::transaction(function () use ($pp, $admin) {
            if (Pendaftaran::where('peminatan_periode_id', $pp->id)->exists()) {
                throw ValidationException::withMessages(['kuota_min' => 'Peminatan ini sudah memiliki pendaftar pada periode tersebut.']);
            }
            $this->audit->catat('lepas_kuota', 'peminatan_periode', $pp->id, sebelum: ['peminatan' => $pp->peminatan_id, 'kuota_min' => $pp->kuota_min], pelaku: $admin);
            $pp->delete();
        });
    }
}
