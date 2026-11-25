<?php

namespace App\States\PesertaDidik;

final class BelumAktif extends StatusPesertaDidik
{
    /** @var string */
    public static $name = 'belum_aktif';

    public function label(): string
    {
        return 'Belum Aktif Semester';
    }
}
