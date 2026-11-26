<?php

// HW-35 · Tarif (P07). Kontrak = Track\TarifController::index (`semester`, `baris`, `jumlah`) dan ::show (state peminatan_*: `peminatan`, `tarif`, `riwayat`, `bisaUbah`, `hariIni`).
$b = fn (string $kode, string $nama, string $track, int $durasi, ?string $form, array $smt) => [
    'kode' => $kode, 'nama' => $nama, 'track' => $track, 'durasi' => $durasi, 'aktif' => true, 'formulir' => $form,
    'semester' => array_pad($smt, 4, null), 'href' => '#',
];
$tarif = ['formulir' => ['label' => 'Formulir pendaftaran', 'nominal' => 300000, 'rupiah' => 'Rp 300.000', 'sejak' => '1 Oktober 2026', 'terjadwal' => null]];
foreach ([1 => 5500000, 2 => 4800000, 3 => 4800000, 4 => null] as $n => $v) {
    $tarif["semester_{$n}"] = ['label' => "Semester {$n}", 'nominal' => $v, 'rupiah' => $v ? 'Rp '.number_format($v, 0, ',', '.') : null,
        'sejak' => $v ? '1 Oktober 2026' : null, 'terjadwal' => $n === 1 ? 'Rp 6.000.000 mulai 1 Januari 2027' : null];
}
$detail = [
    'view' => 'admin.track.tarif.show',
    'peminatan' => ['kode' => 'TRPL', 'nama' => 'Rekayasa Perangkat Lunak', 'track' => 'Teknik Informatika', 'durasi' => 4, 'aktif' => true],
    'tarif' => $tarif,
    'riwayat' => [
        ['item' => 'Semester 1', 'rupiah' => 'Rp 6.000.000', 'efektif' => '1 Januari 2027', 'oleh' => 'Dimas Prakoso', 'dibuat' => '26 Nov 2026, 09.10 WIB', 'terjadwal' => true],
        ['item' => 'Semester 1', 'rupiah' => 'Rp 5.500.000', 'efektif' => '1 Oktober 2026', 'oleh' => 'Dimas Prakoso', 'dibuat' => '1 Okt 2026, 08.00 WIB', 'terjadwal' => false],
        ['item' => 'Formulir pendaftaran', 'rupiah' => 'Rp 300.000', 'efektif' => '1 Oktober 2026', 'oleh' => 'Dimas Prakoso', 'dibuat' => '1 Okt 2026, 08.00 WIB', 'terjadwal' => false],
    ],
    'bisaUbah' => true,
    'hariIni' => '2026-11-26',
];

return [
    'view' => 'admin.track.tarif.index',
    'peran' => 'Admin Track',
    'pengguna' => ['nama' => 'Dimas Prakoso', 'peran' => 'Admin Track', 'periode' => 'Pendaftaran 2026'],
    'menu' => 'Tarif Peminatan',
    'default' => [
        'semester' => 4,
        'baris' => [
            $b('TRMM', 'Rekayasa Multimedia', 'Teknik Informatika', 4, 'Rp 300.000', ['Rp 5.500.000', 'Rp 4.800.000', 'Rp 4.800.000', 'Rp 4.800.000']),
            $b('TRPL', 'Rekayasa Perangkat Lunak', 'Teknik Informatika', 4, 'Rp 300.000', ['Rp 5.500.000', 'Rp 4.800.000', 'Rp 4.800.000', null]),
            $b('AKT', 'Akuntansi', 'Manajemen & Bisnis', 2, null, ['Rp 4.200.000', 'Rp 4.200.000']),
        ],
        'jumlah' => ['peminatan' => 3, 'lengkap' => 1],
    ],
    'peminatan_ubah' => $detail,
    'peminatan_galat' => $detail + ['errors' => ['nominal.semester_2' => 'Nominal tarif harus lebih dari 0.', 'tanggal_efektif' => 'Tanggal berlaku efektif tidak boleh sebelum hari ini.']],
    'peminatan_baca_keuangan' => ['bisaUbah' => false, 'peran' => 'Admin Keuangan', 'pengguna' => ['nama' => 'Carlos Simatupang', 'peran' => 'Admin Keuangan', 'periode' => 'Pendaftaran 2026']] + $detail,
];
