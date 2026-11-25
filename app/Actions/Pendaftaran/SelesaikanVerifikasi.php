<?php

namespace App\Actions\Pendaftaran;

use App\Exceptions\SudahDiverifikasi;
use App\Models\Dokumen;
use App\Models\Pendaftaran;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\Notifier;
use App\States\Pendaftaran\MasukKuota;
use App\States\Pendaftaran\PerluRevisi;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * FR-21, BR-17 (K-42), AC-10.4: setelah semua dokumen berkas diputuskan →
 * ada yang Ditolak = Perlu Revisi; semua Valid = Masuk Kuota (tanpa batas jumlah).
 */
class SelesaikanVerifikasi
{
    public function __construct(private AuditLogger $audit, private Notifier $notifier) {}

    public function __invoke(Pendaftaran $pendaftaran, User $admin): Pendaftaran
    {
        return DB::transaction(function () use ($pendaftaran, $admin) {
            $p = Pendaftaran::whereKey($pendaftaran->id)->lockForUpdate()->firstOrFail();
            if ($p->status->getValue() !== 'diajukan') {
                throw new SudahDiverifikasi; // admin lain sudah menyelesaikan (BR-61)
            }

            $dokumens = Dokumen::where('pendaftaran_id', $p->id)->whereNotNull('versi_aktif_id')->get();
            $belum = $dokumens->filter(fn (Dokumen $d) => $d->status->getValue() === 'menunggu_verifikasi');
            if ($belum->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'selesai' => 'Masih ada dokumen yang belum diputuskan: '.$belum->map->label()->implode(', ').'.',
                ]);
            }

            $ditolak = $dokumens->filter(fn (Dokumen $d) => $d->status->getValue() === 'ditolak');
            if ($ditolak->isNotEmpty()) {
                $p->status->transitionTo(PerluRevisi::class);
                $judul = 'Berkas perlu diperbaiki';
                $isi = "Admin SILAU menolak {$ditolak->map->label()->implode(', ')} pada pendaftaran {$p->nomor_pendaftaran}. Lihat alasannya lalu unggah ulang dokumen tersebut.";
                $url = route('pendaftaran.berkas', $p);
            } else {
                $p->forceFill(['waktu_masuk_kuota' => now()]);
                $p->status->transitionTo(MasukKuota::class);
                $judul = 'Berkas valid · Masuk Kuota';
                $isi = "Seluruh berkas pendaftaran {$p->nomor_pendaftaran} valid. Lunasi tagihan formulir untuk mendapatkan NPD.";
                $url = route('status');
            }

            $this->audit->catat('selesaikan_verifikasi', 'pendaftaran', $p->id,
                sebelum: ['status' => 'diajukan'], sesudah: ['status' => $p->status->getValue(), 'ditolak' => $ditolak->pluck('jenis')->all()], pelaku: $admin);
            $this->notifier->kirim($p->user, 'status_pendaftaran', $judul, $isi, $url); // AC-07.2

            return $p;
        });
    }
}
