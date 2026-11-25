<?php

namespace App\States\PesertaDidik;

use App\Models\PesertaDidik;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/**
 * Status PesertaDidik — tabel transisi SRS §4.3. Transisi di luar daftar ini ditolak (NFR-28, AE-04).
 *
 * @extends State<PesertaDidik>
 */
abstract class StatusPesertaDidik extends State
{
    /** Nama status resmi untuk UI (design-memory §6.2). */
    abstract public function label(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(BelumAktif::class)
            ->registerState(BelumAktif::class)
            ->registerState(Aktif::class)
            ->registerState(Tertahan::class)
            ->registerState(SelesaiMasaProgram::class)
            ->registerState(Dibatalkan::class)
            ->allowTransition(BelumAktif::class, Aktif::class) // BR-42
            ->allowTransition(Aktif::class, BelumAktif::class) // BR-45 rollover N < D
            ->allowTransition(Aktif::class, SelesaiMasaProgram::class) // BR-45 rollover N = D
            ->allowTransition(BelumAktif::class, Tertahan::class) // BR-45
            ->allowTransition(Tertahan::class, Aktif::class) // BR-46
            ->allowTransition([BelumAktif::class, Aktif::class, Tertahan::class], Dibatalkan::class); // BR-24
    }
}
