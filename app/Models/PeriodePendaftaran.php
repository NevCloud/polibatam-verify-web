<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Tabel `periode_pendaftarans` (ERD §3.5). Status: dijadwalkan / dibuka / ditutup.
 *
 * @property int $id
 * @property string $nama
 * @property Carbon $tanggal_mulai
 * @property Carbon $tanggal_selesai
 * @property string $status
 */
class PeriodePendaftaran extends Model
{
    protected $table = 'periode_pendaftarans';

    /** @var list<string> */
    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['tanggal_mulai' => 'datetime', 'tanggal_selesai' => 'datetime', 'ditutup_pada' => 'datetime'];
    }

    public function sedangDibuka(): bool
    {
        return $this->status === 'dibuka' && now()->betweenIncluded($this->tanggal_mulai, $this->tanggal_selesai);
    }
}
