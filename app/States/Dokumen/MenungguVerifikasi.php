<?php

namespace App\States\Dokumen;

final class MenungguVerifikasi extends StatusDokumen
{
    /** @var string */
    public static $name = 'menunggu_verifikasi';

    public function label(): string
    {
        return 'Menunggu Verifikasi';
    }
}
