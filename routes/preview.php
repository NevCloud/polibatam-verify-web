<?php

use App\Support\Preview\PreviewController;
use App\Support\Ui\PortalContext;
use Illuminate\Support\Facades\Route;

// Dimuat hanya bila config('app.preview') = APP_ENV=local && PV_PREVIEW=true (bootstrap/app.php).
Route::get('/_preview', [PreviewController::class, 'index'])->name('preview.index');

// Galeri FE-0: semua komponen x-pv.*; peran dipilih lewat tombol di halaman (?peran=…, tidak peka huruf besar).
Route::get('/_preview/komponen', function () {
    $daftarPeran = ['Calon Peserta', 'Peserta Didik', 'Admin SILAU', 'Admin Keuangan', 'Admin Track', 'Super Admin'];
    $diminta = trim((string) request('peran', 'Calon Peserta'));
    $peran = collect($daftarPeran)->first(fn ($p) => strcasecmp($p, $diminta) === 0);
    abort_if($peran === null, 404);

    return view('preview.komponen', [
        'daftarPeran' => $daftarPeran,
        'portal' => PortalContext::dariFixture(['nama' => 'Nabila Putri Ramadhani', 'peran' => $peran, 'periode' => 'Pendaftaran 2026']),
    ]);
})->name('preview.komponen');

Route::get('/_preview/{halaman}/{state?}', [PreviewController::class, 'show'])->name('preview.show');
