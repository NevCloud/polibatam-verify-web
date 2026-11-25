<?php

namespace App\Exports;

use App\Models\Tagihan;
use App\Queries\RekapKeuanganQuery;
use App\Support\Ui\Format;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomChunkSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * FR-38 ekspor Excel (HW-32): satu baris per tagihan sesuai filter rekap. Dibaca per 1.000 baris dengan urutan
 * unik (id) agar 10.000+ baris tidak dimuat sekaligus. Tanpa NIK/rekening pribadi (NFR-12). Nominal = integer Rupiah.
 *
 * @implements WithMapping<Tagihan>
 */
class RekapKeuanganExport implements FromQuery, WithCustomChunkSize, WithHeadings, WithMapping, WithTitle
{
    /** @param  array{periode: string, peminatan: string, angkatan: string, jenis: string}  $filter */
    public function __construct(private RekapKeuanganQuery $rekap, private array $filter) {}

    /** @return Builder<Tagihan> */
    public function query(): Builder
    {
        return $this->rekap->kueri($this->filter)
            ->select('tagihans.*', 'p.nomor_pendaftaran', 'p.nama_lengkap', 'pm.nama as nama_peminatan', 'pr.nama as nama_periode')
            ->orderBy('tagihans.id');
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    /** @return list<string> */
    public function headings(): array
    {
        return ['Kode referensi', 'Nomor pendaftaran', 'Nama', 'Periode', 'Peminatan', 'Jenis', 'Nominal dasar', 'Kode unik', 'Nominal total',
            'Status', 'Jatuh tempo', 'Tunggakan', 'Lunas pada (WIB)'];
    }

    /** @return list<mixed> */
    public function map($t): array
    {
        $terbuka = in_array($t->status->getValue(), ['belum_bayar', 'ditolak'], true);

        return [
            $t->kode_referensi, $t->getAttribute('nomor_pendaftaran'), $t->getAttribute('nama_lengkap'), $t->getAttribute('nama_periode'),
            $t->getAttribute('nama_peminatan'), $t->labelJenis(), $t->nominal_dasar, $t->kode_unik, $t->nominal_total, $t->status->label(),
            $t->jatuh_tempo?->toDateString() ?? '', $terbuka && $t->jatuh_tempo && $t->jatuh_tempo->toDateString() < RekapKeuanganQuery::hariIni() ? 'Ya' : 'Tidak',
            $t->lunas_pada ? Format::wib($t->lunas_pada)->format('Y-m-d H:i') : '',
        ];
    }

    public function title(): string
    {
        return 'Rekap tagihan';
    }
}
