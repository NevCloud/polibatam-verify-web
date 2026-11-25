<?php

// HW-28 · Antrean verifikasi pembayaran (P05). Kontrak = App\Queries\AntreanPembayaranQuery::halaman().
$b = fn (string $nomor, string $nama, string $kode, string $nominal, string $jenis, string $tgl, string $status) => [
    'nomor' => $nomor, 'nama' => $nama, 'kode' => $kode, 'nominal' => $nominal, 'jenis' => $jenis, 'transfer' => $tgl, 'status' => $status,
    'perlu_tindakan' => $status === 'Menunggu Verifikasi', 'href' => '#',
];
$dasar = [
    'filter' => ['tab' => 'menunggu_verifikasi', 'q' => '', 'jenis' => '', 'peminatan' => '', 'periode' => ''],
    'baris' => [
        $b('PV-2026-001284', 'Atma Fauzila', 'TG-2026-000412', 'Rp 350.284', 'Formulir pendaftaran', '4 Oktober 2026', 'Menunggu Verifikasi'),
        $b('PV-2026-001274', 'Aditya Rahman', 'TG-2026-000655', 'Rp 6.500.117', 'Semester 1', '4 Oktober 2026', 'Menunggu Verifikasi'),
        $b('PV-2026-001248', 'Fajar Nugroho', 'TG-2026-000401', 'Rp 350.031', 'Formulir pendaftaran', '2 Oktober 2026', 'Menunggu Verifikasi'),
    ],
    'jumlahTab' => ['semua' => 452, 'menunggu_verifikasi' => 23, 'lunas' => 412, 'ditolak' => 7], 'lunasHariIni' => 'Rp 8.050.000',
    'total' => 23, 'rentang' => '1–20', 'tautan' => [['url' => '#', 'label' => '1', 'active' => true], ['url' => '#', 'label' => '2', 'active' => false]],
    'sebelumnya' => null, 'berikutnya' => '#',
    'tabTersedia' => ['menunggu_verifikasi' => 'Menunggu Verifikasi', 'semua' => 'Semua', 'lunas' => 'Lunas', 'ditolak' => 'Ditolak'],
    'jenisTersedia' => ['formulir' => 'Formulir', 'semester' => 'Semester'],
    'peminatanTersedia' => [5 => 'Rekayasa Perangkat Lunak'], 'periodeTersedia' => [1 => 'Periode Ganjil 2027'],
];

return [
    'view' => 'admin.keuangan.verifikasi-pembayaran.index',
    'peran' => 'Admin Keuangan',
    'pengguna' => ['nama' => 'Carlos Simatupang', 'peran' => 'Admin Keuangan', 'periode' => 'Pendaftaran 2026'],
    'menu' => 'Verifikasi Pembayaran',
    'default' => $dasar,
    'tab_semua' => ['filter' => ['tab' => 'semua'] + $dasar['filter'], 'baris' => [...$dasar['baris'],
        $b('PV-2026-001269', 'Siti Maharani', 'TG-2026-000390', 'Rp 350.120', 'Formulir pendaftaran', '3 Oktober 2026', 'Lunas'),
        $b('PV-2026-001255', 'Maria Ulfa', 'TG-2026-000377', 'Rp 6.500.117', 'Semester 1', '2 Oktober 2026', 'Ditolak')]] + $dasar,
    'kosong' => ['baris' => [], 'total' => 0, 'rentang' => '0', 'tautan' => [], 'berikutnya' => null] + $dasar,
];
