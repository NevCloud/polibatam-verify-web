<?php

namespace App\States\Dokumen;

use App\Models\Dokumen;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

/**
 * Status Dokumen — tabel transisi A-73 (disimpulkan dari BR-13, BR-14; Valid terkunci). Transisi di luar daftar ini ditolak (NFR-28, AE-04).
 *
 * @extends State<Dokumen>
 */
abstract class StatusDokumen extends State
{
    /** Nama status resmi untuk UI (design-memory §6.2). */
    abstract public function label(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(MenungguVerifikasi::class)
            ->registerState(MenungguVerifikasi::class)
            ->registerState(Valid::class)
            ->registerState(Ditolak::class)
            ->allowTransition(MenungguVerifikasi::class, Valid::class) // FR-20
            ->allowTransition(MenungguVerifikasi::class, Ditolak::class) // BR-13
            ->allowTransition(Ditolak::class, MenungguVerifikasi::class); // BR-14 unggah ulang
    }
}
