<?php

namespace App\Http\Controllers\Web\Track;

use App\Actions\Master\KelolaPeminatan;
use App\Http\Controllers\Controller;
use App\Models\Peminatan;
use App\Models\Track;
use App\Models\User;
use App\Support\Ui\PortalContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** HW-34 (FR-46, BR-43, BR-52, BR-53). Admin Track & Super Admin. Kuota per periode ada di HW-36. */
class PeminatanController extends Controller
{
    public function index(Request $request): View
    {
        $tracks = Track::orderBy('nama')->get(['id', 'kode', 'nama', 'is_active']);
        $filter = [
            'q' => trim((string) $request->query('q', '')),
            'track' => $tracks->contains('id', (int) $request->query('track')) ? (string) (int) $request->query('track') : '',
            'status' => array_key_exists((string) $request->query('status'), TrackController::STATUS) ? (string) $request->query('status') : '',
        ];
        // BR-53: dipakai = ada pendaftaran (lewat peminatan_periodes) atau sudah bertarif.
        $dipakai = DB::table('pendaftarans')->join('peminatan_periodes as pp', 'pp.id', '=', 'pendaftarans.peminatan_periode_id')
            ->distinct()->pluck('pp.peminatan_id')->merge(DB::table('tarifs')->distinct()->pluck('peminatan_id'))->unique()->all();

        $semua = Peminatan::with('track')->orderBy('nama')->get();
        $tampil = $semua
            ->when($filter['q'] !== '', fn ($c) => $c->filter(fn (Peminatan $p) => str_contains(mb_strtolower($p->kode.' '.$p->nama), mb_strtolower($filter['q']))))
            ->when($filter['track'] !== '', fn ($c) => $c->where('track_id', (int) $filter['track']))
            ->when($filter['status'] !== '', fn ($c) => $c->where('is_active', $filter['status'] === 'aktif'));

        return view('admin.track.peminatan.index', [
            'portal' => PortalContext::dariPengguna($request->user(), 'Peminatan'),
            'filter' => $filter,
            'peminatan' => $tampil->sortBy(fn (Peminatan $p) => $p->track->nama.'|'.$p->nama)->map(fn (Peminatan $p) => [
                'id' => $p->id, 'kode' => $p->kode, 'nama' => $p->nama, 'track_id' => $p->track_id, 'track' => $p->track->nama,
                'durasi' => $p->durasi_semester, 'aktif' => $p->is_active, 'bisa_hapus' => ! in_array($p->id, $dipakai, true),
                'tarif' => route('admin.tarif.show', $p->kode),
            ])->values()->all(),
            'jumlah' => ['total' => $semua->count(), 'aktif' => $semua->where('is_active', true)->count(), 'track' => $tracks->count()],
            'trackTersedia' => $tracks->mapWithKeys(fn (Track $t) => [$t->id => $t->kode.' · '.$t->nama.($t->is_active ? '' : ' (nonaktif)')])->all(),
            'statusTersedia' => TrackController::STATUS,
        ]);
    }

    public function store(Request $request, KelolaPeminatan $kelola): RedirectResponse
    {
        $kelola->simpan($this->validasi($request, null), null, $this->admin($request));

        return back()->with('status', 'Peminatan ditambahkan. Atur tarif dan kuota periodenya agar tampil di katalog.');
    }

    public function update(Request $request, Peminatan $peminatan, KelolaPeminatan $kelola): RedirectResponse
    {
        $kelola->simpan($this->validasi($request, $peminatan), $peminatan, $this->admin($request));

        return back()->with('status', "Peminatan {$peminatan->nama} diperbarui. Perubahan durasi hanya berlaku untuk pendaftar berikutnya.");
    }

    public function status(Request $request, Peminatan $peminatan, KelolaPeminatan $kelola): RedirectResponse
    {
        $aktif = $request->boolean('aktif');
        $kelola->ubahStatus($peminatan, $aktif, $this->admin($request));

        return back()->with('status', $aktif ? "Peminatan {$peminatan->nama} diaktifkan." : "Peminatan {$peminatan->nama} dinonaktifkan; tidak tampil di katalog, peserta lama tetap berjalan.");
    }

    public function destroy(Request $request, Peminatan $peminatan, KelolaPeminatan $kelola): RedirectResponse
    {
        $kelola->hapus($peminatan, $this->admin($request));

        return back()->with('status', "Peminatan {$peminatan->nama} dihapus.");
    }

    /** @return array{track_id: int, kode: string, nama: string, durasi_semester: int} */
    private function validasi(Request $request, ?Peminatan $peminatan): array
    {
        $request->merge(['kode' => mb_strtoupper(trim((string) $request->input('kode')))]);

        /** @var array{track_id: int, kode: string, nama: string, durasi_semester: int} */
        return $request->validateWithBag($peminatan ? "peminatan-{$peminatan->id}" : 'peminatan', [
            'track_id' => ['required', 'integer', Rule::exists('tracks', 'id')],
            'kode' => ['required', 'regex:/^[A-Z0-9-]{1,20}$/', Rule::unique('peminatans', 'kode')->ignore($peminatan?->id)],
            'nama' => ['required', 'string', 'max:150'],
            'durasi_semester' => ['required', 'integer', 'min:1', 'max:20'], // BR-52
        ], [
            'kode.regex' => 'Kode hanya huruf, angka, atau tanda hubung (maks. 20).',
            'kode.unique' => 'Kode peminatan sudah digunakan.',
            'durasi_semester.min' => 'Durasi semester minimal 1.',
        ], ['track_id' => 'track', 'durasi_semester' => 'durasi semester']);
    }

    private function admin(Request $request): User
    {
        /** @var User */
        return $request->user();
    }
}
