<?php

namespace App\Console\Commands;

use App\Models\Tagihan;
use App\Services\Notifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/** FR-57, BR-35: pengingat harian ke Super Admin bila masih ada tagihan semester terbuka tanpa jatuh tempo. */
class CekTagihanTanpaJatuhTempo extends Command
{
    protected $signature = 'tagihan:cek-tanpa-jatuh-tempo';

    protected $description = 'Mengingatkan Super Admin tentang tagihan semester yang belum memiliki jatuh tempo';

    public function handle(Notifier $notifier): int
    {
        $jumlah = Tagihan::where('jenis', 'semester')->whereNull('jatuh_tempo')->whereIn('status', Tagihan::TERBUKA)->count();

        if ($jumlah > 0) {
            $notifier->kirimKePeran(['super_admin'], 'tagihan_tanpa_jatuh_tempo', "{$jumlah} tagihan semester tanpa jatuh tempo",
                'Tagihan semester berikut belum memiliki jatuh tempo dan tidak dihitung sebagai tunggakan. Tetapkan jatuh tempo secara manual.',
                route('admin.tagihan.tanpa-jatuh-tempo'));
        }
        Log::info('tagihan:cek-tanpa-jatuh-tempo selesai', ['tanpa_jatuh_tempo' => $jumlah]);
        $this->info("{$jumlah} tagihan tanpa jatuh tempo.");

        return self::SUCCESS;
    }
}
