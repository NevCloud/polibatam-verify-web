<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /** @return HasMany<Peminatan, $this> */
    public function peminatans(): HasMany
    {
        return $this->hasMany(Peminatan::class);
    }
}
