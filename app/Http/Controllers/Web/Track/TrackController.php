<?php

namespace App\Http\Controllers\Web\Track;

use App\Actions\Master\KelolaTrack;
use App\Http\Controllers\Controller;
use App\Models\Track;
use App\Models\User;
use App\Support\Ui\PortalContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** HW-33 (FR-45, BR-52, BR-53). Admin Track & Super Admin (RBAC: Track & peminatan CRU). */
class TrackController extends Controller
{
    public const STATUS = ['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif'];

    public function index(Request $request): View
    {
        $filter = [
            'q' => trim((string) $request->query('q', '')),
            'status' => array_key_exists((string) $request->query('status'), self::STATUS) ? (string) $request->query('status') : '',
        ];
        $semua = Track::withCount('peminatans')->orderBy('nama')->get();
        $tampil = $semua
            ->when($filter['q'] !== '', fn ($c) => $c->filter(fn (Track $t) => str_contains(mb_strtolower($t->kode.' '.$t->nama), mb_strtolower($filter['q']))))
            ->when($filter['status'] !== '', fn ($c) => $c->where('is_active', $filter['status'] === 'aktif'));

        return view('admin.track.track.index', [
            'portal' => PortalContext::dariPengguna($request->user(), 'Track'),
            'filter' => $filter,
            'track' => $tampil->map(fn (Track $t) => [
                'id' => $t->id, 'kode' => $t->kode, 'nama' => $t->nama, 'aktif' => $t->is_active,
                'peminatan' => (int) $t->getAttribute('peminatans_count'), 'bisa_hapus' => (int) $t->getAttribute('peminatans_count') === 0,
            ])->values()->all(),
            'jumlah' => ['total' => $semua->count(), 'aktif' => $semua->where('is_active', true)->count()],
            'statusTersedia' => self::STATUS,
        ]);
    }

    public function store(Request $request, KelolaTrack $kelola): RedirectResponse
    {
        $kelola->simpan($this->validasi($request, null), null, $this->admin($request));

        return back()->with('status', 'Track ditambahkan.');
    }

    public function update(Request $request, Track $track, KelolaTrack $kelola): RedirectResponse
    {
        $kelola->simpan($this->validasi($request, $track), $track, $this->admin($request));

        return back()->with('status', "Track {$track->kode} diperbarui.");
    }

    public function status(Request $request, Track $track, KelolaTrack $kelola): RedirectResponse
    {
        $aktif = $request->boolean('aktif');
        $kelola->ubahStatus($track, $aktif, $this->admin($request));

        return back()->with('status', $aktif ? "Track {$track->nama} diaktifkan." : "Track {$track->nama} dinonaktifkan; peminatannya tidak tampil di katalog.");
    }

    public function destroy(Request $request, Track $track, KelolaTrack $kelola): RedirectResponse
    {
        $kelola->hapus($track, $this->admin($request));

        return back()->with('status', "Track {$track->nama} dihapus.");
    }

    /** @return array{kode: string, nama: string} */
    private function validasi(Request $request, ?Track $track): array
    {
        $request->merge(['kode' => mb_strtoupper(trim((string) $request->input('kode')))]);

        /** @var array{kode: string, nama: string} */
        return $request->validateWithBag($track ? "track-{$track->id}" : 'track', [
            'kode' => ['required', 'regex:/^[A-Z0-9-]{1,20}$/', Rule::unique('tracks', 'kode')->ignore($track?->id)],
            'nama' => ['required', 'string', 'max:100'],
        ], [
            'kode.regex' => 'Kode hanya huruf, angka, atau tanda hubung (maks. 20).',
            'kode.unique' => 'Kode track sudah digunakan.',
        ]);
    }

    private function admin(Request $request): User
    {
        /** @var User */
        return $request->user();
    }
}
