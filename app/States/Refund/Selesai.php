<?php

namespace App\States\Refund;

final class Selesai extends StatusRefund
{
    /** @var string */
    public static $name = 'selesai';

    public function label(): string
    {
        return 'Selesai';
    }
}
