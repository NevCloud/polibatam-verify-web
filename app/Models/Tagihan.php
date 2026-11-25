<?php

namespace App\Models;

use App\States\Tagihan\StatusTagihan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Spatie\ModelStates\HasStates;

/**
 * Tabel `tagihans` (ERD) + snapshot rekening (01d, BR-71).
 *
 * @property int $id
 * @property string $kode_referensi
 * @property int $user_id
 * @property int $pendaftaran_id
 * @property int|null $peserta_didik_id
 * @property int|null $periode_akademik_id
 * @property int $tarif_id
 * @property string $jenis formulir / semester
 * @property int|null $semester_ke
 * @property int $nominal_dasar
 * @property int $kode_unik
 * @property int $nominal_total
 * @property Carbon|null $jatuh_tempo
 * @property bool $jatuh_tempo_manual
 * @property StatusTagihan $status
 * @property Carbon|null $lunas_pada
 * @property string|null $rekening_bank
 * @property string|null $rekening_nomor
 * @property string|null $rekening_atas_nama
 * @property Carbon $created_at
 * @property-read User $user
 * @property-read Pendaftaran $pendaftaran
 * @property-read PesertaDidik|null $pesertaDidik
 * @property-read Collection<int, BuktiPembayaran> $buktis
 * @property-read BuktiPembayaran|null $buktiTerakhir
 */
class Tagihan extends Model
{
    use HasStates;

    /** Status tagihan yang masih terbuka (BR-29: kode unik tidak boleh kembar di antara tagihan ini). */
    public const TERBUKA = ['belum_bayar', 'menunggu_verifikasi', 'ditolak'];

    protected $table = 'tagihans';

    /** @var list<string> */
    protected $guarded = ['id'];

    /** Kolom TIMESTAMP(6): versi optimistic locking (K-36, BR-61). */
    protected $dateFormat = 'Y-m-d H:i:s.u';

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => StatusTagihan::class,
            'jatuh_tempo' => 'date',
            'jatuh_tempo_manual' => 'boolean',
            'lunas_pada' => 'datetime',
            'nominal_dasar' => 'integer',
            'kode_unik' => 'integer',
            'nominal_total' => 'integer',
            'semester_ke' => 'integer',
        ];
    }

    /** Rute memakai kode referensi (`/tagihan/TG-2026-000412`). */
    public function getRouteKeyName(): string
    {
        return 'kode_referensi';
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Pendaftaran, $this> */
    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class);
    }

    /** @return BelongsTo<PesertaDidik, $this> */
    public function pesertaDidik(): BelongsTo
    {
        return $this->belongsTo(PesertaDidik::class);
    }

    /** @return HasMany<BuktiPembayaran, $this> */
    public function buktis(): HasMany
    {
        return $this->hasMany(BuktiPembayaran::class)->orderByDesc('id');
    }

    /** @return HasOne<BuktiPembayaran, $this> */
    public function buktiTerakhir(): HasOne
    {
        return $this->hasOne(BuktiPembayaran::class)->latestOfMany();
    }

    /**
     * Tagihan yang tidak dibatalkan (dipakai idempotensi penerbitan).
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeBerlaku(Builder $query): Builder
    {
        return $query->where('status', '<>', 'dibatalkan');
    }

    /** Status pendaftaran yang membuka gerbang Lunas tagihan formulir (BR-39; NPD Terbit = verifikasi ulang setelah koreksi, A-88). */
    public const GERBANG_PENDAFTARAN = ['masuk_kuota', 'npd_terbit'];

    /**
     * Tagihan yang boleh ditandai Lunas menurut gerbang ganda (BR-39) — dipakai Fast Verify (BR-74).
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeLolosGerbang(Builder $query): Builder
    {
        return $query->where(fn (Builder $q) => $q->where('jenis', '<>', 'formulir')
            ->orWhereHas('pendaftaran', fn (Builder $p) => $p->whereIn('status', self::GERBANG_PENDAFTARAN)));
    }

    public function lolosGerbang(): bool
    {
        return $this->jenis !== 'formulir' || in_array($this->pendaftaran->status->getValue(), self::GERBANG_PENDAFTARAN, true);
    }

    /** "Formulir pendaftaran" / "Semester 2". */
    public function labelJenis(): string
    {
        return $this->jenis === 'formulir' ? 'Formulir pendaftaran' : "Semester {$this->semester_ke}";
    }
}
