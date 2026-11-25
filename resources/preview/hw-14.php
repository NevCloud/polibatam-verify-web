<?php

// HW-14 · Pilih track & peminatan (P03). Kontrak = PendaftaranController::pilih:
// `tracks` [{kode, nama, peminatan [{id, nama, durasi} (tanpa info kuota, BR-75)]}], `periode` (teks), `draf` {nomor, pilihan}|null.
$tracks = [
    ['kode' => 'MB', 'nama' => 'Manajemen & Bisnis', 'peminatan' => [['id' => 1, 'nama' => 'Administrasi Bisnis', 'durasi' => 4]]],
    ['kode' => 'TI', 'nama' => 'Teknik Informatika', 'peminatan' => [
        ['id' => 2, 'nama' => 'Rekayasa Multimedia', 'durasi' => 5],
        ['id' => 3, 'nama' => 'Rekayasa Perangkat Lunak', 'durasi' => 5],
    ]],
];
$dasar = ['tracks' => $tracks, 'periode' => 'Gelombang 2 · 2026 · tutup 30 November 2026', 'draf' => null];

return [
    'view' => 'peserta.pendaftaran.pilih-peminatan',
    'peran' => 'Calon Peserta',
    'pengguna' => ['nama' => 'Atma Fauzilla', 'peran' => 'Calon Peserta', 'periode' => 'Pendaftaran 2026'],
    'menu' => 'Formulir',
    'default' => $dasar,
    'ubah_draf' => ['draf' => ['nomor' => 'PV-2026-001284', 'pilihan' => 3]] + $dasar,
    'periode_tutup' => ['tracks' => [], 'periode' => null, 'draf' => null],
    'galat' => $dasar + ['errors' => ['peminatan_periode_id' => 'Anda sudah memiliki pendaftaran aktif (PV-2026-000418) pada periode ini. Pilihan track dan peminatan tidak dapat diubah setelah formulir diajukan.']],
];
