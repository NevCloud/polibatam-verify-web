<?php

// HW-37 · Dasbor kuota (P07, K-42). Kontrak = Track\KuotaController::dasbor: `periode`, `periodeTersedia`, `baris` (KuotaQuery::baris), `jumlah`.
$b = fn (string $nama, string $track, int $diajukan, int $mk, int $npd, int $min, string $penanda, string $nada) => [
    'id' => 0, 'peminatan_id' => 0, 'peminatan' => $nama, 'kode' => '', 'track' => $track, 'kuota_min' => $min, 'diajukan' => $diajukan,
    'masuk_kuota' => $mk, 'npd_terbit' => $npd, 'persen' => $min ? (int) round($npd / $min * 100) : null, 'penanda' => $penanda, 'nada' => $nada, 'dibatalkan' => false,
];
$dasar = [
    'periode' => ['id' => 1, 'nama' => 'Gelombang 2 · 2026', 'rentang' => '1 Oktober 2026 – 31 Januari 2027', 'sisa_hari' => 5, 'ditutup' => false],
    'periodeTersedia' => [1 => 'Gelombang 2 · 2026'],
    'baris' => [
        $b('Rekayasa Perangkat Lunak', 'Teknik Informatika', 4, 7, 21, 15, 'Tercapai', 'success'),
        $b('Akuntansi', 'Manajemen & Bisnis', 2, 22, 8, 10, 'Peringatan', 'warning'),
        $b('Teknik Mesin', 'Teknik Mesin', 0, 7, 5, 10, 'Peringatan', 'warning'),
        $b('AMTO Basic License', 'Teknik Mesin', 1, 4, 12, 0, 'Tercapai', 'success'),
    ],
    'jumlah' => ['diajukan' => 7, 'masuk_kuota' => 40, 'npd_terbit' => 46, 'di_bawah' => 2],
];

return [
    'view' => 'admin.track.kuota.dasbor',
    'peran' => 'Admin Track',
    'pengguna' => ['nama' => 'Dimas Prakoso', 'peran' => 'Admin Track', 'periode' => 'Pendaftaran 2026'],
    'menu' => 'Dasbor Kuota',
    'default' => $dasar,
    'ditutup' => ['periode' => ['ditutup' => true, 'sisa_hari' => 0] + $dasar['periode'],
        'baris' => [$b('Teknik Mesin', 'Teknik Mesin', 0, 7, 5, 10, 'Di bawah minimum', 'error')]] + $dasar,
    'silau_kosong' => ['periode' => null, 'periodeTersedia' => [], 'baris' => [], 'peran' => 'Admin SILAU',
        'pengguna' => ['nama' => 'Bayu Prasetyo', 'peran' => 'Admin SILAU', 'periode' => 'Pendaftaran 2026']] + $dasar,
];
