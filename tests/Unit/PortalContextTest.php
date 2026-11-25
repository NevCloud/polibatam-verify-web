<?php

use App\Support\Ui\PortalContext;

it('inisial avatar = 2 huruf dari nama (design-memory §8, §9.2)', function (string $nama, string $inisial) {
    expect(PortalContext::inisial($nama))->toBe($inisial);
})->with([
    ['Nabila Putri Ramadhani', 'NR'],
    ['Hendra Saputra', 'HS'],
    ['Ratna Wijayanti', 'RW'],
    ['Yuliana', 'YU'],
]);

it('menu sidebar mengikuti peran (Figma Layar/Sidebar) dan menandai satu item aktif', function () {
    $ctx = PortalContext::dariFixture(['nama' => 'Hendra Saputra', 'peran' => 'Admin SILAU'], 'Data Pendaftaran');

    expect(array_column($ctx->menu, 'label'))->toBe(['Dashboard', 'Data Pendaftaran', 'Verifikasi Berkas', 'Arsip Periode', 'Dasbor Kuota', 'Notifikasi', 'Profil'])
        ->and(array_column(array_filter($ctx->menu, fn ($m) => $m['aktif']), 'label'))->toBe(['Data Pendaftaran'])
        ->and($ctx->eyebrow)->toBe('PORTAL ADMIN')
        ->and($ctx->nadaPeran)->toBe('info');
});

it('T-14: sidebar Super Admin memuat Data Pendaftaran, Arsip Periode, Notifikasi, Profil (BR-67)', function () {
    $label = array_column(PortalContext::dariFixture(['nama' => 'Ratna Wijayanti', 'peran' => 'Super Admin'])->menu, 'label');

    expect($label)->toContain('Data Pendaftaran', 'Arsip Periode', 'Notifikasi', 'Profil')
        ->and($label[0])->toBe('Dashboard')
        ->and(array_slice($label, -2))->toBe(['Notifikasi', 'Profil']);
});

it('setiap ikon menu punya berkas aset di public/images/ikon', function (string $peran) {
    foreach (PortalContext::dariFixture(['nama' => 'Uji Coba', 'peran' => $peran])->menu as $m) {
        expect(dirname(__DIR__, 2)."/public/images/ikon/{$m['ikon']}.svg")->toBeFile();
    }
})->with(['Calon Peserta', 'Peserta Didik', 'Admin SILAU', 'Admin Keuangan', 'Admin Track', 'Super Admin']);

it('chip Peserta Didik bernada success, Calon Peserta bernada info', function () {
    expect(PortalContext::dariFixture(['nama' => 'Rizki Maulana', 'peran' => 'Peserta Didik'])->nadaPeran)->toBe('success')
        ->and(PortalContext::dariFixture(['nama' => 'Nabila Putri Ramadhani', 'peran' => 'Calon Peserta'])->eyebrow)->toBe('PORTAL PESERTA');
});

it('AE-15: peran di luar nama resmi ditolak', function () {
    PortalContext::dariFixture(['nama' => 'X', 'peran' => 'Admin akademik']);
})->throws(InvalidArgumentException::class);
