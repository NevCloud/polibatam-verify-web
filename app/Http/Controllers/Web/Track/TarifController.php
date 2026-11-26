<?php

namespace App\Http\Controllers\Web\Track;

use App\Actions\Tagihan\SimpanVersiTarif;
use App\Http\Controllers\Controller;
use App\Models\Peminatan;
use App\Models\Tarif;
use App\Models\User;
use App\Services\TarifResolver;
use App\Support\Ui\Format;
use App\Support\Ui\PortalContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * HW-35 (FR-47, BR-25–BR-28): ringkasan tarif berlaku semua peminatan + tarif berversi per peminatan.
 * Admin Track & Super Admin mengelola; Admin Keuangan hanya membaca (RBAC "Tarif berversi" R).
 */
class TarifController extends Controller
{
    public function __construct(private TarifResolver $resolver) {}

    public function index(Request $request): View
    {
        $peminatan = Peminatan::with('track')->orderBy('nama')->get()->sortBy(fn (Peminatan $p) => $p->track->nama.'|'.$p->nama);
        $maks = (int) $peminatan->max('durasi_semester');

        return view('admin.track.tarif.index', [
            'portal' => PortalContext::dariPengguna($request->user(), 'Tarif Peminatan'),
            'semester' => $maks,
            'baris' => $peminatan->map(fn (Peminatan $p) => [
                'kode' => $p->kode, 'nama' => $p->nama, 'track' => $p->track->nama, 'durasi' => $p->durasi_semester, 'aktif' => $p->is_active,
                'formulir' => $this->rupiahBerlaku($p, 'formulir', null),
                'semester' => array_map(fn (int $n) => $n <= $p->durasi_semester ? $this->rupiahBerlaku($p, 'semester', $n) : null, range(1, max(1, $maks))),
                'href' => route('admin.tarif.show', $p->kode),
            ])->values()->all(),
            'jumlah' => ['peminatan' => $peminatan->count(), 'lengkap' => $peminatan->filter(fn (Peminatan $p) => $this->lengkap($p))->count()],
        ]);
    }

    public function show(Request $request, Peminatan $peminatan): View
    {
        $peminatan->load('track');
        $item = ['formulir' => ['Formulir pendaftaran', 'formulir', null]];
        foreach (range(1, $peminatan->durasi_semester) as $n) {
            $item["semester_{$n}"] = ["Semester {$n}", 'semester', $n];
        }
        $hariIni = Format::wib(now())->toDateString();

        return view('admin.track.tarif.show', [
            'portal' => PortalContext::dariPengguna($request->user(), 'Tarif Peminatan'),
            'peminatan' => ['kode' => $peminatan->kode, 'nama' => $peminatan->nama, 'track' => $peminatan->track->nama, 'durasi' => $peminatan->durasi_semester, 'aktif' => $peminatan->is_active],
            'tarif' => array_map(function (array $i) use ($peminatan) {
                $berlaku = $this->resolver->untuk($peminatan->id, $i[1], $i[2]);
                $terjadwal = Tarif::where('peminatan_id', $peminatan->id)->where('jenis', $i[1])->where('semester_ke', $i[2])
                    ->where('tanggal_efektif', '>', Format::wib(now())->toDateString())->orderBy('tanggal_efektif')->first();

                return [
                    'label' => $i[0],
                    'nominal' => $berlaku?->nominal,
                    'rupiah' => $berlaku ? Format::rupiah($berlaku->nominal) : null,
                    'sejak' => $berlaku ? Format::tanggal($berlaku->tanggal_efektif) : null,
                    'terjadwal' => $terjadwal ? Format::rupiah($terjadwal->nominal).' mulai '.Format::tanggal($terjadwal->tanggal_efektif) : null,
                ];
            }, $item),
            'riwayat' => Tarif::where('peminatan_id', $peminatan->id)->with('pembuat')->orderByDesc('tanggal_efektif')->orderByDesc('id')->get()
                ->map(fn (Tarif $t) => [
                    'item' => $t->jenis === 'formulir' ? 'Formulir pendaftaran' : "Semester {$t->semester_ke}",
                    'rupiah' => Format::rupiah($t->nominal),
                    'efektif' => Format::tanggal($t->tanggal_efektif),
                    'oleh' => $t->pembuat->name ?? '—',
                    'dibuat' => Format::tanggalJam($t->created_at ?? now()),
                    'terjadwal' => $t->tanggal_efektif->toDateString() > $hariIni,
                ])->all(),
            'bisaUbah' => $request->user()?->hasAnyRole(['admin_track', 'super_admin']) ?? false,
            'hariIni' => $hariIni,
        ]);
    }

    public function store(Request $request, Peminatan $peminatan, SimpanVersiTarif $simpan): RedirectResponse
    {
        // "Rp 5.500.000" → 5500000; kosong = tidak diubah.
        $request->merge(['nominal' => array_map(fn ($v) => ($d = preg_replace('/\D/', '', (string) $v)) === '' ? null : $d, (array) $request->input('nominal', []))]);
        $data = $request->validate([
            'tanggal_efektif' => ['required', 'date_format:Y-m-d'],
            'nominal' => ['array'],
            'nominal.*' => ['nullable', 'integer', 'min:1', 'max:999999999999'],
        ], [
            'tanggal_efektif.required' => 'Tanggal berlaku efektif wajib diisi.',
            'nominal.*.min' => 'Nominal tarif harus lebih dari 0.',
        ]);
        /** @var User $admin */
        $admin = $request->user();
        $jumlah = $simpan($peminatan, $data['tanggal_efektif'], array_map(fn ($v) => $v === null ? null : (int) $v, $data['nominal'] ?? []), $admin);

        return back()->with('status', "{$jumlah} versi tarif baru tersimpan, berlaku mulai ".Format::tanggal($data['tanggal_efektif']).'. Tagihan yang sudah terbit tidak berubah.');
    }

    private function rupiahBerlaku(Peminatan $p, string $jenis, ?int $semester): ?string
    {
        $t = $this->resolver->untuk($p->id, $jenis, $semester);

        return $t ? Format::rupiah($t->nominal) : null;
    }

    private function lengkap(Peminatan $p): bool
    {
        return $this->resolver->untuk($p->id, 'formulir') !== null
            && collect(range(1, $p->durasi_semester))->every(fn (int $n) => $this->resolver->untuk($p->id, 'semester', $n) !== null);
    }
}
