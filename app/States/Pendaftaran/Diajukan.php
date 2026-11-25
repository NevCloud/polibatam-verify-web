<?php

namespace App\States\Pendaftaran;

final class Diajukan extends StatusPendaftaran
{
    /** @var string */
    public static $name = 'diajukan';

    public function label(): string
    {
        return 'Diajukan';
    }
}
