<?php

namespace App\Models;

use App\States\ArsipPeriode\StatusArsipPeriode;
use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStates\HasStates;

/**
 * Tabel `arsip_periodes` (ERD).
 *
 * @property StatusArsipPeriode $status
 */
class ArsipPeriode extends Model
{
    use HasStates;

    protected $table = 'arsip_periodes';

    /** @var list<string> */
    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => StatusArsipPeriode::class,
            'ringkasan' => 'array',
            'dibuat_pada' => 'datetime',
            'diperiksa_pada' => 'datetime',
        ];
    }
}
