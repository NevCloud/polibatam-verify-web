<?php

namespace App\States\ArsipPeriode;

final class Gagal extends StatusArsipPeriode
{
    /** @var string */
    public static $name = 'gagal';

    public function label(): string
    {
        return 'Gagal';
    }
}
