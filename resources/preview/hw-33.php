<?php

// HW-33 · Track (P07). Kontrak = Track\TrackController::index: `track` [{id, kode, nama, aktif, peminatan, bisa_hapus}], `jumlah`, `filter`, `statusTersedia`.
$t = fn (int $id, string $kode, string $nama, bool $aktif, int $n) => compact('id', 'kode', 'nama', 'aktif') + ['peminatan' => $n, 'bisa_hapus' => $n === 0];
$dasar = [
    'filter' => ['q' => '', 'status' => ''],
    'track' => [$t(1, 'TI', 'Teknik Informatika', true, 3), $t(2, 'MB', 'Manajemen & Bisnis', true, 2), $t(3, 'TE', 'Teknik Elektro', false, 0), $t(4, 'TM', 'Teknik Mesin', true, 1)],
    'jumlah' => ['total' => 4, 'aktif' => 3],
    'statusTersedia' => ['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif'],
];

return [
    'view' => 'admin.track.track.index',
    'peran' => 'Admin Track',
    'pengguna' => ['nama' => 'Dimas Prakoso', 'peran' => 'Admin Track', 'periode' => 'Pendaftaran 2026'],
    'menu' => 'Track',
    'default' => $dasar,
    'galat_kode' => $dasar + ['errors' => ['kode' => 'Kode track sudah digunakan.']],
    'kosong' => ['track' => [], 'filter' => ['q' => 'xyz', 'status' => '']] + $dasar,
];
