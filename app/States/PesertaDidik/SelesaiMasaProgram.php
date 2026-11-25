<?php

namespace App\States\PesertaDidik;

final class SelesaiMasaProgram extends StatusPesertaDidik
{
    /** @var string */
    public static $name = 'selesai_masa_program';

    public function label(): string
    {
        return 'Selesai Masa Program';
    }
}
