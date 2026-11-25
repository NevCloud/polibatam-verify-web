<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabel `tracks` (ERD §3.2). Dikelola Admin Track (P07).
 *
 * @property int $id
 * @property string $kode
 * @property string $nama
 * @property bool $is_active
 */
class Track extends Model
{
    protected $table = 'tracks';

    /** @var list<string> */
    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
