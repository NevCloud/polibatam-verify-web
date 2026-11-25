<?php

// HW-56 · Rekening institusi (P05). Kontrak = Keuangan\RekeningController::index: `rekening` [..], `jumlah` {total, aktif}, `jenisTersedia`.
$r = fn (int $id, string $bank, string $nomor, string $jenis, bool $aktif, bool $dipakai) => [
    'id' => $id, 'bank' => $bank, 'nomor' => $nomor, 'atas_nama' => 'Politeknik Negeri Batam', 'jenis' => $jenis,
    'jenis_label' => ucfirst($jenis), 'aktif' => $aktif, 'dipakai' => $dipakai,
];
$dasar = [
    'rekening' => [
        $r(1, 'Bank Negara Indonesia (BNI)', '0123456789', 'formulir', true, true),
        $r(2, 'Bank Rakyat Indonesia (BRI)', '009801000123304', 'semester', true, true),
        $r(3, 'Bank Mandiri', '1080012345678', 'formulir', false, true),
        $r(4, 'Bank Syariah Indonesia (BSI)', '7012345678', 'semester', false, false),
    ],
    'jumlah' => ['total' => 4, 'aktif' => 2], 'jenisTersedia' => ['formulir' => 'Formulir', 'semester' => 'Semester'],
];

return [
    'view' => 'admin.keuangan.rekening.index',
    'peran' => 'Admin Keuangan',
    'pengguna' => ['nama' => 'Carlos Simatupang', 'peran' => 'Admin Keuangan', 'periode' => 'Pendaftaran 2026'],
    'menu' => 'Rekening Institusi',
    'default' => $dasar,
    'kosong' => ['rekening' => [], 'jumlah' => ['total' => 0, 'aktif' => 0]] + $dasar,
];
