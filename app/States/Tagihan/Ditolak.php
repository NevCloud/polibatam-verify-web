<?php

namespace App\States\Tagihan;

final class Ditolak extends StatusTagihan
{
    /** @var string */
    public static $name = 'ditolak';

    public function label(): string
    {
        return 'Ditolak';
    }
}
