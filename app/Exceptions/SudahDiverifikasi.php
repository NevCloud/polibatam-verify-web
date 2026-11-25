<?php

namespace App\Exceptions;

use RuntimeException;

/** Data sudah diubah admin lain sejak dibuka (BR-61, FR-61) → HTTP 409 kode SUDAH_DIVERIFIKASI. */
class SudahDiverifikasi extends RuntimeException
{
    public const KODE = 'SUDAH_DIVERIFIKASI';

    public function __construct(string $message = 'Data ini sudah diverifikasi admin lain. Muat ulang untuk melihat data terbaru.')
    {
        parent::__construct($message);
    }
}
