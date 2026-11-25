<?php

namespace App\Http\Controllers\Web\Peserta;

use App\Actions\Pendaftaran\UnggahDokumen;
use App\Actions\Tagihan\UnggahBukti;
use App\Http\Controllers\Controller;
use App\Http\Requests\Peserta\UnggahBuktiRequest;
use App\Http\Requests\Peserta\UnggahDokumenRequest;
use App\Models\BuktiPembayaran;
use App\Models\Tagihan;
use App\Models\User;
use App\Services\Pengaturan;
use App\Support\Ui\Format;
use App\Support\Ui\PortalContext;
use App\Support\Ui\TampilanPendaftaran;
use App\Support\Ui\TampilanTagihan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** HW-17 daftar tagihan (FR-37) & HW-18 detail + unggah bukti (FR-29, FR-30, BR-36, BR-72). Hanya tagihan milik sendiri (BR-04). */
class TagihanController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $tagihans = Tagihan::where('user_id', $user->id)->berlaku()->orderBy('created_at')->get();
        $lunas = $tagihans->filter(fn (Tagihan $t) => $t->status->getValue() === 'lunas');
        $belum = $tagihans->filter(fn (Tagihan $t) => in_array($t->status->getValue(), ['belum_bayar', 'ditolak'], true));

        return view('peserta.tagihan.index', [
            'portal' => PortalContext::dariPengguna($user, 'Tagihan'),
            'npd' => $user->pesertaDidiks()->latest('id')->value('npd'),
            'ringkasan' => [
                'lunas' => ['nominal' => Format::rupiah((int) $lunas->sum('nominal_total')), 'jumlah' => $lunas->count()],
                'belum' => ['nominal' => Format::rupiah((int) $belum->sum('nominal_total')), 'jumlah' => $belum->count()],
                'menunggu_penetapan' => $tagihans->filter(fn (Tagihan $t) => $t->jenis === 'semester' && $t->jatuh_tempo === null && $t->status->getValue() !== 'lunas')->count(),
            ],
            'tagihan' => $tagihans->map(fn (Tagihan $t) => TampilanTagihan::baris($t))->values()->all(),
        ]);
    }

    public function show(Request $request, Tagihan $tagihan): View
    {
        $this->pastikanPemilik($request, $tagihan);
        $tagihan->load('pendaftaran.peminatanPeriode.peminatan.track', 'pendaftaran.peminatanPeriode.periode', 'pendaftaran.user', 'user', 'buktis');
        TampilanPendaftaran::muat($tagihan->pendaftaran);

        return view('peserta.tagihan.show', [
            'portal' => PortalContext::dariPengguna($request->user(), 'Tagihan'),
            'tagihan' => TampilanTagihan::rincian($tagihan),
            'bukti' => TampilanTagihan::bukti($tagihan, 'tagihan.bukti'),
            'pendaftaran' => TampilanPendaftaran::ringkasan($tagihan->pendaftaran),
            'menungguBerkas' => $tagihan->jenis === 'formulir' && ! in_array($tagihan->pendaftaran->status->getValue(), ['masuk_kuota', 'npd_terbit'], true),
            'batasUnggah' => UnggahDokumenRequest::ukuranTeks(app(Pengaturan::class)->int('ukuran_maks_unggah_kb')),
        ]);
    }

    public function unggah(UnggahBuktiRequest $request, Tagihan $tagihan, UnggahBukti $unggah): RedirectResponse
    {
        $this->pastikanPemilik($request, $tagihan);
        $selisih = $unggah($tagihan, $request->file('berkas'), $request->safe()->only(['tanggal_transfer', 'nominal_transfer', 'bank_pengirim', 'nama_pengirim']));

        return to_route('tagihan.show', $tagihan)
            ->with('status', 'Bukti pembayaran terkirim dan menunggu verifikasi Admin Keuangan.')
            ->with('peringatan', $selisih ? 'Nominal yang Anda isi berbeda dari nominal tagihan ('.Format::rupiah($tagihan->nominal_total).'). Bukti tetap dikirim; Admin Keuangan akan mencocokkan dengan mutasi rekening.' : null);
    }

    /** NFR-07(c), NFR-12: bukti hanya untuk pemilik lewat URL bertanda tangan ≤ 15 menit. */
    public function bukti(Request $request, Tagihan $tagihan, BuktiPembayaran $bukti): StreamedResponse
    {
        $this->pastikanPemilik($request, $tagihan);
        abort_unless($bukti->tagihan_id === $tagihan->id && Storage::disk(UnggahDokumen::DISK)->exists($bukti->path), 404);

        return Storage::disk(UnggahDokumen::DISK)->response($bukti->path, "bukti-{$tagihan->kode_referensi}", [
            'Content-Type' => $bukti->mime, 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store',
        ], 'inline');
    }

    private function pastikanPemilik(Request $request, Tagihan $tagihan): void
    {
        abort_unless($tagihan->user_id === $request->user()?->getAuthIdentifier(), 403); // BR-04, NFR-12
    }
}
