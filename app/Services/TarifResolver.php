<?php

namespace App\Services;

use App\Models\Tarif;
use App\Support\Ui\Format;
use DateTimeInterface;

/** BR-27: tarif yang dipakai = versi dengan tanggal efektif terbaru ≤ tanggal penerbitan (tanggal kalender WIB). */
class TarifResolver
{
    public function untuk(int $peminatanId, string $jenis, ?int $semesterKe = null, ?DateTimeInterface $pada = null): ?Tarif
    {
        return Tarif::where('peminatan_id', $peminatanId)->where('jenis', $jenis)
            ->when($jenis === 'semester', fn ($q) => $q->where('semester_ke', $semesterKe), fn ($q) => $q->whereNull('semester_ke'))
            ->where('tanggal_efektif', '<=', Format::wib($pada ?? now())->toDateString())
            ->orderByDesc('tanggal_efektif')->orderByDesc('id')
            ->first();
    }
}
