<?php

namespace App\States\PesertaDidik;

final class Tertahan extends StatusPesertaDidik
{
    /** @var string */
    public static $name = 'tertahan';

    public function label(): string
    {
        return 'Tertahan (Tunggakan)';
    }
}
