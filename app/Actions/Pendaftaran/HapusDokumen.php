<?php

namespace App\Actions\Pendaftaran;

use App\Models\Dokumen;
use App\Models\Pendaftaran;
use Illuminate\Support\Facades\DB;

/**
 * HW-15 Tab Berkas "Hapus" (Figma): hanya saat Draf. pv_app tidak punya DELETE (keamanan-basis-data),
 * jadi slot dikosongkan dengan melepas versi aktif; versi lama tetap di riwayat (FR-17).
 */
class HapusDokumen
{
    public function __invoke(Pendaftaran $pendaftaran, string $jenis): void
    {
        DB::transaction(function () use ($pendaftaran, $jenis) {
            SimpanDraf::pastikanDraf(Pendaftaran::whereKey($pendaftaran->id)->lockForUpdate()->firstOrFail());

            Dokumen::where('pendaftaran_id', $pendaftaran->id)->where('jenis', $jenis)->first()
                ?->forceFill(['versi_aktif_id' => null])->save();
        });
    }
}
