<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Tabel `tarifs` (ERD §3.4): riwayat append-only (pv_app hanya INSERT, BR-26). Dikelola Admin Track (P07).
 *
 * @property int $id
 * @property int $peminatan_id
 * @property string $jenis formulir / semester
 * @property int|null $semester_ke
 * @property int $nominal
 * @property Carbon $tanggal_efektif
 */
class Tarif extends Model
{
    protected $table = 'tarifs';

    /** @var list<string> */
    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['tanggal_efektif' => 'date', 'nominal' => 'integer'];
    }
}
