<?php

namespace App\States\PesertaDidik;

final class Aktif extends StatusPesertaDidik
{
    /** @var string */
    public static $name = 'aktif';

    public function label(): string
    {
        return 'Aktif Semester';
    }
}
