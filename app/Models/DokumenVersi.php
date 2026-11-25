<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Tabel `dokumen_versis` (ERD §3.9): riwayat append-only (pv_app hanya INSERT, FR-17).
 *
 * @property int $id
 * @property int $dokumen_id
 * @property string $path
 * @property string $nama_asli
 * @property string $mime
 * @property int $ukuran
 * @property Carbon $diunggah_pada
 */
class DokumenVersi extends Model
{
    protected $table = 'dokumen_versis';

    /** @var list<string> */
    protected $guarded = ['id'];

    /** Path & hash berkas tidak pernah ikut serialisasi. */
    protected $hidden = ['path', 'sha256'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['diunggah_pada' => 'datetime'];
    }

    /** @return BelongsTo<Dokumen, $this> */
    public function dokumen(): BelongsTo
    {
        return $this->belongsTo(Dokumen::class);
    }

    /**
     * Keputusan Admin SILAU atas versi ini (P04 menulis; peserta membaca alasan, FR-16/FR-17).
     *
     * @return HasMany<VerifikasiDokumen, $this>
     */
    public function verifikasi(): HasMany
    {
        return $this->hasMany(VerifikasiDokumen::class);
    }
}
