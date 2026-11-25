<?php

namespace App\States\Pendaftaran;

final class PerluRevisi extends StatusPendaftaran
{
    /** @var string */
    public static $name = 'perlu_revisi';

    public function label(): string
    {
        return 'Perlu Revisi';
    }
}
