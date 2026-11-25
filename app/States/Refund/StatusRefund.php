<?php

namespace App\States\Refund;

use App\Models\Refund;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/**
 * Status Refund — tabel transisi SRS §4.5. Transisi di luar daftar ini ditolak (NFR-28, AE-04).
 *
 * @extends State<Refund>
 */
abstract class StatusRefund extends State
{
    /** Nama status resmi untuk UI (design-memory §6.2). */
    abstract public function label(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(MenungguDataRekening::class)
            ->registerState(MenungguDataRekening::class)
            ->registerState(Diproses::class)
            ->registerState(Selesai::class)
            ->registerState(Ditolak::class)
            ->allowTransition(MenungguDataRekening::class, Diproses::class) // BR-50, BR-51
            ->allowTransition(Diproses::class, Selesai::class) // BR-51
            ->allowTransition([MenungguDataRekening::class, Diproses::class], Ditolak::class); // BR-49, BR-51
    }
}
