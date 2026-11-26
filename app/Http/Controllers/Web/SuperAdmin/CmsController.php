<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Actions\Cms\KelolaKontenCms;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Publik\PublikController;
use App\Models\KontenCms;
use App\Models\User;
use App\Queries\KatalogQuery;
use App\Support\Ui\Format;
use App\Support\Ui\PortalContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** HW-46 (FR-10, BR-55, AC-29.1–29.4): CMS landing page, hanya Super Admin (middleware role). */
class CmsController extends Controller
{
    public function index(Request $request): View
    {
        $baris = KontenCms::all()->groupBy('bagian');
        $aktif = array_key_exists((string) $request->query('bagian'), KontenCms::BAGIAN) ? (string) $request->query('bagian') : 'pengumuman';

        return view('admin.superadmin.cms.index', [
            'portal' => PortalContext::dariPengguna($request->user(), 'CMS Landing Page'),
            'aktif' => $aktif,
            'bagian' => collect(KontenCms::BAGIAN)->map(function (array $b, string $kunci) use ($baris) {
                $draf = $baris->get($kunci)?->firstWhere('status', 'draf');
                $terbit = $baris->get($kunci)?->firstWhere('status', 'terbit');
                $bawaan = KatalogQuery::KONTEN_BAWAAN[$kunci];

                return $b + [
                    'judul' => $draf->judul ?? $terbit->judul ?? $bawaan[0],
                    'isi' => $draf->isi ?? $terbit->isi ?? $bawaan[1],
                    'status' => match (true) {
                        $terbit === null => 'Belum diterbitkan (memakai isi bawaan)',
                        $draf !== null && ($draf->judul !== $terbit->judul || $draf->isi !== $terbit->isi) => 'Ada perubahan draf belum diterbitkan',
                        default => 'Terbit',
                    },
                    'terbit_pada' => $terbit?->diterbitkan_pada ? Format::tanggalJam($terbit->diterbitkan_pada) : null,
                    'bisa_terbit' => $draf !== null,
                ];
            })->all(),
        ]);
    }

    public function simpan(Request $request, KelolaKontenCms $kelola): RedirectResponse
    {
        $data = $request->validate([
            'bagian' => ['required', Rule::in(array_keys(KontenCms::BAGIAN))],
            'judul' => ['required', 'string', 'max:200'],
            'isi' => ['required', 'string', 'max:5000'],
        ]);
        $kelola->simpanDraf($data['bagian'], $data['judul'], $data['isi'], $this->admin($request));

        return to_route('admin.cms.index', ['bagian' => $data['bagian']])
            ->with('status', 'Draf '.mb_strtolower(KontenCms::BAGIAN[$data['bagian']]['label']).' tersimpan. Pratinjau atau terbitkan agar tampil di beranda.');
    }

    public function terbitkan(Request $request, KelolaKontenCms $kelola): RedirectResponse
    {
        $bagian = (string) $request->validate(['bagian' => ['required', Rule::in(array_keys(KontenCms::BAGIAN))]])['bagian'];
        $kelola->terbitkan($bagian, $this->admin($request));

        return to_route('admin.cms.index', ['bagian' => $bagian])
            ->with('status', KontenCms::BAGIAN[$bagian]['label'].' diterbitkan dan langsung tampil di beranda.');
    }

    /** AC-29.2: beranda dengan isi draf (belum diterbitkan). */
    public function pratinjau(): View
    {
        return view('publik.beranda', PublikController::dataBeranda(true));
    }

    private function admin(Request $request): User
    {
        /** @var User */
        return $request->user();
    }
}
