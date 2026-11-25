<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Tabel `periode_akademiks` (ERD §3.13): semester berjalan; dasar jatuh tempo tagihan semester (BR-35). Dikelola di P10.
 *
 * @property int $id
 * @property string $nama
 * @property Carbon $tanggal_mulai
 * @property Carbon $tanggal_selesai
 */
class PeriodeAkademik extends Model
{
    protected $table = 'periode_akademiks';

    /** @var list<string> */
    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['tanggal_mulai' => 'date', 'tanggal_selesai' => 'date'];
    }
}
