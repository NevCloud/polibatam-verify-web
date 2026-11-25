<?php

namespace App\Http\Controllers\Web\Peserta;

use App\Actions\Pendaftaran\AjukanFormulir;
use App\Actions\Pendaftaran\PilihPeminatan;
use App\Actions\Pendaftaran\SimpanDraf;
use App\Http\Controllers\Controller;
use App\Http\Requests\Peserta\SimpanDrafRequest;
use App\Http\Requests\Peserta\UnggahDokumenRequest;
use App\Models\PeminatanPeriode;
use App\Models\Pendaftaran;
use App\Models\User;
use App\Services\Pengaturan;
use App\Support\Ui\Format;
use App\Support\Ui\PortalContext;
use App\Support\Ui\TampilanPendaftaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/** HW-14 (pilih peminatan), HW-15 (formulir 3 tab), kartu pendaftar PDF (FR-68, BR-73, A-63). */
class PendaftaranController extends Controller
{
    /** Menu "Formulir": pendaftaran yang sedang berjalan, atau pilih peminatan bila belum ada. */
    public function saya(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $p = Pendaftaran::where('user_id', $user->id)->belumAkhir()->latest('id')->first();

        return $p ? to_route('pendaftaran.formulir', $p) : to_route('pendaftaran.baru');
    }

    public function pilih(Request $request): View|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $aktif = Pendaftaran::where('user_id', $user->id)->belumAkhir()->latest('id')->first();

        if ($aktif && ! $aktif->adalahDraf()) {
            return to_route('pendaftaran.formulir', $aktif); // BR-09: pilihan terkunci setelah Diajukan
        }

        $katalog = PeminatanPeriode::terbuka()->with('peminatan.track', 'periode')->get();
        $tracks = [];
        foreach ($katalog->sortBy([['peminatan.track.nama', 'asc'], ['peminatan.nama', 'asc']]) as $pp) {
            $t = $pp->peminatan->track;
            $tracks[$t->kode] ??= ['kode' => $t->kode, 'nama' => $t->nama, 'peminatan' => []];
            $tracks[$t->kode]['peminatan'][] = [
                'id' => $pp->id, 'nama' => $pp->peminatan->nama, 'durasi' => $pp->peminatan->durasi_semester, // BR-75: tanpa info kuota
            ];
        }
        $periode = $katalog->first()?->periode;

