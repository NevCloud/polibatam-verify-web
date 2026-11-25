<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Tabel `bukti_pembayarans` (ERD) + data pengiriman (01d, BR-72). Keputusan: menunggu / diterima / ditolak.
 *
 * @property int $id
 * @property int $tagihan_id
 * @property string $path
 * @property string $mime
 * @property int $ukuran
 * @property Carbon $diunggah_pada
 * @property Carbon|null $tanggal_transfer
 * @property int|null $nominal_transfer
 * @property string|null $bank_pengirim
 * @property string|null $nama_pengirim
 * @property string $keputusan
 * @property string|null $alasan
 * @property Carbon|null $diverifikasi_pada
 * @property-read Tagihan $tagihan
 */
class BuktiPembayaran extends Model
{
    protected $table = 'bukti_pembayarans';

    /** @var list<string> */
    protected $guarded = ['id'];

    protected $dateFormat = 'Y-m-d H:i:s.u';

    /** Path & hash berkas tidak pernah ikut serialisasi. */
    protected $hidden = ['path', 'sha256'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'diunggah_pada' => 'datetime',
            'tanggal_transfer' => 'date',
            'nominal_transfer' => 'integer',
            'diverifikasi_pada' => 'datetime',
        ];
    }

    /** @return BelongsTo<Tagihan, $this> */
    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class);
    }
}
