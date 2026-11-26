<?php

namespace App\Models;

use App\Support\Ui\Format;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tabel `peminatan_periodes` (ERD §3.6): pusat kuota per peminatan per periode.
 *
 * @property int $id
 * @property int $periode_pendaftaran_id
 * @property int $peminatan_id
 * @property int $kuota_min
 * @property string $status
 * @property-read PeriodePendaftaran $periode
 * @property-read Peminatan $peminatan
 */
class PeminatanPeriode extends Model
{
    protected $table = 'peminatan_periodes';

    /** @var list<string> */
    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['kuota_min' => 'integer'];
    }

    /** @return BelongsTo<PeriodePendaftaran, $this> */
    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodePendaftaran::class, 'periode_pendaftaran_id');
    }

    /** @return BelongsTo<Peminatan, $this> */
    public function peminatan(): BelongsTo
    {
        return $this->belongsTo(Peminatan::class);
    }

    /**
     * BR-07: periode sedang dibuka, peminatan & track aktif, kuota berjalan, dan tarif formulir sudah berlaku.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeTerbuka(Builder $query): Builder
    {
        $sekarang = now();
        $hariIni = Format::wib($sekarang)->toDateString(); // tanggal_efektif tarif = tanggal kalender WIB

        return $query->where('peminatan_periodes.status', 'berjalan')
            ->whereHas('periode', fn (Builder $q) => $q->where('status', 'dibuka')
                ->where('tanggal_mulai', '<=', $sekarang)->where('tanggal_selesai', '>', $sekarang))
            ->whereHas('peminatan', fn (Builder $q) => $q->where('is_active', true)
                ->whereHas('track', fn (Builder $t) => $t->where('is_active', true))
                ->whereExists(fn ($t) => $t->from('tarifs')->whereColumn('tarifs.peminatan_id', 'peminatans.id')
                    ->where('jenis', 'formulir')->where('tanggal_efektif', '<=', $hariIni)));
    }
}
