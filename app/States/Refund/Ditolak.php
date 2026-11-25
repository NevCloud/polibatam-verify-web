<?php

namespace App\States\Refund;

final class Ditolak extends StatusRefund
{
    /** @var string */
    public static $name = 'ditolak';

    public function label(): string
    {
        return 'Ditolak';
    }
}
