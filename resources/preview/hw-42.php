<?php

// HW-42 · Tagihan tanpa jatuh tempo (P05). Kontrak = SuperAdmin\TagihanController::tanpaJatuhTempo: `baris` [{id, kode, pemilik, peminatan, jenis, terbit}].
$b = fn (int $id, string $kode, string $pemilik, string $peminatan, string $jenis) => compact('id', 'kode', 'pemilik', 'peminatan', 'jenis') + ['terbit' => '20 Januari 2027'];

return [
    'view' => 'admin.superadmin.tagihan.tanpa-jatuh-tempo',
    'peran' => 'Super Admin',
    'pengguna' => ['nama' => 'Citra Aulia', 'peran' => 'Super Admin', 'periode' => 'Pendaftaran 2026'],
    'menu' => 'Tagihan',
    'default' => ['baris' => [
        $b(201, 'TG-2027-000201', 'Atma Fauzilla', 'Teknik Mesin', 'Semester 3'),
        $b(202, 'TG-2027-000202', 'Atma Fauzilla', 'Rekayasa Perangkat Lunak', 'Semester 2'),
        $b(207, 'TG-2027-000207', 'Dewi Lestari', 'Administrasi Bisnis', 'Semester 2'),
    ]],
    'galat' => ['baris' => [$b(210, 'TG-2027-000210', 'Fajar Nugroho', 'Umum (Sesuai Skema)', 'Semester 2')], 'errors' => ['tagihan' => 'Pilih minimal satu tagihan.']],
    'kosong' => ['baris' => []],
];
