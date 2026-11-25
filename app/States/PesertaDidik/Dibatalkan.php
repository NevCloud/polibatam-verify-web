<?php

namespace App\States\PesertaDidik;

final class Dibatalkan extends StatusPesertaDidik
{
    /** @var string */
    public static $name = 'dibatalkan';

    public function label(): string
    {
        return 'Dibatalkan';
    }
}
