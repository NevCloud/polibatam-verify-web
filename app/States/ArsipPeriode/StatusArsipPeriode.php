<?php

namespace App\States\ArsipPeriode;

use App\Models\ArsipPeriode;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/**
 * Status ArsipPeriode — tabel transisi SRS §4.6 (Tersedia final). Transisi di luar daftar ini ditolak (NFR-28, AE-04).
 *
 * @extends State<ArsipPeriode>
 */
abstract class StatusArsipPeriode extends State
{
    /** Nama status resmi untuk UI (design-memory §6.2). */
    abstract public function label(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Diproses::class)
            ->registerState(Diproses::class)
            ->registerState(Tersedia::class)
            ->registerState(Gagal::class)
            ->allowTransition(Diproses::class, Tersedia::class) // BR-69
            ->allowTransition(Diproses::class, Gagal::class) // BR-69
            ->allowTransition(Gagal::class, Diproses::class); // BR-69 percobaan ulang
    }
}
