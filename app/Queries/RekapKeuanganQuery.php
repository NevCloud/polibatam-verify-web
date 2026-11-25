<?php

namespace App\Queries;

use App\Models\Peminatan;
use App\Models\PeriodePendaftaran;
use App\Models\Tagihan;
use App\Support\Ui\Format;
use Illuminate\Database\Eloquent\Builder;

/**
 * HW-32 (FR-38, NFR-19): jumlah & total nominal per status, termasuk tunggakan (belum lunas dan lewat jatuh tempo),
 * per peminatan; filter periode, peminatan, angkatan (= tahun mulai periode pendaftaran), jenis tagihan.
 * Agregasi dikerjakan di SQL (GROUP BY), bukan di PHP. Struktur kunci = fixture resources/preview/hw-32.php.
 */
class RekapKeuanganQuery
{
    public const JENIS = ['formulir' => 'Formulir', 'semester' => 'Semester'];

    /**
     * @param  array<string, mixed>  $filter
     * @return array<string, mixed>
     */
    public function halaman(array $filter): array
    {
        $filter = $this->bersihkan($filter);
        $hariIni = Format::wib(now())->toDateString();

        $total = $this->kueri($filter)->toBase()->selectRaw("
            SUM(tagihans.status = 'lunas') lunas_n, SUM(CASE WHEN tagihans.status = 'lunas' THEN nominal_total ELSE 0 END) lunas_rp,
            SUM(tagihans.status = 'menunggu_verifikasi') mv_n, SUM(CASE WHEN tagihans.status = 'menunggu_verifikasi' THEN nominal_total ELSE 0 END) mv_rp,
            SUM(tagihans.status IN ('belum_bayar','ditolak')) bb_n, SUM(CASE WHEN tagihans.status IN ('belum_bayar','ditolak') THEN nominal_total ELSE 0 END) bb_rp,
            SUM(tagihans.status IN ('belum_bayar','ditolak') AND jatuh_tempo < ?) tg_n,
            SUM(CASE WHEN tagihans.status IN ('belum_bayar','ditolak') AND jatuh_tempo < ? THEN nominal_total ELSE 0 END) tg_rp", [$hariIni, $hariIni])->first();

        $baris = $this->kueri($filter)->toBase()->groupBy('pm.id', 'pm.nama')->orderBy('pm.nama')->selectRaw("
            pm.nama peminatan, SUM(tagihans.status = 'lunas') lunas_n, SUM(CASE WHEN tagihans.status = 'lunas' THEN nominal_total ELSE 0 END) lunas_rp,
            SUM(tagihans.status IN ('belum_bayar','ditolak')) bb_n,
            SUM(CASE WHEN tagihans.status IN ('belum_bayar','ditolak') AND jatuh_tempo < ? THEN nominal_total ELSE 0 END) tg_rp", [$hariIni])->get();

        $kartu = fn ($n, $rp) => ['nominal' => Format::rupiah((int) $rp), 'jumlah' => (int) $n];

        return [
            'filter' => $filter,
            'ringkasan' => [
                'lunas' => $kartu($total->lunas_n ?? 0, $total->lunas_rp ?? 0),
                'menunggu' => $kartu($total->mv_n ?? 0, $total->mv_rp ?? 0),
                'belum' => $kartu($total->bb_n ?? 0, $total->bb_rp ?? 0),
                'tunggakan' => $kartu($total->tg_n ?? 0, $total->tg_rp ?? 0),
            ],
            'baris' => $baris->map(fn ($b) => [
                'peminatan' => $b->peminatan, 'lunas' => (int) $b->lunas_n, 'nominal_lunas' => Format::rupiah((int) $b->lunas_rp),
                'belum' => (int) $b->bb_n, 'tunggakan' => Format::rupiah((int) $b->tg_rp),
            ])->all(),
            'periodeTersedia' => PeriodePendaftaran::orderByDesc('tanggal_mulai')->pluck('nama', 'id')->all(),
            'peminatanTersedia' => Peminatan::orderBy('nama')->pluck('nama', 'id')->all(),
            'angkatanTersedia' => PeriodePendaftaran::selectRaw('DISTINCT YEAR(tanggal_mulai) th')->orderByDesc('th')->pluck('th', 'th')->all(),
            'jenisTersedia' => self::JENIS,
        ];
    }

    /**
     * Tagihan (tanpa yang dibatalkan) + join peminatan & periode sesuai filter. Dipakai juga ekspor.
     *
     * @param  array<string, string>  $filter  hasil bersihkan()
     * @return Builder<Tagihan>
     */
    public function kueri(array $filter): Builder
    {
        return Tagihan::query()->where('tagihans.status', '<>', 'dibatalkan')
            ->join('pendaftarans as p', 'p.id', '=', 'tagihans.pendaftaran_id')
            ->join('peminatan_periodes as pp', 'pp.id', '=', 'p.peminatan_periode_id')
            ->join('peminatans as pm', 'pm.id', '=', 'pp.peminatan_id')
            ->join('periode_pendaftarans as pr', 'pr.id', '=', 'pp.periode_pendaftaran_id')
            ->when($filter['periode'] !== '', fn (Builder $q) => $q->where('pr.id', $filter['periode']))
            ->when($filter['peminatan'] !== '', fn (Builder $q) => $q->where('pm.id', $filter['peminatan']))
            ->when($filter['angkatan'] !== '', fn (Builder $q) => $q->whereYear('pr.tanggal_mulai', $filter['angkatan']))
            ->when($filter['jenis'] !== '', fn (Builder $q) => $q->where('tagihans.jenis', $filter['jenis']));
    }

    /**
     * @param  array<string, mixed>  $filter
     * @return array{periode: string, peminatan: string, angkatan: string, jenis: string}
     */
    public function bersihkan(array $filter): array
    {
        $angka = fn (string $k) => ctype_digit((string) ($filter[$k] ?? '')) ? (string) $filter[$k] : '';

        return [
            'periode' => $angka('periode'),
            'peminatan' => $angka('peminatan'),
            'angkatan' => preg_match('/^\d{4}$/', (string) ($filter['angkatan'] ?? '')) ? (string) $filter['angkatan'] : '',
            'jenis' => array_key_exists((string) ($filter['jenis'] ?? ''), self::JENIS) ? (string) $filter['jenis'] : '',
        ];
    }

    /** Tanggal hari ini (WIB) untuk penanda tunggakan; dipakai ekspor juga. */
    public static function hariIni(): string
    {
        return Format::wib(now())->toDateString();
    }
}
