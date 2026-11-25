<?php

namespace App\Actions\Tagihan;

use App\Models\BuktiPembayaran;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\CekVersiData;
use App\Services\Notifier;
use App\States\Tagihan\MenungguVerifikasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * FR-36, BR-40, A-74: koreksi tagihan Lunas oleh Super Admin = kembali ke Menunggu Verifikasi (bukti dicek ulang
 * Admin Keuangan), alasan wajib, audit `koreksi_lunas`. NPD/status peserta yang sudah terbit tidak dibatalkan (A-88).
 */
class KoreksiLunas
{
    public function __construct(private CekVersiData $versi, private AuditLogger $audit, private Notifier $notifier) {}

    public function __invoke(Tagihan $tagihan, ?string $versi, string $alasan, User $superAdmin): Tagihan
    {
        $alasan = trim($alasan);
        if (mb_strlen($alasan) < 10) {
            throw ValidationException::withMessages(['alasan' => 'Alasan koreksi wajib diisi (minimal 10 karakter) dan akan tercatat di audit log.']);
        }

        return DB::transaction(function () use ($tagihan, $versi, $alasan, $superAdmin) {
            $this->versi->transisi($tagihan, MenungguVerifikasi::class, $versi, ['lunas_pada' => null]);
            BuktiPembayaran::where('tagihan_id', $tagihan->id)->latest('id')->first()
                ?->update(['keputusan' => 'menunggu', 'diverifikasi_oleh' => null, 'diverifikasi_pada' => null]);
            $this->audit->catat('koreksi_lunas', 'tagihan', $tagihan->id, sebelum: ['status' => 'lunas'], sesudah: ['status' => 'menunggu_verifikasi'],
                alasan: $alasan, pelaku: $superAdmin);

            $this->notifier->kirim($tagihan->user, 'tagihan', 'Status pembayaran dikoreksi',
                "Pembayaran {$tagihan->kode_referensi} dikembalikan ke Menunggu Verifikasi oleh Super Admin: {$alasan}", route('tagihan.show', $tagihan));
            $this->notifier->kirimKePeran(['admin_keuangan'], 'tagihan', 'Koreksi Lunas perlu diverifikasi ulang',
                "Tagihan {$tagihan->kode_referensi} dikoreksi Super Admin ke Menunggu Verifikasi: {$alasan}", route('admin.verifikasi-pembayaran.show', $tagihan));

            return $tagihan;
        });
    }
}
