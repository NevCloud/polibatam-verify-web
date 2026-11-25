<?php

namespace App\States\ArsipPeriode;

final class Tersedia extends StatusArsipPeriode
{
    /** @var string */
    public static $name = 'tersedia';

    public function label(): string
    {
        return 'Tersedia';
    }
}
