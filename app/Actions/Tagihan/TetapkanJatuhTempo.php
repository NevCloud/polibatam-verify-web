<?php

namespace App\Actions\Tagihan;

use App\Models\Tagihan;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

/**
 * FR-57, BR-35: Super Admin menetapkan jatuh tempo tagihan semester yang belum punya jatuh tempo (per tagihan atau massal).
 * Boleh lebih awal dari hari ini (langsung lewat jatuh tempo). Penetapan massal bukan keputusan verifikasi (BR-66).
 *
 * @return int jumlah tagihan yang ditetapkan
 */
class TetapkanJatuhTempo
{
    public function __construct(private AuditLogger $audit) {}

    /** @param  list<int>  $ids */
    public function __invoke(array $ids, string $tanggal, ?string $catatan, User $superAdmin): int
    {
        return DB::transaction(function () use ($ids, $tanggal, $catatan, $superAdmin) {
            $tagihans = Tagihan::whereIn('id', $ids)->where('jenis', 'semester')->whereNull('jatuh_tempo')
                ->whereIn('status', Tagihan::TERBUKA)->lockForUpdate()->get();

            foreach ($tagihans as $t) {
                $t->update(['jatuh_tempo' => $tanggal, 'jatuh_tempo_manual' => true]);
                $this->audit->catat('tetapkan_jatuh_tempo', 'tagihan', $t->id, sesudah: ['jatuh_tempo' => $tanggal], alasan: $catatan, pelaku: $superAdmin);
            }

            return $tagihans->count();
        });
    }
}
