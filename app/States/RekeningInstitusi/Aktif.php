<?php

namespace App\States\RekeningInstitusi;

final class Aktif extends StatusRekeningInstitusi
{
    /** @var string */
    public static $name = 'aktif';

    public function label(): string
    {
        return 'Aktif';
    }
}