        return view('peserta.pendaftaran.pilih-peminatan', [
            'portal' => PortalContext::dariPengguna($user, 'Formulir'),
            'tracks' => array_values($tracks),
            'periode' => $periode ? $periode->nama.' · tutup '.Format::tanggal($periode->tanggal_selesai) : null,
            'draf' => $aktif ? ['nomor' => $aktif->nomor_pendaftaran, 'pilihan' => $aktif->peminatan_periode_id] : null,
        ]);
    }

    public function simpanPilihan(Request $request, PilihPeminatan $pilih): RedirectResponse
    {
        $data = $request->validate(['peminatan_periode_id' => ['required', 'integer']], [
            'peminatan_periode_id.required' => 'Pilih track dan peminatan terlebih dahulu.',
        ]);
        /** @var User $user */
        $user = $request->user();
        $p = $pilih($user, (int) $data['peminatan_periode_id']);

        return to_route('pendaftaran.formulir', $p)->with('status', "Draf tersimpan. Nomor pendaftaran Anda: {$p->nomor_pendaftaran}.");
    }

    public function formulir(Request $request, Pendaftaran $pendaftaran): View
    {
        Gate::authorize('view', $pendaftaran);
        TampilanPendaftaran::muat($pendaftaran);
        $kurang = $pendaftaran->kekurangan();
        // AC-04.3: setelah Ajukan ditolak, buka tab pertama yang bermasalah.
        $kunci = $request->session()->get('errors')?->getBag('default')->keys() ?? [];
        $galatBerkas = array_filter($kunci, fn (string $k) => str_starts_with($k, 'berkas')) !== [];
        $galatData = array_filter($kunci, fn (string $k) => ! str_starts_with($k, 'berkas') && $k !== 'formulir') !== [];

        return view('peserta.pendaftaran.formulir', [
            'portal' => PortalContext::dariPengguna($request->user(), 'Formulir'),
            'pendaftaran' => TampilanPendaftaran::ringkasan($pendaftaran),
            'data' => [
                'nik' => $pendaftaran->nik_terenkripsi, // pemilik melihat NIK-nya sendiri utuh di formulir
                'nama_lengkap' => $pendaftaran->nama_lengkap,
                'tempat_lahir' => $pendaftaran->tempat_lahir,
                'tanggal_lahir' => $pendaftaran->tanggal_lahir?->toDateString(),
                'jenis_kelamin' => $pendaftaran->jenis_kelamin,
                'no_hp' => $pendaftaran->no_hp,
                'alamat' => $pendaftaran->alamat,
                'asal_sekolah' => $pendaftaran->asal_sekolah,
                'tahun_lulus' => $pendaftaran->tahun_lulus,
            ],
            'berkas' => TampilanPendaftaran::berkas($pendaftaran),
            'tinjau' => TampilanPendaftaran::dataDiri($pendaftaran),
            'kekurangan' => $kurang,
            'terisi' => (count(Pendaftaran::KOLOM_WAJIB) - count($kurang['data'])).' / '.count(Pendaftaran::KOLOM_WAJIB).' terisi',
            'tab' => match (true) {
                $galatData => 'data',
                $galatBerkas => 'berkas',
                in_array($request->query('tab'), ['data', 'berkas', 'tinjau'], true) => $request->query('tab'),
                default => 'data',
            },
            'batasUnggah' => UnggahDokumenRequest::ukuranTeks(app(Pengaturan::class)->int('ukuran_maks_unggah_kb')),
            'disimpan' => Format::tanggalJam($pendaftaran->updated_at),
        ]);
    }

    public function simpan(SimpanDrafRequest $request, Pendaftaran $pendaftaran, SimpanDraf $simpan): RedirectResponse
    {
        Gate::authorize('update', $pendaftaran);
        $simpan($pendaftaran, $request->validated());

        return to_route('pendaftaran.formulir', ['pendaftaran' => $pendaftaran, 'tab' => $request->input('tab_tujuan', 'data')])
            ->with('status', 'Draf tersimpan.');
    }

    /** Tombol Ajukan mengirim isian Data Diri sekaligus: simpan dulu, lalu ajukan (AC-04.2, AC-04.3). */
    public function ajukan(SimpanDrafRequest $request, Pendaftaran $pendaftaran, SimpanDraf $simpan, AjukanFormulir $ajukan): RedirectResponse
    {
        Gate::authorize('update', $pendaftaran);
        $simpan($pendaftaran, $request->validated());
        $ajukan($pendaftaran);

        return to_route('status')->with('status', "Formulir {$pendaftaran->nomor_pendaftaran} berhasil diajukan.");
    }

    /** A-63: dibangkitkan dari data terkini setiap kali diunduh, tanpa QR, tanpa berkas tersimpan. */
    public function kartu(Pendaftaran $pendaftaran): Response
    {
        Gate::authorize('unduhKartu', $pendaftaran);
        TampilanPendaftaran::muat($pendaftaran);

        return Pdf::loadView('pdf.kartu-pendaftar', [
            'kartu' => TampilanPendaftaran::ringkasan($pendaftaran),
            'diajukan' => $pendaftaran->waktu_pengajuan ? Format::tanggalJam($pendaftaran->waktu_pengajuan) : '—',
            'dicetak' => Format::tanggalJam(now()),
        ])->download("kartu-pendaftar-{$pendaftaran->nomor_pendaftaran}.pdf");
    }
}
