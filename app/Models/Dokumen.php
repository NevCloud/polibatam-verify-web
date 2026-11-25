<?php

namespace App\Models;

use App\States\Dokumen\StatusDokumen;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\ModelStates\HasStates;

/**
 * Tabel `dokumens` (ERD). Satu baris per jenis dokumen per pendaftaran; isi berkas di `dokumen_versis`.
 * Slot kosong = `versi_aktif_id` NULL (pv_app tidak punya DELETE, jadi "hapus" saat Draf = kosongkan versi aktif).
 *
 * @property int $id
 * @property int $pendaftaran_id
 * @property string $jenis
 * @property bool $wajib
 * @property StatusDokumen $status
 * @property int|null $versi_aktif_id
 * @property-read Pendaftaran $pendaftaran
 * @property-read DokumenVersi|null $versiAktif
 * @property-read Collection<int, DokumenVersi> $versis
 */
class Dokumen extends Model
{
    use HasStates;

    /** BR-11 (urutan & teks mengikuti Figma HW-15 Tab Berkas). */
    public const JENIS = [
        'ktp' => ['label' => 'KTP', 'wajib' => true, 'keterangan' => 'Seluruh informasi terbaca jelas'],
        'ijazah_skl_rapor' => ['label' => 'Ijazah / SKL / Rapor', 'wajib' => true, 'keterangan' => 'Ijazah, SKL, atau Rapor semester 5'],
        'skhu_nilai' => ['label' => 'SKHU / Daftar Nilai', 'wajib' => true, 'keterangan' => 'SKHU, Nilai UN, SKL, atau Daftar Nilai'],
        'bukti_pendaftaran_polibatam' => ['label' => 'Bukti Pendaftaran Polibatam', 'wajib' => false, 'keterangan' => 'Jika pernah mendaftar di Polibatam'],
    ];

    protected $table = 'dokumens';

    /** @var list<string> */
    protected $guarded = ['id'];

    /** Kolom TIMESTAMP(6): versi optimistic locking (K-36, BR-61). */
    protected $dateFormat = 'Y-m-d H:i:s.u';

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => StatusDokumen::class,
            'wajib' => 'boolean',
        ];
    }

    /** @return BelongsTo<Pendaftaran, $this> */
    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class);
    }

    /** @return BelongsTo<DokumenVersi, $this> */
    public function versiAktif(): BelongsTo
    {
        return $this->belongsTo(DokumenVersi::class, 'versi_aktif_id');
    }

    /** @return HasMany<DokumenVersi, $this> */
    public function versis(): HasMany
    {
        return $this->hasMany(DokumenVersi::class)->orderByDesc('id');
    }

    public function label(): string
    {
        return self::JENIS[$this->jenis]['label'] ?? $this->jenis;
    }
}
