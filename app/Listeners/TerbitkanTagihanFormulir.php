<?php

namespace App\Listeners;

use App\Actions\Tagihan\TerbitkanTagihan;
use App\Events\FormulirDiajukan;

/** BR-32(a), FR-26: tagihan formulir terbit saat formulir diajukan (setelah commit pengajuan). */
class TerbitkanTagihanFormulir
{
    public function __construct(private TerbitkanTagihan $terbitkan) {}

    public function handle(FormulirDiajukan $event): void
    {
        $this->terbitkan->formulir($event->pendaftaran);
    }
}
