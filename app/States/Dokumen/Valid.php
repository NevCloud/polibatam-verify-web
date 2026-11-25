<?php

namespace App\States\Dokumen;

final class Valid extends StatusDokumen
{
    /** @var string */
    public static $name = 'valid';

    public function label(): string
    {
        return 'Valid';
    }
}
