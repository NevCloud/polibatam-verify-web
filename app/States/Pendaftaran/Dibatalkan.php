<?php

namespace App\States\Pendaftaran;

final class Dibatalkan extends StatusPendaftaran
{
    /** @var string */
    public static $name = 'dibatalkan';

    public function label(): string
    {
        return 'Dibatalkan';
    }
}
