<?php

namespace App\Actions\Tagihan;

use App\Actions\Pendaftaran\UnggahDokumen;
use App\Models\BuktiPembayaran;
use App\Models\Tagihan;
use App\Services\AuditLogger;
use App\States\Tagihan\MenungguVerifikasi;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * FR-29, FR-30, BR-36, BR-37, BR-72: unggah bukti transfer + data pengiriman → tagihan Menunggu Verifikasi.
 * Hanya untuk Belum Bayar/Ditolak; file ber-hash sama dengan bukti mana pun ditolak; selisih nominal hanya peringatan.
 */
class UnggahBukti
{
    public const PESAN_DUPLIKAT = 'Bukti ini sudah pernah digunakan. Unggah bukti transfer yang lain.';

    public function __construct(private AuditLogger $audit) {}

    /**
     * @param  array{tanggal_transfer: string, nominal_transfer: int, bank_pengirim: string, nama_pengirim: string}  $kirim
     * @return bool true bila nominal yang ditransfer berbeda dari nominal tagihan (peringatan BR-72)
     */
    public function __invoke(Tagihan $tagihan, UploadedFile $berkas, array $kirim): bool
    {
        $sha = hash_file('sha256', $berkas->getRealPath());
        if (BuktiPembayaran::where('sha256', $sha)->exists()) {
            throw ValidationException::withMessages(['berkas' => self::PESAN_DUPLIKAT]); // AC-06.4
        }

        $ekstensi = $berkas->guessExtension() ?: $berkas->extension();
        $path = Storage::disk(UnggahDokumen::DISK)->putFileAs("bukti/{$tagihan->id}", $berkas, Str::random(40).'.'.$ekstensi);

        try {
            return DB::transaction(function () use ($tagihan, $berkas, $kirim, $sha, $path) {
                $t = Tagihan::whereKey($tagihan->id)->lockForUpdate()->firstOrFail();
                if (! in_array($t->status->getValue(), ['belum_bayar', 'ditolak'], true)) {
                    throw ValidationException::withMessages(['berkas' => 'Bukti hanya dapat diunggah untuk tagihan berstatus Belum Bayar atau Ditolak.']); // BR-36
                }

                $bukti = BuktiPembayaran::create($kirim + [
                    'tagihan_id' => $t->id, 'path' => $path, 'mime' => (string) $berkas->getMimeType(), 'ukuran' => (int) $berkas->getSize(),
                    'sha256' => $sha, 'diunggah_pada' => now(), 'keputusan' => 'menunggu',
                ]);
                $t->status->transitionTo(MenungguVerifikasi::class);

                $selisih = (int) $kirim['nominal_transfer'] !== $t->nominal_total;
                $this->audit->catat('unggah_bukti', 'tagihan', $t->id, sesudah: ['bukti' => $bukti->id, 'selisih_nominal' => $selisih]);

                return $selisih;
            });
        } catch (Throwable $e) {
            Storage::disk(UnggahDokumen::DISK)->delete($path);

            throw $e;
        }
    }
}
