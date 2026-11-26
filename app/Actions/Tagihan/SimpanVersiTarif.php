<?php

namespace App\Actions\Tagihan;

use App\Models\Peminatan;
use App\Models\Tarif;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\TarifResolver;
use App\Support\Ui\Format;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * HW-35 (FR-47, BR-25–BR-27, BR-58): setiap perubahan tarif = baris versi baru dengan tanggal berlaku efektif.
 * Versi lama tidak pernah diubah/dihapus (pv_app hanya SELECT, INSERT pada `tarifs`). Hanya nominal yang berubah dibuatkan versi.
 * Tagihan yang sudah terbit tidak terpengaruh (snapshot nominal, BR-28).
 */
class SimpanVersiTarif
{
    public function __construct(private TarifResolver $resolver, private AuditLogger $audit) {}

    /**
     * @param  array<string, int|null>  $nominal  kunci `formulir`, `semester_1` … `semester_D`; null/kosong = tidak diubah
     * @return int jumlah versi baru
     */
    public function __invoke(Peminatan $peminatan, string $tanggalEfektif, array $nominal, User $admin): int
    {
        $tanggal = Carbon::parse($tanggalEfektif.' 12:00', 'Asia/Jakarta');
        if ($tanggal->toDateString() < Format::wib(now())->toDateString()) {
            // Tanggal mundur akan mengubah tarif yang "seharusnya" berlaku di masa lalu; versi baru hanya untuk ke depan.
            throw ValidationException::withMessages(['tanggal_efektif' => 'Tanggal berlaku efektif tidak boleh sebelum hari ini.']);
        }

        $butir = ['formulir' => [null, 'Formulir']];
        foreach (range(1, $peminatan->durasi_semester) as $n) {
            $butir["semester_{$n}"] = [$n, "Semester {$n}"];
        }

        return DB::transaction(function () use ($peminatan, $tanggal, $nominal, $butir, $admin) {
            $baru = [];
            foreach ($butir as $kunci => [$semester, $label]) {
                $nilai = $nominal[$kunci] ?? null;
                if ($nilai === null) {
                    continue;
                }
                if ($nilai <= 0) { // BR-25 (CHECK chk_tarifs_nominal juga menolak)
                    throw ValidationException::withMessages(["nominal.{$kunci}" => "Tarif {$label} harus lebih dari 0."]);
                }
                $jenis = $semester === null ? 'formulir' : 'semester';
                if ($this->resolver->untuk($peminatan->id, $jenis, $semester, $tanggal)?->nominal === $nilai) {
                    continue; // tidak berubah
                }
                $sudahAda = Tarif::where('peminatan_id', $peminatan->id)->where('jenis', $jenis)->where('semester_ke', $semester)
                    ->whereDate('tanggal_efektif', $tanggal->toDateString())->exists();
                if ($sudahAda) {
                    throw ValidationException::withMessages(['tanggal_efektif' => "Sudah ada versi tarif {$label} dengan tanggal efektif ini; versi tidak dapat diubah. Pilih tanggal lain."]);
                }
                Tarif::create([
                    'peminatan_id' => $peminatan->id, 'jenis' => $jenis, 'semester_ke' => $semester, 'nominal' => $nilai,
                    'tanggal_efektif' => $tanggal->toDateString(), 'dibuat_oleh' => $admin->id,
                ]);
                $baru[$kunci] = $nilai;
            }

            if ($baru === []) {
                throw ValidationException::withMessages(['nominal' => 'Tidak ada perubahan tarif untuk disimpan.']);
            }
            $this->audit->catat('simpan_tarif', 'peminatan', $peminatan->id,
                sesudah: ['tanggal_efektif' => $tanggal->toDateString(), 'nominal' => $baru], pelaku: $admin);

            return count($baru);
        });
    }
}
