<?php

namespace App\States\Refund;

final class Diproses extends StatusRefund
{
    /** @var string */
    public static $name = 'diproses';

    public function label(): string
    {
        return 'Diproses';
    }
}
