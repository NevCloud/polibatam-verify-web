<?php

namespace App\Queries;

use App\Models\Peminatan;
use App\Models\Pendaftaran;
use App\Models\PeriodePendaftaran;
use App\Models\Track;
use App\Services\NikHasher;
use App\Support\Ui\Format;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * HW-25 (FR-18, AC-09.1): pendaftaran berstatus Diajukan, urut pengajuan terlama, tanpa Draf (BR-64), NIK tersamar (BR-62).
 * Struktur kunci = fixture resources/preview/hw-25.php.
 */
class AntreanVerifikasiQuery
{
    public const PER_HALAMAN = 20;

    public const JENIS = ['baru' => 'Pengajuan baru', 'revisi' => 'Revisi masuk'];

    public function __construct(private NikHasher $nik) {}

    /**
     * @param  array<string, mixed>  $filter
     * @return array<string, mixed>
     */
    public function halaman(array $filter): array
    {
        $tracks = Track::orderBy('nama')->pluck('nama', 'kode')->all();
        $periode = PeriodePendaftaran::orderByDesc('tanggal_mulai')->pluck('nama', 'id')->all();
        $peminatan = Peminatan::orderBy('nama')->pluck('nama', 'id')->all();
        $filter = [
            'q' => trim((string) ($filter['q'] ?? '')),
            'track' => array_key_exists((string) ($filter['track'] ?? ''), $tracks) ? (string) $filter['track'] : '',
            'peminatan' => array_key_exists((int) ($filter['peminatan'] ?? 0), $peminatan) ? (string) $filter['peminatan'] : '',
            'periode' => array_key_exists((int) ($filter['periode'] ?? 0), $periode) ? (string) $filter['periode'] : '',
            'jenis' => array_key_exists((string) ($filter['jenis'] ?? ''), self::JENIS) ? (string) $filter['jenis'] : '',
        ];
        $nikCari = preg_replace('/\D/', '', $filter['q']);
        // "Revisi masuk" = pernah ada keputusan Ditolak pada salah satu dokumennya (diajukan ulang setelah Perlu Revisi).
        $pernahDitolak = fn ($q) => $q->select(DB::raw(1))->from('verifikasi_dokumens as vd')
            ->join('dokumen_versis as dv', 'dv.id', '=', 'vd.dokumen_versi_id')
            ->join('dokumens as d', 'd.id', '=', 'dv.dokumen_id')
            ->whereColumn('d.pendaftaran_id', 'pendaftarans.id')->where('vd.keputusan', 'ditolak');

        $dasar = Pendaftaran::untukAdmin()->where('status', 'diajukan');

        /** @var LengthAwarePaginator<int, Pendaftaran> $halaman */
        $halaman = (clone $dasar)->with('peminatanPeriode.peminatan')
            ->select('pendaftarans.*')->selectSub(fn ($q) => $pernahDitolak($q)->limit(1), 'revisi')
            ->when($filter['q'] !== '', fn (Builder $q) => $q->where(fn (Builder $w) => $w
                ->where('nama_lengkap', 'like', '%'.$filter['q'].'%')
                ->orWhere('nomor_pendaftaran', 'like', '%'.$filter['q'].'%')
                ->when(strlen($nikCari) === 16, fn (Builder $n) => $n->orWhere('nik_hash', $this->nik->hash($nikCari))))) // BR-62: cari NIK lewat hash
            ->when($filter['track'] !== '', fn (Builder $q) => $q->whereHas('peminatanPeriode.peminatan.track', fn (Builder $t) => $t->where('kode', $filter['track'])))
            ->when($filter['peminatan'] !== '', fn (Builder $q) => $q->whereHas('peminatanPeriode', fn (Builder $t) => $t->where('peminatan_id', $filter['peminatan'])))
            ->when($filter['periode'] !== '', fn (Builder $q) => $q->whereHas('peminatanPeriode', fn (Builder $t) => $t->where('periode_pendaftaran_id', $filter['periode'])))
            ->when($filter['jenis'] === 'revisi', fn (Builder $q) => $q->whereExists($pernahDitolak))
            ->when($filter['jenis'] === 'baru', fn (Builder $q) => $q->whereNotExists($pernahDitolak))
            ->orderBy('waktu_pengajuan')->orderBy('id')
            ->paginate(self::PER_HALAMAN)->withQueryString();

        return [
            'filter' => $filter,
            'baris' => collect($halaman->items())->map(fn (Pendaftaran $p) => [
                'nomor' => $p->nomor_pendaftaran,
                'nama' => (string) $p->nama_lengkap,
                'nik' => NikHasher::samarkan($p->nik_terenkripsi),
                'peminatan' => $p->peminatanPeriode->peminatan->nama,
                'diajukan' => $p->waktu_pengajuan ? Format::wib($p->waktu_pengajuan)->translatedFormat('j M Y') : '—',
                'jenis' => $p->getAttribute('revisi') ? 'Revisi masuk' : 'Diajukan',
                'href' => route('admin.verifikasi-berkas.show', $p),
            ])->all(),
            'menunggu' => (clone $dasar)->count(),
            'total' => $halaman->total(),
            'rentang' => $halaman->total() ? $halaman->firstItem().'–'.$halaman->lastItem() : '0',
            'tautan' => $halaman->linkCollection()->all(),
            'sebelumnya' => $halaman->previousPageUrl(),
            'berikutnya' => $halaman->nextPageUrl(),
            'trackTersedia' => $tracks,
            'peminatanTersedia' => $peminatan,
            'periodeTersedia' => $periode,
            'jenisTersedia' => self::JENIS,
        ];
    }
}
