<?php

namespace App\Actions\Pendaftaran;

use App\Exceptions\SudahDiverifikasi;
use App\Models\Dokumen;
use App\Models\KriteriaChecklist;
use App\Models\User;
use App\Models\VerifikasiDokumen as Keputusan;
use App\Services\AuditLogger;
use App\Services\CekVersiData;
use App\States\Dokumen\Ditolak;
use App\States\Dokumen\Valid;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Keputusan Admin SILAU untuk SATU dokumen (FR-20, BR-13, BR-61, BR-66, A-64). Dipakai web (HW-26) & API mobile (P06).
 * - Valid hanya bila seluruh kriteria checklist aktif terpenuhi (AC-10.2, BR-74).
 * - Ditolak wajib alasan: kriteria yang tidak terpenuhi dan/atau catatan, minimal salah satu (A-64).
 * - Optimistic locking: `versi` = updated_at dokumen yang dilihat admin; berbeda → SudahDiverifikasi (409).
 */
class VerifikasiDokumen
{
    public const KEPUTUSAN = ['valid', 'ditolak'];

    public function __construct(private CekVersiData $versi, private AuditLogger $audit) {}

    /** @param  list<int|string>  $terpenuhi  id kriteria yang dicentang */
    public function __invoke(Dokumen $dokumen, ?string $versi, string $keputusan, array $terpenuhi, ?string $catatan, User $admin, ?string $kanal = null): Keputusan
    {
        if (! in_array($keputusan, self::KEPUTUSAN, true)) {
            throw ValidationException::withMessages(['keputusan' => 'Pilih keputusan Valid atau Ditolak.']);
        }

        $kriteria = KriteriaChecklist::aktifUntuk($dokumen->jenis)->get(['id', 'teks']);
        $terpenuhi = array_map('intval', $terpenuhi);
        $hasil = $kriteria->map(fn (KriteriaChecklist $k) => ['kriteria_id' => $k->id, 'teks' => $k->teks, 'terpenuhi' => in_array($k->id, $terpenuhi, true)]);
        $gagal = $hasil->where('terpenuhi', false)->pluck('teks')->all();
        $catatan = trim((string) $catatan) ?: null;

        if ($keputusan === 'valid' && $gagal) {
            throw ValidationException::withMessages(['keputusan' => 'Dokumen hanya dapat dinyatakan Valid bila seluruh kriteria checklist terpenuhi.']);
        }
        if ($keputusan === 'ditolak' && ! $gagal && $catatan === null) {
            throw ValidationException::withMessages(['catatan' => 'Alasan penolakan wajib: hilangkan centang kriteria yang tidak terpenuhi dan/atau isi catatan.']);
        }

        $alasan = $keputusan === 'valid' ? null : implode(' ', array_filter([
            $gagal ? 'Tidak terpenuhi: '.implode('; ', $gagal).'.' : null,
            $catatan ? 'Catatan: '.$catatan : null,
        ]));

        return DB::transaction(function () use ($dokumen, $versi, $keputusan, $hasil, $alasan, $admin, $kanal) {
            // BR-61: keputusan hanya sah saat pendaftaran masih Diajukan; diputuskan pihak lain → 409.
            $statusPendaftaran = DB::table('pendaftarans')->where('id', $dokumen->pendaftaran_id)->lockForUpdate()->value('status');
            if ($statusPendaftaran !== 'diajukan') {
                throw new SudahDiverifikasi;
            }

            $this->versi->transisi($dokumen, $keputusan === 'valid' ? Valid::class : Ditolak::class, $versi);

            $baris = Keputusan::create([
                'dokumen_versi_id' => $dokumen->versi_aktif_id,
                'keputusan' => $keputusan,
                'hasil_checklist' => $hasil->values()->all(),
                'alasan' => $alasan,
                'diverifikasi_oleh' => $admin->id,
                'kanal' => $kanal ?? (request()->is('api/*') ? 'mobile' : 'web'),
            ]);

            $this->audit->catat('verifikasi_dokumen', 'dokumen', $dokumen->id,
                sebelum: ['status' => 'menunggu_verifikasi'], sesudah: ['status' => $keputusan, 'jenis' => $dokumen->jenis], alasan: $alasan, pelaku: $admin);

            return $baris;
        });
    }
}
