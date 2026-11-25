<?php

namespace App\States\ArsipPeriode;

final class Diproses extends StatusArsipPeriode
{
    /** @var string */
    public static $name = 'diproses';

    public function label(): string
    {
        return 'Diproses';
    }
}
