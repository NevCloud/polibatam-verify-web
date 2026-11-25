<?php

namespace App\Models;

use App\States\Pendaftaran\Draf;
use App\States\Pendaftaran\StatusPendaftaran;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Spatie\ModelStates\HasStates;

/**
 * Tabel `pendaftarans` (ERD).
 *
 * @property int $id
 * @property string $nomor_pendaftaran
 * @property int $user_id
 * @property int $peminatan_periode_id
 * @property StatusPendaftaran $status
 * @property string|null $nama_lengkap
 * @property string|null $nik_terenkripsi
 * @property string|null $tempat_lahir
 * @property Carbon|null $tanggal_lahir
 * @property string|null $jenis_kelamin
 * @property string|null $no_hp
 * @property string|null $alamat
 * @property string|null $asal_sekolah
 * @property int|null $tahun_lulus
 * @property Carbon|null $waktu_pengajuan
 * @property Carbon|null $waktu_masuk_kuota
 * @property string|null $alasan_batal
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @property-read PeminatanPeriode $peminatanPeriode
 * @property-read Collection<int, Dokumen> $dokumens
 * @property-read PesertaDidik|null $pesertaDidik
 */
class Pendaftaran extends Model
{
    use HasStates;

    /** Kolom wajib sebelum diajukan (FR-11, FR-12, K-31) → label untuk penanda kekurangan. */
    public const KOLOM_WAJIB = [
        'nik_terenkripsi' => 'NIK',
        'nama_lengkap' => 'Nama lengkap',
        'tempat_lahir' => 'Tempat lahir',
        'tanggal_lahir' => 'Tanggal lahir',
        'jenis_kelamin' => 'Jenis kelamin',
        'no_hp' => 'Nomor HP',
        'alamat' => 'Alamat',
        'asal_sekolah' => 'Asal sekolah',
        'tahun_lulus' => 'Tahun lulus',
    ];

    public const JENIS_KELAMIN = ['L' => 'Laki-laki', 'P' => 'Perempuan'];

    protected $table = 'pendaftarans';

    /** @var list<string> */
    protected $guarded = ['id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => StatusPendaftaran::class,
            'nik_terenkripsi' => 'encrypted', // AE-06: AES-256 dengan APP_KEY
            'tanggal_lahir' => 'date',
            'tahun_lulus' => 'integer',
            'waktu_pengajuan' => 'datetime',
            'waktu_masuk_kuota' => 'datetime',
        ];
    }

    /** AE-06: NIK utuh dan hash-nya tidak pernah ikut serialisasi (JSON/API/log). */
    protected $hidden = ['nik_terenkripsi', 'nik_hash'];

    /** Rute peserta memakai nomor pendaftaran (`/pendaftaran/PV-2026-000418`). */
    public function getRouteKeyName(): string
    {
        return 'nomor_pendaftaran';
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<PeminatanPeriode, $this> */
    public function peminatanPeriode(): BelongsTo
    {
        return $this->belongsTo(PeminatanPeriode::class);
    }

    /** @return HasMany<Dokumen, $this> */
    public function dokumens(): HasMany
    {
        return $this->hasMany(Dokumen::class);
    }

    /** @return HasMany<Tagihan, $this> */
    public function tagihans(): HasMany
    {
        return $this->hasMany(Tagihan::class);
    }

    /** @return HasOne<PesertaDidik, $this> */
    public function pesertaDidik(): HasOne
    {
        return $this->hasOne(PesertaDidik::class);
    }

    /**
     * BR-64: Draf tidak pernah tampil di sisi admin (antrean, data pendaftaran, laporan, ekspor, arsip).
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeUntukAdmin(Builder $query): Builder
    {
        return $query->whereNot('status', Draf::getMorphClass());
    }

    /**
     * BR-08: pendaftaran yang belum berstatus akhir (Dibatalkan, NPD Terbit).
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeBelumAkhir(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['dibatalkan', 'npd_terbit']);
    }

    public function adalahDraf(): bool
    {
        return $this->status->equals(Draf::class);
    }

    /** Label status resmi (design-memory §6.2). */
    public function labelStatus(): string
    {
        return $this->status->label();
    }

    /**
     * FR-12 / AC-04.3: kolom dan dokumen wajib yang belum lengkap.
     *
     * @return array{data: array<string, string>, berkas: array<string, string>}
     */
    public function kekurangan(): array
    {
        $data = array_filter(self::KOLOM_WAJIB, fn (string $label, string $kolom) => blank($this->getAttribute($kolom)), ARRAY_FILTER_USE_BOTH);
        $terunggah = $this->dokumens->whereNotNull('versi_aktif_id')->pluck('jenis')->all();
        $berkas = [];
        foreach (Dokumen::JENIS as $jenis => $info) {
            if ($info['wajib'] && ! in_array($jenis, $terunggah, true)) {
                $berkas[$jenis] = $info['label'];
            }
        }

        return ['data' => $data, 'berkas' => $berkas];
    }
}
