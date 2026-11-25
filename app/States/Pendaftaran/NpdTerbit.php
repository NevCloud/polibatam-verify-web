<?php

namespace App\States\Pendaftaran;

final class NpdTerbit extends StatusPendaftaran
{
    /** @var string */
    public static $name = 'npd_terbit';

    public function label(): string
    {
        return 'NPD Terbit';
    }
}
