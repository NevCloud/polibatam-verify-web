<?php

use App\Support\Preview\PreviewController;
use App\Support\Ui\PortalContext;
use Illuminate\Support\Facades\Route;

// PRD §4.2 butir 4: setiap fixture dirender pada tiap state tanpa error.
foreach (PreviewController::semuaFixture() as $id => $fixture) {
    foreach (PreviewController::state($fixture) as $state) {
        $view = PreviewController::view($fixture, $state);
        it("kontrak {$id} state {$state}: view {$view} dirender", function () use ($fixture, $state, $view) {
            $html = view($view, PreviewController::data($fixture, $state))->render();

            expect($html)->toContain('<html lang="id">')
                ->not->toMatch('/#[0-9a-fA-F]{6}\b/'); // AE-17: tidak ada hex di view
        });
    }
}

it('HW-49–55: abort() memakai halaman sistem dengan kode status yang benar', function (int $kode, string $judul) {
    Route::get('/_uji/galat', fn () => abort($kode))->middleware('web');

    $this->get('/_uji/galat')->assertStatus($kode)->assertSee($judul)->assertSee('lang="id"', false);
})->with([
    [403, 'Akses Ditolak'],
    [404, 'Halaman Tidak Ditemukan'],
    [410, 'Tautan Kedaluwarsa'],
    [419, 'Sesi Berakhir'],
    [429, 'Terlalu Banyak Permintaan'],
    [503, 'Sedang Dalam Pemeliharaan'],
]);

it('HW-54/NFR-35: halaman 500 tanpa detail teknis saat APP_DEBUG=false', function () {
    config(['app.debug' => false]);
    Route::get('/_uji/meledak', fn () => throw new RuntimeException('RAHASIA_STACK_TRACE pv_app'))->middleware('web');

    $this->get('/_uji/meledak')
        ->assertStatus(500)
        ->assertSee('Terjadi Kesalahan pada Sistem')
        ->assertDontSee('RAHASIA_STACK_TRACE')
        ->assertDontSee('pv_app')
        ->assertDontSee('RuntimeException');
});

it('portal layout menerima PortalContext dari fixture', function () {
    $html = view('layouts.portal', [
        'portal' => PortalContext::dariFixture(['nama' => 'Nabila Putri Ramadhani', 'peran' => 'Calon Peserta']),
    ])->render();

    expect($html)->toContain('Nabila Putri Ramadhani')->toContain('NR')->toContain('Calon Peserta')->toContain('PORTAL PESERTA');
});

it('FE-0: galeri komponen dirender untuk setiap peran tanpa hex', function (string $peran) {
    $html = view('preview.komponen', [
        'portal' => PortalContext::dariFixture(['nama' => 'Ratna Wijayanti', 'peran' => $peran]),
    ])->render();

    expect($html)->toContain('Komponen PV')->toContain($peran)->not->toMatch('/#[0-9a-fA-F]{6}\b/');
})->with(['Calon Peserta', 'Peserta Didik', 'Admin SILAU', 'Admin Keuangan', 'Admin Track', 'Super Admin']);

it('HW-55: halaman 503 tanpa tautan kedua (Figma)', function () {
    expect(view('errors.503')->render())->not->toContain('Ke Halaman Utama');
});
