<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Tabel `verifikasi_dokumens` (ERD §3.10): riwayat keputusan per versi, append-only. Ditulis P04.
 *
 * @property string $keputusan valid / ditolak
 * @property string|null $alasan
 * @property Carbon $created_at
 */
class VerifikasiDokumen extends Model
{
    protected $table = 'verifikasi_dokumens';

    /** @var list<string> */
    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['hasil_checklist' => 'array'];
    }
}
