<?php

namespace App\States\Pendaftaran;

use App\Models\Pendaftaran;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/**
 * Status Pendaftaran — tabel transisi SRS §4.2. Transisi di luar daftar ini ditolak (NFR-28, AE-04).
 * Daftar Tunggu & Tidak Tertampung dihapus (K-42: tidak ada kuota maksimum).
 *
 * @extends State<Pendaftaran>
 */
abstract class StatusPendaftaran extends State
{
    /** Nama status resmi untuk UI (design-memory §6.2). */
    abstract public function label(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Draf::class)
            ->registerState(Draf::class)
            ->registerState(Diajukan::class)
            ->registerState(PerluRevisi::class)
            ->registerState(MasukKuota::class)
            ->registerState(NpdTerbit::class)
            ->registerState(Dibatalkan::class)
            ->allowTransition(Draf::class, Diajukan::class) // BR-07
            ->allowTransition(Draf::class, Dibatalkan::class) // BR-65
            ->allowTransition(Diajukan::class, PerluRevisi::class) // BR-13
            ->allowTransition(PerluRevisi::class, Diajukan::class) // BR-14
            ->allowTransition(Diajukan::class, MasukKuota::class) // BR-17 (tanpa kuota maksimum, K-42)
            ->allowTransition(MasukKuota::class, NpdTerbit::class) // BR-39, BR-41
            ->allowTransition(MasukKuota::class, Dibatalkan::class) // BR-21, BR-24
            ->allowTransition([Diajukan::class, PerluRevisi::class, NpdTerbit::class], Dibatalkan::class); // BR-24 peminatan dibatalkan
    }
}
