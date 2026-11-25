<?php

namespace App\Models;

use App\States\Tagihan\StatusTagihan;
use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStates\HasStates;

/**
 * Tabel `tagihans` (ERD).
 *
 * @property StatusTagihan $status
 */
class Tagihan extends Model
{
    use HasStates;

    protected $table = 'tagihans';

    /** @var list<string> */
    protected $guarded = ['id'];

    /** Kolom TIMESTAMP(6): versi optimistic locking (K-36, BR-61). */
    protected $dateFormat = 'Y-m-d H:i:s.u';

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => StatusTagihan::class,
            'jatuh_tempo' => 'date',
            'lunas_pada' => 'datetime',
        ];
    }
}
