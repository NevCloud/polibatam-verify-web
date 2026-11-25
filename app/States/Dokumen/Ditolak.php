<?php

namespace App\States\Dokumen;

final class Ditolak extends StatusDokumen
{
    /** @var string */
    public static $name = 'ditolak';

    public function label(): string
    {
        return 'Ditolak';
    }
}
