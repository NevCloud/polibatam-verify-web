<?php

namespace App\States\RekeningInstitusi;

final class Nonaktif extends StatusRekeningInstitusi
{
    /** @var string */
    public static $name = 'nonaktif';

    public function label(): string
    {
        return 'Nonaktif';
    }
}
