<?php

namespace App\States\Tagihan;

final class BelumBayar extends StatusTagihan
{
    /** @var string */
    public static $name = 'belum_bayar';

    public function label(): string
    {
        return 'Belum Bayar';
    }
}
