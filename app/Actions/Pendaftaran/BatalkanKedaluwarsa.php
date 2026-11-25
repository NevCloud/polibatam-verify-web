<?php

namespace App\Actions\Pendaftaran;

use App\Models\Pendaftaran;
use App\Models\Tagihan;
use App\Services\AuditLogger;
use App\Services\Notifier;
use App\Services\Pengaturan;
use App\States\Pendaftaran\Dibatalkan;
use App\States\Tagihan\Dibatalkan as TagihanDibatalkan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * BR-21, FR-24 (`pendaftaran:batalkan-kedaluwarsa`, tiap jam): Masuk Kuota yang tagihan formulirnya belum Lunas
 * > batas_pelunasan_formulir_hari → Dibatalkan. Tagihan Menunggu Verifikasi dikecualikan.
 * Idempoten (NFR-25): hanya memproses yang masih Masuk Kuota; syarat diperiksa ulang di dalam kunci baris.
 */
class BatalkanKedaluwarsa
{
    public const ALASAN = 'Tagihan formulir tidak dilunasi dalam batas waktu setelah Masuk Kuota';

    public function __construct(private AuditLogger $audit, private Notifier $notifier, private Pengaturan $pengaturan) {}

    /** @return int jumlah pendaftaran yang dibatalkan */
    public function __invoke(): int
    {
        $hari = $this->pengaturan->int('batas_pelunasan_formulir_hari');
        $batas = now()->subDays($hari);
        $jumlah = 0;

        foreach ($this->kandidat($batas)->pluck('id') as $id) {
            $jumlah += DB::transaction(function () use ($id, $batas, $hari) {
                $p = $this->kandidat($batas)->whereKey($id)->lockForUpdate()->first();
                if (! $p) {
                    return 0; // sudah lunas / diverifikasi / dibatalkan proses lain
                }

                $p->forceFill(['alasan_batal' => self::ALASAN." ({$hari} hari)"]);
                $p->status->transitionTo(Dibatalkan::class);

                Tagihan::where('pendaftaran_id', $p->id)->where('jenis', 'formulir')->whereIn('status', ['belum_bayar', 'ditolak'])
                    ->get()->each(fn (Tagihan $t) => $t->status->transitionTo(TagihanDibatalkan::class));

                $this->audit->catat('batalkan_kedaluwarsa', 'pendaftaran', $p->id,
                    sebelum: ['status' => 'masuk_kuota'], sesudah: ['status' => 'dibatalkan'], alasan: $p->alasan_batal);
                $this->notifier->kirim($p->user, 'status_pendaftaran', 'Pendaftaran dibatalkan',
                    "Pendaftaran {$p->nomor_pendaftaran} dibatalkan karena tagihan formulir tidak dilunasi dalam {$hari} hari setelah Masuk Kuota.",
                    route('status'));

                return 1;
            });
        }

        return $jumlah;
    }

    /** @return Builder<Pendaftaran> */
    private function kandidat(\DateTimeInterface $batas): Builder
    {
        return Pendaftaran::where('status', 'masuk_kuota')->where('waktu_masuk_kuota', '<=', $batas)
            ->whereNotExists(fn ($q) => $q->from('tagihans')->whereColumn('tagihans.pendaftaran_id', 'pendaftarans.id')
                ->where('tagihans.jenis', 'formulir')->whereIn('tagihans.status', ['menunggu_verifikasi', 'lunas']));
    }
}
