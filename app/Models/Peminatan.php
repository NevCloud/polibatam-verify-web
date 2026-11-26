<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tabel `peminatans` (ERD §3.3). Dikelola Admin Track (P07).
 *
 * @property int $id
 * @property int $track_id
 * @property string $kode
 * @property string $nama
 * @property int $durasi_semester
 * @property bool $is_active
 * @property-read Track $track
 */
class Peminatan extends Model
{
    protected $table = 'peminatans';

    /** @var list<string> */
    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** @return BelongsTo<Track, $this> */
    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    /** @return HasMany<Tarif, $this> */
    public function tarifs(): HasMany
    {
        return $this->hasMany(Tarif::class);
    }

    /** @return HasMany<PeminatanPeriode, $this> */
    public function peminatanPeriodes(): HasMany
    {
        return $this->hasMany(PeminatanPeriode::class);
    }

    /** BR-53: sudah pernah dipakai pendaftaran (Draf pun dihitung) → tidak boleh dihapus, kode dikunci (dipakai format NPD). */
    public function pernahDipakai(): bool
    {
        return Pendaftaran::whereHas('peminatanPeriode', fn ($q) => $q->where('peminatan_id', $this->id))->exists();
    }
}
