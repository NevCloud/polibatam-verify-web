<?php

namespace App\Events;

use App\Models\PeriodePendaftaran;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * PRD kontrak event: dipicu TutupPeriode (P04) setelah Draf dibatalkan.
 * STUB-BE(P11): pendengar pembuatan arsip periode (FR-65, BR-69) belum ada.
 */
class PeriodeDitutup implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public readonly PeriodePendaftaran $periode) {}
}
