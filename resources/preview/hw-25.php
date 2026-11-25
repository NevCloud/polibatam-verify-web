<?php

// HW-25 · Antrean verifikasi berkas (P04). Kontrak = App\Queries\AntreanVerifikasiQuery::halaman():
// `filter` {q, track, peminatan, periode, jenis}, `baris` [{nomor, nama, nik, peminatan, diajukan, jenis, href}], `menunggu`,
// `total`, `rentang`, `tautan`, `sebelumnya`, `berikutnya`, `trackTersedia`, `peminatanTersedia`, `periodeTersedia`, `jenisTersedia`.
$b = fn (string $nomor, string $nama, string $nik, string $peminatan, string $tgl, string $jenis = 'Diajukan') => [
    'nomor' => $nomor, 'nama' => $nama, 'nik' => "2171 •••• •••• {$nik}", 'peminatan' => $peminatan, 'diajukan' => $tgl, 'jenis' => $jenis, 'href' => '#',
];
$dasar = [
    'filter' => ['q' => '', 'track' => '', 'peminatan' => '', 'periode' => '', 'jenis' => ''],
    'baris' => [
        $b('PV-2026-000433', 'Siti Aisyah', '2210', 'Akuntansi', '18 Nov 2026'),
        $b('PV-2026-000440', 'Andi Pratama', '2251', 'Rekayasa Multimedia', '18 Nov 2026'),
        $b('PV-2026-000447', 'Dewi Lestari', '2292', 'Administrasi Bisnis', '19 Nov 2026', 'Revisi masuk'),
        $b('PV-2026-000454', 'Fajar Nugroho', '2333', 'Umum (Sesuai Skema)', '19 Nov 2026'),
        $b('PV-2026-000461', 'Putri Anggraini', '2374', 'Rekayasa Pengelasan', '20 Nov 2026'),
        $b('PV-2026-000468', 'Yoga Saputra', '2415', 'AMTO Basic License', '20 Nov 2026'),
    ],
    'menunggu' => 42, 'total' => 42, 'rentang' => '1–20',
    'tautan' => [['url' => '#', 'label' => '1', 'active' => true], ['url' => '#', 'label' => '2', 'active' => false], ['url' => '#', 'label' => '3', 'active' => false]],
    'sebelumnya' => null, 'berikutnya' => '#',
    'trackTersedia' => ['MB' => 'Manajemen & Bisnis', 'TE' => 'Teknik Elektro', 'TI' => 'Teknik Informatika', 'TM' => 'Teknik Mesin'],
    'peminatanTersedia' => [1 => 'Administrasi Bisnis', 2 => 'Akuntansi', 5 => 'Rekayasa Perangkat Lunak'],
    'periodeTersedia' => [1 => 'Periode Ganjil 2027'],
    'jenisTersedia' => ['baru' => 'Pengajuan baru', 'revisi' => 'Revisi masuk'],
];

return [
    'view' => 'admin.silau.verifikasi-berkas.index',
    'peran' => 'Admin SILAU',
    'pengguna' => ['nama' => 'Fahad Arifin', 'peran' => 'Admin SILAU', 'periode' => 'Pendaftaran 2026'],
    'menu' => 'Verifikasi Berkas',
    'default' => $dasar,
    'filter_revisi' => ['filter' => ['jenis' => 'revisi'] + $dasar['filter'], 'baris' => [$dasar['baris'][2]], 'total' => 1, 'rentang' => '1–1',
        'tautan' => [['url' => '#', 'label' => '1', 'active' => true]], 'berikutnya' => null] + $dasar,
    'kosong' => ['baris' => [], 'menunggu' => 0, 'total' => 0, 'rentang' => '0', 'tautan' => [], 'berikutnya' => null] + $dasar,
];
