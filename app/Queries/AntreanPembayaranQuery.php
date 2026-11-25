<?php

namespace App\Queries;

use App\Models\Peminatan;
use App\Models\PeriodePendaftaran;
use App\Models\Tagihan;
use App\Support\Ui\Format;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * HW-28 (FR-31, BR-66): tagihan per status (tab), filter jenis/peminatan/periode, cari nama/nomor/kode referensi.
 * Bawaan tab "Menunggu Verifikasi", urut pengunggahan terlama. Struktur kunci = fixture resources/preview/hw-28.php.
 */
class AntreanPembayaranQuery
{
    public const PER_HALAMAN = 20;

    public const TAB = ['menunggu_verifikasi' => 'Menunggu Verifikasi', 'semua' => 'Semua', 'lunas' => 'Lunas', 'ditolak' => 'Ditolak'];

    public const JENIS = ['formulir' => 'Formulir', 'semester' => 'Semester'];

    /**
     * @param  array<string, mixed>  $filter
     * @return array<string, mixed>
     */
    public function halaman(array $filter): array
    {
        $peminatan = Peminatan::orderBy('nama')->pluck('nama', 'id')->all();
        $periode = PeriodePendaftaran::orderByDesc('tanggal_mulai')->pluck('nama', 'id')->all();
        $filter = [
            'tab' => array_key_exists((string) ($filter['tab'] ?? ''), self::TAB) ? (string) $filter['tab'] : 'menunggu_verifikasi',
            'q' => trim((string) ($filter['q'] ?? '')),
            'jenis' => array_key_exists((string) ($filter['jenis'] ?? ''), self::JENIS) ? (string) $filter['jenis'] : '',
            'peminatan' => array_key_exists((int) ($filter['peminatan'] ?? 0), $peminatan) ? (string) $filter['peminatan'] : '',
            'periode' => array_key_exists((int) ($filter['periode'] ?? 0), $periode) ? (string) $filter['periode'] : '',
        ];

        $dasar = Tagihan::query()->berlaku()
            ->when($filter['q'] !== '', fn (Builder $q) => $q->where(fn (Builder $w) => $w->where('kode_referensi', 'like', '%'.$filter['q'].'%')
                ->orWhereHas('pendaftaran', fn (Builder $p) => $p->where('nomor_pendaftaran', 'like', '%'.$filter['q'].'%')->orWhere('nama_lengkap', 'like', '%'.$filter['q'].'%'))))
            ->when($filter['jenis'] !== '', fn (Builder $q) => $q->where('jenis', $filter['jenis']))
            ->when($filter['peminatan'] !== '', fn (Builder $q) => $q->whereHas('pendaftaran.peminatanPeriode', fn (Builder $p) => $p->where('peminatan_id', $filter['peminatan'])))
            ->when($filter['periode'] !== '', fn (Builder $q) => $q->whereHas('pendaftaran.peminatanPeriode', fn (Builder $p) => $p->where('periode_pendaftaran_id', $filter['periode'])));

        $hitung = (clone $dasar)->toBase()->selectRaw('status, COUNT(*) n')->groupBy('status')->pluck('n', 'status');

        /** @var LengthAwarePaginator<int, Tagihan> $halaman */
        $halaman = (clone $dasar)->with('pendaftaran', 'buktiTerakhir')
            ->when($filter['tab'] !== 'semua', fn (Builder $q) => $q->where('status', $filter['tab']))
            ->orderBy('updated_at')->orderBy('id')
            ->paginate(self::PER_HALAMAN)->withQueryString();

        return [
            'filter' => $filter,
            'baris' => collect($halaman->items())->map(fn (Tagihan $t) => [
                'nomor' => $t->pendaftaran->nomor_pendaftaran,
                'nama' => (string) $t->pendaftaran->nama_lengkap,
                'kode' => $t->kode_referensi,
                'nominal' => Format::rupiah($t->nominal_total),
                'jenis' => $t->labelJenis(),
                'transfer' => $t->buktiTerakhir?->tanggal_transfer ? Format::tanggal($t->buktiTerakhir->tanggal_transfer) : '—',
                'status' => $t->status->label(),
                'perlu_tindakan' => $t->status->getValue() === 'menunggu_verifikasi',
                'href' => route('admin.verifikasi-pembayaran.show', $t),
            ])->all(),
            'jumlahTab' => [
                'semua' => (int) $hitung->sum(),
                'menunggu_verifikasi' => (int) ($hitung['menunggu_verifikasi'] ?? 0),
                'lunas' => (int) ($hitung['lunas'] ?? 0),
                'ditolak' => (int) ($hitung['ditolak'] ?? 0),
            ],
            'lunasHariIni' => Format::rupiah((int) Tagihan::where('status', 'lunas')->where('lunas_pada', '>=', Format::wib(now())->startOfDay()->utc())->sum('nominal_total')),
            'total' => $halaman->total(),
            'rentang' => $halaman->total() ? $halaman->firstItem().'–'.$halaman->lastItem() : '0',
            'tautan' => $halaman->linkCollection()->all(),
            'sebelumnya' => $halaman->previousPageUrl(),
            'berikutnya' => $halaman->nextPageUrl(),
            'tabTersedia' => self::TAB,
            'jenisTersedia' => self::JENIS,
            'peminatanTersedia' => $peminatan,
            'periodeTersedia' => $periode,
        ];
    }
}
