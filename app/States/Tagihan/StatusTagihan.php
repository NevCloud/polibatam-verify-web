<?php

namespace App\States\Tagihan;

use App\Models\Tagihan;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/**
 * Status Tagihan — tabel transisi SRS §4.4. Transisi di luar daftar ini ditolak (NFR-28, AE-04).
 *
 * @extends State<Tagihan>
 */
abstract class StatusTagihan extends State
{
    /** Nama status resmi untuk UI (design-memory §6.2). */
    abstract public function label(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(BelumBayar::class)
            ->registerState(BelumBayar::class)
            ->registerState(MenungguVerifikasi::class)
            ->registerState(Lunas::class)
            ->registerState(Ditolak::class)
            ->registerState(Dibatalkan::class)
            ->allowTransition([BelumBayar::class, Ditolak::class], MenungguVerifikasi::class) // BR-36, BR-72
            ->allowTransition(MenungguVerifikasi::class, Lunas::class) // BR-39, BR-74
            ->allowTransition(MenungguVerifikasi::class, Ditolak::class) // BR-38
            ->allowTransition([BelumBayar::class, Ditolak::class, MenungguVerifikasi::class], Dibatalkan::class) // BR-21, BR-24
            ->allowTransition(Lunas::class, MenungguVerifikasi::class); // BR-40 koreksi Super Admin (keputusan 25-11-2026)
    }
}
