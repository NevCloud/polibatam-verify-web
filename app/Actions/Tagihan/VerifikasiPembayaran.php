<?php

namespace App\Actions\Tagihan;

use App\Models\BuktiPembayaran;
use App\Models\PesertaDidik;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\CekVersiData;
use App\Services\Notifier;
use App\States\PesertaDidik\Aktif;
use App\States\Tagihan\Ditolak;
use App\States\Tagihan\Lunas;
use App\Support\Ui\Format;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * FR-32–FR-35, BR-38, BR-39, BR-61, BR-66, A-64. Dipakai web (HW-29) & API mobile (P06).
 * - Lunas formulir hanya bila pendaftaran Masuk Kuota (gerbang ganda BR-39) → NPD terbit (FR-34).
 * - Lunas semester N → peserta Aktif Semester N, termasuk yang Tertahan (FR-35).
 * - Ditolak wajib alasan: alasan baku dan/atau catatan, minimal salah satu (A-64); unggah bukti dibuka lagi.
 * - Optimistic locking: versi = updated_at tagihan yang dilihat admin → 409 bila berbeda.
 */
class VerifikasiPembayaran
{
    public const KEPUTUSAN = ['lunas', 'tolak'];

    /** Alasan baku penolakan pembayaran (A-64). */
    public const ALASAN_BAKU = [
        'mutasi_tidak_ditemukan' => 'Mutasi tidak ditemukan',
        'nominal_tidak_sesuai' => 'Nominal tidak sesuai',
        'bukti_tidak_terbaca' => 'Bukti tidak terbaca',
    ];

    public const PESAN_GERBANG = 'Menunggu verifikasi berkas: tagihan formulir hanya dapat dinyatakan Lunas setelah pendaftaran Masuk Kuota.';

    public function __construct(private CekVersiData $versi, private TerbitkanNpd $npd, private AuditLogger $audit, private Notifier $notifier) {}

    /** @param  list<string>  $alasanBaku  kunci ALASAN_BAKU */
    public function __invoke(Tagihan $tagihan, ?string $versi, string $keputusan, array $alasanBaku, ?string $catatan, User $admin, ?string $kanal = null): Tagihan
    {
        if (! in_array($keputusan, self::KEPUTUSAN, true)) {
            throw ValidationException::withMessages(['keputusan' => 'Pilih Tandai Lunas atau Tolak Pembayaran.']);
        }
        $catatan = trim((string) $catatan) ?: null;
        $baku = array_values(array_intersect_key(self::ALASAN_BAKU, array_flip($alasanBaku)));
        if ($keputusan === 'tolak' && ! $baku && $catatan === null) {
            throw ValidationException::withMessages(['catatan' => 'Alasan penolakan wajib: pilih alasan baku dan/atau isi catatan.']); // BR-38
        }
        $alasan = $keputusan === 'tolak' ? implode(' ', array_filter([$baku ? implode('; ', $baku).'.' : null, $catatan ? 'Catatan: '.$catatan : null])) : null;
        $kanal ??= request()->is('api/*') ? 'mobile' : 'web';

        return DB::transaction(function () use ($tagihan, $versi, $keputusan, $alasan, $admin, $kanal) {
            $p = $tagihan->pendaftaran()->lockForUpdate()->firstOrFail();
            $sudahNpd = $p->status->getValue() === 'npd_terbit'; // verifikasi ulang setelah koreksi Lunas (A-88): NPD tidak diterbitkan lagi
            if ($keputusan === 'lunas' && $tagihan->jenis === 'formulir' && ! in_array($p->status->getValue(), Tagihan::GERBANG_PENDAFTARAN, true)) {
                throw ValidationException::withMessages(['keputusan' => self::PESAN_GERBANG]); // BR-39, FR-33
            }

            $this->versi->transisi($tagihan, $keputusan === 'lunas' ? Lunas::class : Ditolak::class, $versi,
                $keputusan === 'lunas' ? ['lunas_pada' => now()] : []);

            BuktiPembayaran::where('tagihan_id', $tagihan->id)->latest('id')->first()?->update([
                'keputusan' => $keputusan === 'lunas' ? 'diterima' : 'ditolak', 'alasan' => $alasan,
                'diverifikasi_oleh' => $admin->id, 'diverifikasi_pada' => now(), 'kanal' => $kanal,
            ]);

            $this->audit->catat('verifikasi_pembayaran', 'tagihan', $tagihan->id,
                sebelum: ['status' => 'menunggu_verifikasi'], sesudah: ['status' => $keputusan === 'lunas' ? 'lunas' : 'ditolak'], alasan: $alasan, pelaku: $admin);

            if ($keputusan === 'tolak') {
                $this->notifier->kirim($p->user, 'tagihan', 'Bukti pembayaran ditolak',
                    "Pembayaran {$tagihan->kode_referensi} ditolak: {$alasan} Unggah ulang bukti yang benar.", route('tagihan.show', $tagihan));
            } elseif ($tagihan->jenis === 'formulir' && ! $sudahNpd) {
                ($this->npd)($p); // FR-34 (notifikasi NPD dikirim di sana)
            } else {
                if ($tagihan->jenis === 'semester') {
                    $this->aktifkanSemester($tagihan);
                }
                $this->notifier->kirim($p->user, 'tagihan', "Pembayaran {$tagihan->labelJenis()} lunas",
                    "Pembayaran {$tagihan->kode_referensi} sebesar ".Format::rupiah($tagihan->nominal_total).' telah disahkan.', route('tagihan.show', $tagihan));
            }

            return $tagihan;
        });
    }

    /** FR-35: Belum Aktif / Tertahan Semester N → Aktif Semester N. */
    private function aktifkanSemester(Tagihan $tagihan): void
    {
        $pd = PesertaDidik::whereKey($tagihan->peserta_didik_id)->lockForUpdate()->first();
        if ($pd && $pd->semester_berjalan === $tagihan->semester_ke && $pd->status->canTransitionTo(Aktif::class)) {
            $pd->status->transitionTo(Aktif::class);
        }
    }
}
