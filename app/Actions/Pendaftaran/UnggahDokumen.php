<?php

namespace App\Actions\Pendaftaran;

use App\Models\Dokumen;
use App\Models\DokumenVersi;
use App\Models\Pendaftaran;
use App\Services\AuditLogger;
use App\Services\Notifier;
use App\States\Dokumen\Ditolak;
use App\States\Dokumen\MenungguVerifikasi;
use App\States\Pendaftaran\Diajukan;
use App\States\Pendaftaran\PerluRevisi;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * FR-14, FR-16, FR-17, BR-12, BR-14, NFR-07.
 * - Draf: unggah/ganti berkas pada slot mana pun (AC-05.4); versi lama tetap di riwayat.
 * - Perlu Revisi: hanya dokumen Ditolak (AC-08.2/08.3). Setelah tidak ada lagi dokumen Ditolak,
 *   pendaftaran kembali Diajukan (FR-16) dengan waktu pengajuan awal (FIFO BR-18 tidak berubah).
 * Validasi format/ukuran (magic bytes) di UnggahDokumenRequest; berkas di disk `private`, nama acak.
 */
class UnggahDokumen
{
    public const DISK = 'private';

    public function __construct(private AuditLogger $audit, private Notifier $notifier) {}

    public function __invoke(Pendaftaran $pendaftaran, string $jenis, UploadedFile $berkas): Dokumen
    {
        $info = Dokumen::JENIS[$jenis] ?? throw ValidationException::withMessages(['berkas' => 'Jenis dokumen tidak dikenal.']);
        $ekstensi = $berkas->guessExtension() ?: $berkas->extension(); // dari isi berkas, bukan nama
        $path = Storage::disk(self::DISK)->putFileAs("pendaftaran/{$pendaftaran->id}", $berkas, Str::random(40).'.'.$ekstensi);

        try {
            return DB::transaction(function () use ($pendaftaran, $jenis, $info, $berkas, $path) {
                $terkunci = Pendaftaran::whereKey($pendaftaran->id)->lockForUpdate()->firstOrFail();
                $revisi = $terkunci->status->equals(PerluRevisi::class);
                $dokumen = Dokumen::where('pendaftaran_id', $terkunci->id)->where('jenis', $jenis)->first();

                if (! $terkunci->adalahDraf() && ! ($revisi && $dokumen?->status->equals(Ditolak::class))) {
                    throw ValidationException::withMessages(['berkas' => $revisi
                        ? 'Hanya dokumen berstatus Ditolak yang dapat diunggah ulang. Dokumen Valid terkunci.'
                        : 'Berkas tidak dapat diubah setelah formulir diajukan.']);
                }

                $dokumen ??= Dokumen::create([
                    'pendaftaran_id' => $terkunci->id, 'jenis' => $jenis, 'wajib' => $info['wajib'], 'status' => MenungguVerifikasi::class,
                ]);

                $versi = DokumenVersi::create([
                    'dokumen_id' => $dokumen->id,
                    'path' => $path,
                    'nama_asli' => mb_substr($berkas->getClientOriginalName(), 0, 255),
                    'mime' => (string) $berkas->getMimeType(),
                    'ukuran' => (int) $berkas->getSize(),
                    'sha256' => hash_file('sha256', $berkas->getRealPath()),
                    'diunggah_pada' => now(),
                ]);

                $dokumen->versi_aktif_id = $versi->id;
                $revisi ? $dokumen->status->transitionTo(MenungguVerifikasi::class) : $dokumen->save();

                if ($revisi) {
                    $this->audit->catat('unggah_ulang_dokumen', 'dokumen', $dokumen->id, sesudah: ['versi' => $versi->id, 'jenis' => $jenis]);
                    $this->ajukanUlangBilaSelesai($terkunci);
                }

                return $dokumen;
            });
        } catch (Throwable $e) {
            Storage::disk(self::DISK)->delete($path); // jangan tinggalkan berkas yatim bila DB gagal

            throw $e;
        }
    }

    private function ajukanUlangBilaSelesai(Pendaftaran $pendaftaran): void
    {
        if (Dokumen::where('pendaftaran_id', $pendaftaran->id)->where('status', Ditolak::getMorphClass())->exists()) {
            return;
        }

        $pendaftaran->status->transitionTo(Diajukan::class);
        $this->audit->catat('ajukan_ulang_berkas', 'pendaftaran', $pendaftaran->id, sesudah: ['status' => 'diajukan']);
        $this->notifier->kirim($pendaftaran->user, 'status_pendaftaran', 'Berkas revisi diajukan ulang',
            "Berkas revisi pendaftaran {$pendaftaran->nomor_pendaftaran} sudah diajukan ulang dan menunggu verifikasi Admin SILAU.",
            route('status'));
    }
}
