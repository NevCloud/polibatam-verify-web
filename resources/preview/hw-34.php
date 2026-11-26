<?php

// HW-34 · Peminatan (P07). Kontrak = Track\PeminatanController::index: `peminatan` [{id, kode, nama, track_id, track, durasi, aktif, bisa_hapus, tarif}], `jumlah`, `filter`, `trackTersedia`, `statusTersedia`.
$p = fn (int $id, string $kode, string $nama, int $track, string $namaTrack, int $durasi, bool $aktif, bool $hapus) => [
    'id' => $id, 'kode' => $kode, 'nama' => $nama, 'track_id' => $track, 'track' => $namaTrack, 'durasi' => $durasi, 'aktif' => $aktif, 'bisa_hapus' => $hapus, 'tarif' => '#',
];
$dasar = [
    'filter' => ['q' => '', 'track' => '', 'status' => ''],
    'peminatan' => [
        $p(1, 'TRMM', 'Rekayasa Multimedia', 1, 'Teknik Informatika', 4, true, false),
        $p(2, 'TRPL', 'Rekayasa Perangkat Lunak', 1, 'Teknik Informatika', 4, true, false),
        $p(3, 'AKT', 'Akuntansi', 2, 'Manajemen & Bisnis', 2, true, false),
        $p(4, 'ELK', 'Umum Elektro', 3, 'Teknik Elektro', 2, false, true),
    ],
    'jumlah' => ['total' => 4, 'aktif' => 3, 'track' => 3],
    'trackTersedia' => [1 => 'TI · Teknik Informatika', 2 => 'MB · Manajemen & Bisnis', 3 => 'TE · Teknik Elektro (nonaktif)'],
    'statusTersedia' => ['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif'],
];

return [
    'view' => 'admin.track.peminatan.index',
    'peran' => 'Admin Track',
    'pengguna' => ['nama' => 'Dimas Prakoso', 'peran' => 'Admin Track', 'periode' => 'Pendaftaran 2026'],
    'menu' => 'Peminatan',
    'default' => $dasar,
    'galat_hapus' => $dasar + ['errors' => ['peminatan' => 'Peminatan ini sudah dipakai pendaftaran atau sudah memiliki tarif sehingga tidak dapat dihapus. Nonaktifkan saja.']],
    'kosong' => ['peminatan' => []] + $dasar,
];
