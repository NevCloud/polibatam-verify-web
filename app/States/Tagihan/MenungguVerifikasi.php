<?php

namespace App\States\Tagihan;

final class MenungguVerifikasi extends StatusTagihan
{
    /** @var string */
    public static $name = 'menunggu_verifikasi';

    public function label(): string
    {
        return 'Menunggu Verifikasi';
    }
}
