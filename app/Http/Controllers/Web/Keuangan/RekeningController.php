<?php

namespace App\Http\Controllers\Web\Keuangan;

use App\Actions\Tagihan\KelolaRekening;
use App\Http\Controllers\Controller;
use App\Models\RekeningInstitusi;
use App\Models\User;
use App\Support\Ui\PortalContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** HW-56 (FR-67, BR-70, A-62): rekening institusi. Admin Keuangan & Super Admin; selain itu 403 (middleware role). */
class RekeningController extends Controller
{
    public const JENIS = ['formulir' => 'Formulir', 'semester' => 'Semester'];

    public function index(Request $request): View
    {
        $dipakai = DB::table('tagihans')->whereNotNull('rekening_institusi_id')->distinct()->pluck('rekening_institusi_id')->all();
        $rekening = RekeningInstitusi::orderBy('jenis_tagihan')->orderByDesc('status')->orderBy('nama_bank')->get();

        return view('admin.keuangan.rekening.index', [
            'portal' => PortalContext::dariPengguna($request->user(), 'Rekening Institusi'),
            'rekening' => $rekening->map(fn (RekeningInstitusi $r) => [
                'id' => $r->id, 'bank' => $r->nama_bank, 'nomor' => $r->nomor_rekening, 'atas_nama' => $r->atas_nama,
                'jenis' => $r->jenis_tagihan, 'jenis_label' => self::JENIS[$r->jenis_tagihan] ?? $r->jenis_tagihan,
                'aktif' => $r->status->getValue() === 'aktif', 'dipakai' => in_array($r->id, $dipakai, true),
            ])->all(),
            'jumlah' => ['total' => $rekening->count(), 'aktif' => $rekening->filter(fn ($r) => $r->status->getValue() === 'aktif')->count()],
            'jenisTersedia' => self::JENIS,
        ]);
    }

    public function store(Request $request, KelolaRekening $kelola): RedirectResponse
    {
        $kelola->simpan($this->validasi($request), null, $this->admin($request));

        return back()->with('status', 'Rekening ditambahkan (Nonaktif). Aktifkan bila sudah siap dipakai.');
    }

    public function update(Request $request, RekeningInstitusi $rekening, KelolaRekening $kelola): RedirectResponse
    {
        $kelola->simpan($this->validasi($request, "rekening-{$rekening->id}"), $rekening, $this->admin($request));

        return back()->with('status', 'Rekening diperbarui. Tagihan yang sudah terbit tetap memakai rekening lama (snapshot).');
    }

    public function status(Request $request, RekeningInstitusi $rekening, KelolaRekening $kelola): RedirectResponse
    {
        $aktif = $request->boolean('aktif');
        $kelola->ubahStatus($rekening, $aktif, $this->admin($request));

        return back()->with('status', $aktif ? "Rekening {$rekening->nama_bank} aktif untuk tagihan {$rekening->jenis_tagihan}; rekening aktif sebelumnya dinonaktifkan." : 'Rekening dinonaktifkan.');
    }

    /** @return array{jenis_tagihan: string, nama_bank: string, nomor_rekening: string, atas_nama: string} */
    private function validasi(Request $request, string $tas = 'rekening'): array
    {
        $request->merge(['nomor_rekening' => preg_replace('/\D/', '', (string) $request->input('nomor_rekening'))]);

        /** @var array{jenis_tagihan: string, nama_bank: string, nomor_rekening: string, atas_nama: string} */
        return $request->validateWithBag($tas, [
            'jenis_tagihan' => ['required', Rule::in(array_keys(self::JENIS))],
            'nama_bank' => ['required', 'string', 'max:100'],
            'nomor_rekening' => ['required', 'regex:/^[0-9]{5,30}$/'],
            'atas_nama' => ['required', 'string', 'max:150'],
        ], ['nomor_rekening.*' => 'Nomor rekening harus 5–30 digit angka.']);
    }

    private function admin(Request $request): User
    {
        /** @var User */
        return $request->user();
    }
}
