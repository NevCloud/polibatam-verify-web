<?php

namespace App\States\Pendaftaran;

final class Draf extends StatusPendaftaran
{
    /** @var string */
    public static $name = 'draf';

    public function label(): string
    {
        return 'Draf';
    }
}
