<?php

namespace App\Http\Controllers\Web\Peserta;

use App\Actions\Pendaftaran\HapusDokumen;
use App\Actions\Pendaftaran\UnggahDokumen;
use App\Http\Controllers\Controller;
use App\Http\Requests\Peserta\UnggahDokumenRequest;
use App\Models\DokumenVersi;
use App\Models\Pendaftaran;
use App\Services\Pengaturan;
use App\Support\Ui\PortalContext;
use App\Support\Ui\TampilanPendaftaran;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** HW-15 Tab Berkas (unggah/hapus saat Draf), HW-16 (status per dokumen, revisi, riwayat versi), pratinjau berkas. */
class BerkasController extends Controller
{
    public function index(Request $request, Pendaftaran $pendaftaran): View|RedirectResponse
    {
        Gate::authorize('view', $pendaftaran);

        if ($pendaftaran->adalahDraf()) {
            return to_route('pendaftaran.formulir', ['pendaftaran' => $pendaftaran, 'tab' => 'berkas']);
        }

        TampilanPendaftaran::muat($pendaftaran);

        return view('peserta.pendaftaran.berkas', [
            'portal' => PortalContext::dariPengguna($request->user(), 'Status'),
            'pendaftaran' => TampilanPendaftaran::ringkasan($pendaftaran),
            'berkas' => TampilanPendaftaran::berkas($pendaftaran),
            'riwayat' => TampilanPendaftaran::riwayatVersi($pendaftaran),
            'batasUnggah' => UnggahDokumenRequest::ukuranTeks(app(Pengaturan::class)->int('ukuran_maks_unggah_kb')),
        ]);
    }

    public function unggah(UnggahDokumenRequest $request, Pendaftaran $pendaftaran, string $jenis, UnggahDokumen $unggah): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $pendaftaran);
        $dokumen = $unggah($pendaftaran, $jenis, $request->file('berkas'));
        $pesan = $dokumen->label().' berhasil diunggah.';

        // Unggahan dengan indikator progres (XHR, NFR-16) menerima JSON; tanpa JS kembali ke halaman.
        return $request->expectsJson() ? response()->json(['pesan' => $pesan]) : back()->with('status', $pesan);
    }

    public function hapus(Pendaftaran $pendaftaran, string $jenis, HapusDokumen $hapus): RedirectResponse
    {
        Gate::authorize('update', $pendaftaran);
        $hapus($pendaftaran, $jenis);

        return to_route('pendaftaran.formulir', ['pendaftaran' => $pendaftaran, 'tab' => 'berkas'])->with('status', 'Berkas dihapus dari slot.');
    }

    /** NFR-07(c): hanya lewat URL bertanda tangan ≤ 15 menit (middleware `signed`) + pemilik (NFR-12). */
    public function lihat(Pendaftaran $pendaftaran, DokumenVersi $versi): StreamedResponse
    {
        Gate::authorize('view', $pendaftaran);
        abort_unless($versi->dokumen()->where('pendaftaran_id', $pendaftaran->id)->exists(), 404);
        abort_unless(Storage::disk(UnggahDokumen::DISK)->exists($versi->path), 404); // berkas hilang dari disk → 404, bukan 500

        return Storage::disk(UnggahDokumen::DISK)->response($versi->path, $versi->nama_asli, [
            'Content-Type' => $versi->mime,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ], 'inline');
    }
}
