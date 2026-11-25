<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Pendaftaran\UnggahDokumen;
use App\Models\BuktiPembayaran;
use App\Models\DokumenVersi;
use App\Models\Pendaftaran;
use App\Models\Tagihan;
use App\Support\Ui\Format;
use App\Support\Ui\TampilanPendaftaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @group Umum
 *
 * Dasbor, notifikasi in-app (tanpa push, K-30), dan tautan berkas bertanda tangan (NFR-07, NFR-12).
 */
class UmumController extends ApiController
{
    /**
     * Ringkasan dasbor
     *
     * Admin SILAU: jumlah antrean berkas. Admin Keuangan: jumlah tagihan Menunggu Verifikasi dan yang siap diputuskan (lolos gerbang ganda).
     */
    public function ringkasan(Request $request): JsonResponse
    {
        $admin = self::admin($request);
        $data = $admin->hasRole('admin_silau')
            ? ['antrean_berkas' => Pendaftaran::untukAdmin()->where('status', 'diajukan')->count()]
            : [
                'antrean_pembayaran' => Tagihan::where('status', 'menunggu_verifikasi')->count(),
                'siap_diputuskan' => Tagihan::where('status', 'menunggu_verifikasi')->lolosGerbang()->count(),
                'lunas_hari_ini' => (int) Tagihan::where('status', 'lunas')->where('lunas_pada', '>=', Format::wib(now())->startOfDay()->utc())->sum('nominal_total'),
            ];

        return $this->ok('Ringkasan dasbor berhasil dimuat.', $data + ['notifikasi_belum_dibaca' => $admin->unreadNotifications()->count()]);
    }

    /**
     * Daftar notifikasi
     *
     * @queryParam page integer Halaman. Example: 1
     * @queryParam per_page integer 1–100, bawaan 20. Example: 20
     */
    public function notifikasi(Request $request): JsonResponse
    {
        $admin = self::admin($request);

        return $this->daftar($admin->notifications()->paginate(self::perHalaman($request)), 'Notifikasi berhasil dimuat.', self::notif(...));
    }

    /** Tandai notifikasi dibaca */
    public function dibaca(Request $request, string $id): JsonResponse
    {
        $n = self::admin($request)->notifications()->whereKey($id)->firstOrFail();
        $n->markAsRead();

        return $this->ok('Notifikasi ditandai dibaca.', self::notif($n));
    }

    /**
     * Tautan berkas bertanda tangan
     *
     * Admin SILAU: `id` = versi dokumen pendaftar. Admin Keuangan: `id` = bukti pembayaran. Tautan berlaku 15 menit.
     *
     * @urlParam id integer required ID versi dokumen (SILAU) atau bukti pembayaran (Keuangan).
     */
    public function tautan(Request $request, int $id): JsonResponse
    {
        $silau = self::admin($request)->hasRole('admin_silau');
        if ($silau) {
            $versi = DokumenVersi::with('dokumen.pendaftaran')->findOrFail($id);
            abort_if($versi->dokumen->pendaftaran->adalahDraf(), 404); // BR-64
        } else {
            BuktiPembayaran::findOrFail($id);
        }
        $kedaluwarsa = now()->addMinutes(TampilanPendaftaran::MENIT_URL);

        return $this->ok('Tautan berkas berhasil dibuat.', [
            'url' => URL::temporarySignedRoute('api.berkas.unduh', $kedaluwarsa, ['jenis' => $silau ? 'dokumen' : 'bukti', 'id' => $id]),
            'kedaluwarsa_pada' => self::iso($kedaluwarsa),
        ]);
    }

    /** @hideFromAPIDocumentation */
    public function unduh(string $jenis, int $id): StreamedResponse
    {
        [$path, $mime, $nama] = $jenis === 'dokumen'
            ? (fn (DokumenVersi $v) => [$v->path, $v->mime, $v->nama_asli])(DokumenVersi::findOrFail($id))
            : (fn (BuktiPembayaran $b) => [$b->path, $b->mime, "bukti-{$b->id}"])(BuktiPembayaran::findOrFail($id));
        abort_unless(Storage::disk(UnggahDokumen::DISK)->exists($path), 404);

        return Storage::disk(UnggahDokumen::DISK)->response($path, $nama, [
            'Content-Type' => $mime, 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store',
        ], 'inline');
    }

    /** @return array<string, mixed> */
    private static function notif(DatabaseNotification $n): array
    {
        return [
            'id' => $n->id,
            'jenis' => $n->data['jenis'] ?? null,
            'judul' => (string) ($n->data['judul'] ?? ''),
            'isi' => (string) ($n->data['isi'] ?? ''),
            'dibaca' => $n->read(),
            'dibuat_pada' => self::iso($n->created_at ?? now()), // sama dengan NotifikasiController web
        ];
    }
}
