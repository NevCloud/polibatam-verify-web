<?php

namespace App\Events;

use App\Models\Pendaftaran;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * PRD kontrak event: dipicu AjukanFormulir (P03). Pendengar: P05 menerbitkan tagihan formulir (FR-26).
 * STUB-BE(P05): belum ada pendengar; kartu "Tagihan Formulir" tampil "Belum terbit".
 */
class FormulirDiajukan implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public readonly Pendaftaran $pendaftaran) {}
}
