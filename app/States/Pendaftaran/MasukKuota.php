<?php

namespace App\States\Pendaftaran;

final class MasukKuota extends StatusPendaftaran
{
    /** @var string */
    public static $name = 'masuk_kuota';

    public function label(): string
    {
        return 'Masuk Kuota';
    }
}
