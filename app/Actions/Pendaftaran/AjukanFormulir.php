<?php

namespace App\Actions\Pendaftaran;

use App\Events\FormulirDiajukan;
use App\Models\PeminatanPeriode;
use App\Models\Pendaftaran;
use App\Services\AuditLogger;
use App\Services\Notifier;
use App\States\Pendaftaran\Diajukan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * HW-15 Tab Tinjau & Ajukan (FR-12, FR-13, BR-07, AC-04.2, AC-04.3).
 * Draf → Diajukan bila semua kolom & dokumen wajib lengkap dan periode/peminatan masih terbuka.
 * Memicu FormulirDiajukan (P05 menerbitkan tagihan formulir, FR-26).
 */
class AjukanFormulir
{
    /** Kolom di tabel → nama input formulir (untuk menandai field yang belum lengkap). */
    private const INPUT = ['nik_terenkripsi' => 'nik'];

    public function __construct(private AuditLogger $audit, private Notifier $notifier) {}

    public function __invoke(Pendaftaran $pendaftaran): Pendaftaran
    {
        return DB::transaction(function () use ($pendaftaran) {
            $terkunci = Pendaftaran::whereKey($pendaftaran->id)->lockForUpdate()->with('dokumens', 'peminatanPeriode.peminatan')->firstOrFail();
            SimpanDraf::pastikanDraf($terkunci);

            $kurang = $terkunci->kekurangan();
            if ($kurang['data'] || $kurang['berkas']) {
                $pesan = [];
                foreach ($kurang['data'] as $kolom => $label) {
                    $pesan[self::INPUT[$kolom] ?? $kolom] = "{$label} wajib diisi sebelum formulir diajukan.";
                }
                foreach ($kurang['berkas'] as $jenis => $label) {
                    $pesan["berkas.{$jenis}"] = "Unggah {$label} (dokumen wajib) sebelum formulir diajukan.";
                }

                throw ValidationException::withMessages($pesan);
            }

            if (! PeminatanPeriode::terbuka()->whereKey($terkunci->peminatan_periode_id)->exists()) {
                throw ValidationException::withMessages([
                    'formulir' => 'Formulir tidak dapat diajukan: periode pendaftaran sudah ditutup atau peminatan tidak lagi tersedia.',
                ]);
            }

            $terkunci->forceFill([
                'waktu_pengajuan' => now(),
                'durasi_semester_snapshot' => $terkunci->peminatanPeriode->peminatan->durasi_semester, // BR-43
            ]);
            $terkunci->status->transitionTo(Diajukan::class);

            $this->audit->catat('ajukan_formulir', 'pendaftaran', $terkunci->id, sebelum: ['status' => 'draf'], sesudah: ['status' => 'diajukan']);
            $this->notifier->kirim($terkunci->user, 'status_pendaftaran', 'Formulir berhasil diajukan',
                "Formulir {$terkunci->nomor_pendaftaran} berhasil diajukan dan menunggu verifikasi berkas oleh Admin SILAU.",
                route('status'));

            FormulirDiajukan::dispatch($terkunci);

            return $terkunci;
        });
    }
}
