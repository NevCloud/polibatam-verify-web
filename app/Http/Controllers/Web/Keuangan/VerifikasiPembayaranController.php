<?php

namespace App\Http\Controllers\Web\Keuangan;

use App\Actions\Pendaftaran\UnggahDokumen;
use App\Actions\Tagihan\VerifikasiPembayaran;
use App\Http\Controllers\Controller;
use App\Models\BuktiPembayaran;
use App\Models\Tagihan;
use App\Models\User;
use App\Queries\AntreanPembayaranQuery;
use App\Support\Ui\PortalContext;
use App\Support\Ui\TampilanTagihan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** HW-28 antrean & HW-29 detail verifikasi pembayaran (FR-31–FR-35, BR-38, BR-39, BR-61, BR-66). Admin Keuangan & Super Admin. */
class VerifikasiPembayaranController extends Controller
{
    public function index(Request $request, AntreanPembayaranQuery $query): View
    {
        return view('admin.keuangan.verifikasi-pembayaran.index', $query->halaman($request->query()) + [
            'portal' => PortalContext::dariPengguna($request->user(), 'Verifikasi Pembayaran'),
        ]);
    }

    public function show(Request $request, Tagihan $tagihan): View
    {
        abort_if($tagihan->status->getValue() === 'dibatalkan', 404);
        $tagihan->load('pendaftaran.peminatanPeriode.peminatan.track', 'pendaftaran.user', 'user', 'buktis');
        $rincian = TampilanTagihan::rincian($tagihan);
        $bukti = TampilanTagihan::bukti($tagihan, 'admin.verifikasi-pembayaran.bukti');
        $terakhir = $tagihan->buktis->first();

        return view('admin.keuangan.verifikasi-pembayaran.show', [
            'portal' => PortalContext::dariPengguna($request->user(), 'Verifikasi Pembayaran'),
            'tagihan' => $rincian,
            'bukti' => $bukti,
            'gerbang' => $tagihan->lolosGerbang(), // BR-39
            'bisaDiputuskan' => $tagihan->status->getValue() === 'menunggu_verifikasi',
            // Peringatan BR-72: nama pengirim berbeda dari pemilik tagihan
            'pengirimBerbeda' => $terakhir?->nama_pengirim && mb_strtolower(trim($terakhir->nama_pengirim)) !== mb_strtolower(trim($rincian['nama'])),
            'alasanBaku' => VerifikasiPembayaran::ALASAN_BAKU,
        ]);
    }

    public function putuskan(Request $request, Tagihan $tagihan, VerifikasiPembayaran $verifikasi): RedirectResponse
    {
        /** @var User $admin */
        $admin = $request->user();
        $verifikasi($tagihan, $request->input('versi'), (string) $request->input('keputusan'),
            array_map('strval', (array) $request->input('alasan', [])), $request->input('catatan'), $admin);

        return to_route('admin.verifikasi-pembayaran.index')
            ->with('status', "{$tagihan->kode_referensi} ".($tagihan->status->getValue() === 'lunas' ? 'ditandai Lunas.' : 'ditolak; peserta dapat mengunggah ulang bukti.'));
    }

    /** NFR-07(c), NFR-12: bukti untuk Admin Keuangan & Super Admin lewat URL bertanda tangan ≤ 15 menit. */
    public function bukti(Tagihan $tagihan, BuktiPembayaran $bukti): StreamedResponse
    {
        abort_unless($bukti->tagihan_id === $tagihan->id && Storage::disk(UnggahDokumen::DISK)->exists($bukti->path), 404);

        return Storage::disk(UnggahDokumen::DISK)->response($bukti->path, "bukti-{$tagihan->kode_referensi}", [
            'Content-Type' => $bukti->mime, 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store',
        ], 'inline');
    }
}
