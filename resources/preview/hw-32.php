<?php

// HW-32 · Rekap & ekspor keuangan (P05). Kontrak = App\Queries\RekapKeuanganQuery::halaman().
$k = fn (string $rp, int $n) => ['nominal' => $rp, 'jumlah' => $n];
$dasar = [
    'filter' => ['periode' => '', 'peminatan' => '', 'angkatan' => '', 'jenis' => ''],
    'ringkasan' => ['lunas' => $k('Rp 142.380.512', 312), 'menunggu' => $k('Rp 9.814.220', 18), 'belum' => $k('Rp 27.105.338', 64), 'tunggakan' => $k('Rp 13.000.542', 2)],
    'baris' => [
        ['peminatan' => 'Rekayasa Perangkat Lunak', 'lunas' => 58, 'nominal_lunas' => 'Rp 46.512.300', 'belum' => 9, 'tunggakan' => 'Rp 0'],
        ['peminatan' => 'Teknik Mesin', 'lunas' => 41, 'nominal_lunas' => 'Rp 31.220.180', 'belum' => 6, 'tunggakan' => 'Rp 6.500.271'],
        ['peminatan' => 'AMTO Basic License', 'lunas' => 12, 'nominal_lunas' => 'Rp 44.006.032', 'belum' => 2, 'tunggakan' => 'Rp 6.500.271'],
    ],
    'periodeTersedia' => [1 => 'Gelombang 2 · 2026'], 'peminatanTersedia' => [5 => 'Rekayasa Perangkat Lunak'],
    'angkatanTersedia' => [2026 => 2026], 'jenisTersedia' => ['formulir' => 'Formulir', 'semester' => 'Semester'],
];

return [
    'view' => 'admin.keuangan.rekap.index',
    'peran' => 'Admin Keuangan',
    'pengguna' => ['nama' => 'Carlos Simatupang', 'peran' => 'Admin Keuangan', 'periode' => 'Pendaftaran 2026'],
    'menu' => 'Rekap & Ekspor',
    'default' => $dasar,
    'kosong' => ['baris' => [], 'ringkasan' => ['lunas' => $k('Rp 0', 0), 'menunggu' => $k('Rp 0', 0), 'belum' => $k('Rp 0', 0), 'tunggakan' => $k('Rp 0', 0)]] + $dasar,
];
