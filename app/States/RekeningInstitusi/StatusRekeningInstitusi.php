<?php

namespace App\States\RekeningInstitusi;

use App\Models\RekeningInstitusi;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/**
 * Status RekeningInstitusi — tabel transisi SRS §4.7. Transisi di luar daftar ini ditolak (NFR-28, AE-04).
 *
 * @extends State<RekeningInstitusi>
 */
abstract class StatusRekeningInstitusi extends State
{
    /** Nama status resmi untuk UI (design-memory §6.2). */
    abstract public function label(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Nonaktif::class)
            ->registerState(Nonaktif::class)
            ->registerState(Aktif::class)
            ->allowTransition(Nonaktif::class, Aktif::class) // BR-70
            ->allowTransition(Aktif::class, Nonaktif::class); // BR-70, BR-71
    }
}
