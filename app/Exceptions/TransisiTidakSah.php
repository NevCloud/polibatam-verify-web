<?php

namespace App\Exceptions;

use RuntimeException;

/** Perubahan status di luar tabel transisi SRS §4 (NFR-28, AE-04) → HTTP 409 kode TRANSISI_TIDAK_SAH. */
class TransisiTidakSah extends RuntimeException
{
    public const KODE = 'TRANSISI_TIDAK_SAH';

    public static function dari(string $entitas, string $dari, string $ke): self
    {
        return new self("Status {$entitas} tidak dapat diubah dari {$dari} menjadi {$ke}.");
    }
}
