<?php

// HW-02 · Katalog peminatan (P08, K-42 tanpa kuota). Kontrak = Publik\PublikController::katalog: `periode`, `trackTersedia`, `track`, `peminatan` (KatalogQuery::daftar).
$p = fn (string $kode, string $nama, string $track, int $durasi, ?string $form, ?string $smt, bool $dibuka = true) => [
    'kode' => $kode, 'nama' => $nama, 'track' => $track, 'track_kode' => '', 'durasi' => $durasi, 'formulir' => $form, 'semester' => $smt, 'dibuka' => $dibuka, 'href' => '#',
];
$dasar = [
    'navAktif' => 'Program',
    'periode' => ['buka' => true, 'nama' => 'Gelombang 2 · 2026', 'teks' => 'Pendaftaran dibuka 1 Oktober 2026 – 31 Januari 2027'],
    'trackTersedia' => ['TI' => 'Teknik Informatika', 'MB' => 'Manajemen & Bisnis', 'TE' => 'Teknik Elektro', 'TM' => 'Teknik Mesin'],
    'track' => '',
    'peminatan' => [
        $p('TRPL', 'Rekayasa Perangkat Lunak', 'Teknik Informatika', 5, 'Rp 350.000', 'Rp 6.500.000'),
        $p('AKT', 'Akuntansi', 'Manajemen & Bisnis', 4, 'Rp 300.000', 'Rp 5.500.000'),
        $p('TRPF', 'Rekayasa Pengelasan', 'Teknik Mesin', 7, 'Rp 300.000', 'Rp 6.000.000 – Rp 6.500.000'),
        $p('AMTO', 'AMTO Basic License', 'Teknik Mesin', 4, 'Rp 500.000', 'Rp 20.000.000', false),
        $p('ELU', 'Umum', 'Teknik Elektro', 4, null, null, false),
    ],
];

return [
    'view' => 'publik.katalog',
    'peran' => null,
    'pengguna' => null,
    'default' => $dasar,
    'filter_track' => ['track' => 'TM', 'peminatan' => array_slice($dasar['peminatan'], 2, 2)] + $dasar,
    'ditutup' => ['periode' => ['buka' => false, 'nama' => null, 'teks' => 'Pendaftaran ditutup. Periode berikutnya (Gelombang 1 · 2027) dibuka 1 Maret 2027.']] + $dasar,
    'kosong' => ['peminatan' => []] + $dasar,
];
