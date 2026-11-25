<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Actions\Tagihan\KoreksiLunas;
use App\Actions\Tagihan\TetapkanJatuhTempo;
use App\Http\Controllers\Controller;
use App\Models\Tagihan;
use App\Models\User;
use App\Support\Ui\Format;
use App\Support\Ui\PortalContext;
use App\Support\Ui\TampilanTagihan;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** HW-42 tagihan tanpa jatuh tempo (FR-57, BR-35) & HW-43 cari & koreksi tagihan (FR-36, BR-40, A-74). Super Admin. */
class TagihanController extends Controller
{
    public const STATUS = ['belum_bayar' => 'Belum Bayar', 'menunggu_verifikasi' => 'Menunggu Verifikasi', 'lunas' => 'Lunas', 'ditolak' => 'Ditolak', 'dibatalkan' => 'Dibatalkan'];

    public function index(Request $request): View
    {
        $filter = [
            'q' => trim((string) $request->query('q', '')),
            'jenis' => in_array($request->query('jenis'), ['formulir', 'semester'], true) ? (string) $request->query('jenis') : '',
            'status' => array_key_exists((string) $request->query('status'), self::STATUS) ? (string) $request->query('status') : '',
        ];
        $halaman = Tagihan::with('pendaftaran')
            ->when($filter['q'] !== '', fn (Builder $q) => $q->where(fn (Builder $w) => $w->where('kode_referensi', 'like', '%'.$filter['q'].'%')
                ->orWhereHas('pendaftaran', fn (Builder $p) => $p->where('nomor_pendaftaran', 'like', '%'.$filter['q'].'%')->orWhere('nama_lengkap', 'like', '%'.$filter['q'].'%'))))
            ->when($filter['jenis'] !== '', fn (Builder $q) => $q->where('jenis', $filter['jenis']))
            ->when($filter['status'] !== '', fn (Builder $q) => $q->where('status', $filter['status']))
            ->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.superadmin.tagihan.index', [
            'portal' => PortalContext::dariPengguna($request->user(), 'Tagihan'),
            'filter' => $filter,
            'baris' => collect($halaman->items())->map(fn (Tagihan $t) => TampilanTagihan::baris($t, 'admin.tagihan.show') + [
                'nama' => (string) $t->pendaftaran->nama_lengkap, 'nomor' => $t->pendaftaran->nomor_pendaftaran,
            ])->all(),
            'statusTersedia' => self::STATUS,
            'total' => $halaman->total(),
            'rentang' => $halaman->total() ? $halaman->firstItem().'–'.$halaman->lastItem() : '0',
            'tautan' => $halaman->linkCollection()->all(),
            'sebelumnya' => $halaman->previousPageUrl(),
            'berikutnya' => $halaman->nextPageUrl(),
        ]);
    }

    public function show(Request $request, Tagihan $tagihan): View
    {
        $tagihan->load('pendaftaran.peminatanPeriode.peminatan.track', 'user', 'buktis');

        return view('admin.superadmin.tagihan.show', [
            'portal' => PortalContext::dariPengguna($request->user(), 'Tagihan'),
            'tagihan' => TampilanTagihan::rincian($tagihan),
            'bukti' => TampilanTagihan::bukti($tagihan, 'admin.verifikasi-pembayaran.bukti'),
            'bisaKoreksi' => $tagihan->status->getValue() === 'lunas',
        ]);
    }

    public function koreksi(Request $request, Tagihan $tagihan, KoreksiLunas $koreksi): RedirectResponse
    {
        /** @var User $sa */
        $sa = $request->user();
        $koreksi($tagihan, $request->input('versi'), (string) $request->input('alasan'), $sa);

        return to_route('admin.tagihan.show', $tagihan)->with('status', 'Status Lunas dikoreksi menjadi Menunggu Verifikasi; Admin Keuangan perlu memverifikasi ulang bukti.');
    }

    public function tanpaJatuhTempo(Request $request): View
    {
        $tagihans = Tagihan::with('pendaftaran.peminatanPeriode.peminatan')->where('jenis', 'semester')->whereNull('jatuh_tempo')
            ->whereIn('status', Tagihan::TERBUKA)->orderBy('id')->get();

        return view('admin.superadmin.tagihan.tanpa-jatuh-tempo', [
            'portal' => PortalContext::dariPengguna($request->user(), 'Tagihan'),
            'baris' => $tagihans->map(fn (Tagihan $t) => [
                'id' => $t->id, 'kode' => $t->kode_referensi, 'pemilik' => (string) $t->pendaftaran->nama_lengkap,
                'peminatan' => $t->pendaftaran->peminatanPeriode->peminatan->nama, 'jenis' => $t->labelJenis(), 'terbit' => Format::tanggal($t->created_at),
            ])->all(),
        ]);
    }

    public function tetapkanJatuhTempo(Request $request, TetapkanJatuhTempo $tetapkan): RedirectResponse
    {
        $data = $request->validate([
            'tagihan' => ['required', 'array', 'min:1'], 'tagihan.*' => ['integer'],
            'jatuh_tempo' => ['required', 'date', 'after:2020-01-01'], 'catatan' => ['nullable', 'string', 'max:255'],
        ], ['tagihan.required' => 'Pilih minimal satu tagihan.', 'jatuh_tempo.required' => 'Isi tanggal jatuh tempo.']);
        /** @var User $sa */
        $sa = $request->user();
        $n = $tetapkan(array_map('intval', $data['tagihan']), $data['jatuh_tempo'], $data['catatan'] ?? null, $sa);

        return back()->with('status', "Jatuh tempo ditetapkan untuk {$n} tagihan.");
    }
}
