<?php

namespace App\Http\Controllers\Web\Track;

use App\Actions\Kuota\AturKuota;
use App\Http\Controllers\Controller;
use App\Models\Peminatan;
use App\Models\PeminatanPeriode;
use App\Models\User;
use App\Queries\KuotaQuery;
use App\Support\Ui\PortalContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * HW-36 kuota minimum per periode (FR-48, BR-15, BR-20) & HW-37 dasbor kuota (FR-49, AC-24.1/24.2). K-42: tanpa kuota maksimum.
 * Atur: Admin Track (sebelum periode dimulai) & Super Admin. Baca: Admin SILAU (RBAC kuota R) — dasbor juga Admin Track & Super Admin.
 */
class KuotaController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $admin */
        $admin = $request->user();
        $periode = KuotaQuery::periode($request->query('periode'));
        $baris = $periode ? KuotaQuery::baris($periode) : [];
        $terpasang = array_column($baris, 'peminatan_id');
        // AC-23.6: peminatan aktif tanpa kuota di periode ini tidak tampil di katalog → tampilkan agar bisa diatur.
        $belum = Peminatan::with('track')->where('is_active', true)->whereNotIn('id', $terpasang)->orderBy('nama')->get();

        return view('admin.track.kuota.index', [
            'portal' => PortalContext::dariPengguna($admin, 'Kuota per Periode'),
            'periode' => $periode ? ['id' => $periode->id] + KuotaQuery::ringkasPeriode($periode) + ['dimulai' => AturKuota::sudahDimulai($periode)] : null,
            'periodeTersedia' => KuotaQuery::daftarPeriode(),
            'baris' => $baris,
            'belumDiatur' => $belum->map(fn (Peminatan $p) => ['peminatan_id' => $p->id, 'peminatan' => $p->nama, 'kode' => $p->kode, 'track' => $p->track->nama])->values()->all(),
            'bisaUbah' => $periode !== null && AturKuota::bolehUbah($periode, $admin),
            'wajibAlasan' => $periode !== null && AturKuota::sudahDimulai($periode),
            'jumlah' => [
                'peminatan' => count($baris),
                'kuota_min' => array_sum(array_column($baris, 'kuota_min')),
                'npd_terbit' => array_sum(array_column($baris, 'npd_terbit')),
            ],
        ]);
    }

    /** Simpan semua isian sekaligus (satu transaksi); isian kosong untuk peminatan yang belum diatur = dilewati. */
    public function simpan(Request $request, AturKuota $atur): RedirectResponse
    {
        $data = $request->validate([
            'periode' => ['required', 'integer', 'exists:periode_pendaftarans,id'],
            'kuota' => ['array'],
            'kuota.*' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'alasan' => ['nullable', 'string', 'max:500'],
        ], ['kuota.*.min' => 'Kuota minimum tidak boleh negatif.', 'kuota.*.integer' => 'Kuota minimum harus bilangan bulat.']);
        $periode = KuotaQuery::periode($data['periode']);
        abort_if($periode === null, 404);
        /** @var User $admin */
        $admin = $request->user();

        $diubah = DB::transaction(function () use ($data, $periode, $admin, $atur) {
            $peminatan = Peminatan::whereIn('id', array_keys($data['kuota'] ?? []))->get()->keyBy('id');
            $sebelum = PeminatanPeriode::where('periode_pendaftaran_id', $periode->id)->pluck('kuota_min', 'peminatan_id');
            $n = 0;
            foreach ($data['kuota'] ?? [] as $id => $nilai) {
                if ($nilai === null || ! $peminatan->has($id) || (isset($sebelum[$id]) && (int) $sebelum[$id] === (int) $nilai)) {
                    continue;
                }
                try {
                    $atur($periode, $peminatan[$id], (int) $nilai, $data['alasan'] ?? null, $admin);
                } catch (ValidationException $e) {
                    $pesan = $e->errors();
                    throw ValidationException::withMessages(isset($pesan['alasan']) ? ['alasan' => $pesan['alasan']] : ["kuota.{$id}" => $peminatan[$id]->nama.': '.collect($pesan)->flatten()->first()]);
                }
                $n++;
            }

            return $n;
        });

        return to_route('admin.kuota.index', ['periode' => $periode->id])
            ->with('status', $diubah ? "{$diubah} kuota minimum tersimpan dan tercatat di audit log." : 'Tidak ada perubahan kuota.');
    }

    public function lepas(Request $request, PeminatanPeriode $peminatanPeriode, AturKuota $atur): RedirectResponse
    {
        /** @var User $admin */
        $admin = $request->user();
        $atur->lepas($peminatanPeriode, $admin);

        return back()->with('status', 'Peminatan dilepas dari periode ini.');
    }

    public function dasbor(Request $request): View
    {
        $periode = KuotaQuery::periode($request->query('periode'));
        $baris = $periode ? KuotaQuery::baris($periode) : [];

        return view('admin.track.kuota.dasbor', [
            'portal' => PortalContext::dariPengguna($request->user(), 'Dasbor Kuota'),
            'periode' => $periode ? ['id' => $periode->id] + KuotaQuery::ringkasPeriode($periode) : null,
            'periodeTersedia' => KuotaQuery::daftarPeriode(),
            'baris' => $baris,
            'jumlah' => [
                'diajukan' => array_sum(array_column($baris, 'diajukan')),
                'masuk_kuota' => array_sum(array_column($baris, 'masuk_kuota')),
                'npd_terbit' => array_sum(array_column($baris, 'npd_terbit')),
                'di_bawah' => count(array_filter($baris, fn (array $b) => in_array($b['nada'], ['warning', 'error'], true))),
            ],
        ]);
    }
}
