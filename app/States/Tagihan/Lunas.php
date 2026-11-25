<?php

namespace App\States\Tagihan;

final class Lunas extends StatusTagihan
{
    /** @var string */
    public static $name = 'lunas';

    public function label(): string
    {
        return 'Lunas';
    }
}
