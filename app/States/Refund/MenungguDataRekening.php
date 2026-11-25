<?php

namespace App\States\Refund;

final class MenungguDataRekening extends StatusRefund
{
    /** @var string */
    public static $name = 'menunggu_data_rekening';

    public function label(): string
    {
        return 'Menunggu Data Rekening';
    }
}
