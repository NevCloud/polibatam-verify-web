<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Tabel `kriteria_checklists` (ERD §3.11): kriteria verifikasi per jenis dokumen (FR-20). Dikelola Super Admin (P12).
 *
 * @property int $id
 * @property string $jenis_dokumen
 * @property string $teks
 * @property int $urutan
 * @property bool $is_active
 */
class KriteriaChecklist extends Model
{
    protected $table = 'kriteria_checklists';

    /** @var list<string> */
    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeAktifUntuk(Builder $query, string $jenis): Builder
    {
        return $query->where('jenis_dokumen', $jenis)->where('is_active', true)->orderBy('urutan');
    }
}
