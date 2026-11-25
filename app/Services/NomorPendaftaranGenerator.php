<?php

namespace App\Services;

use App\Support\Ui\Format;

/**
 * BR-68: nomor `PV-{TAHUN}-{6 digit}` dari penghitung per tahun `urut_nomor_pendaftaran_{TAHUN}` (Penghitung, FOR UPDATE).
 * Wajib dipanggil di dalam transaksi yang sama dengan INSERT pendaftaran.
 */
class NomorPendaftaranGenerator
{
    public function __construct(private Penghitung $penghitung) {}

    public function berikutnya(): string
    {
        $tahun = Format::wib(now())->year;

        return sprintf('PV-%d-%06d', $tahun, $this->penghitung->berikutnya("urut_nomor_pendaftaran_{$tahun}", "Penghitung nomor pendaftaran {$tahun} (BR-68)"));
    }
}
