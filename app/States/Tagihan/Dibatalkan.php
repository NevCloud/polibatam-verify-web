<?php

namespace App\States\Tagihan;

final class Dibatalkan extends StatusTagihan
{
    /** @var string */
    public static $name = 'dibatalkan';

    public function label(): string
    {
        return 'Dibatalkan';
    }
}
