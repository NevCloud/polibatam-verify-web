<?php

// HW-17 · Daftar tagihan peserta (P05). Kontrak = Peserta\TagihanController::index:
// `npd`, `ringkasan` {lunas{nominal,jumlah}, belum{nominal,jumlah}, menunggu_penetapan}, `tagihan` [TampilanTagihan::baris].
$t = fn (string $jenis, string $kode, string $nominal, string $jt, string $status, bool $bayar = false) => [
    'kode' => $kode, 'jenis' => $jenis, 'nominal' => $nominal, 'jatuh_tempo' => $jt, 'status' => $status, 'bisa_bayar' => $bayar, 'href' => '#',
];

return [
    'view' => 'peserta.tagihan.index',
    'peran' => 'Peserta Didik',
    'pengguna' => ['nama' => 'Atma Fauzilla', 'peran' => 'Peserta Didik', 'periode' => 'Pendaftaran 2026'],
    'menu' => 'Tagihan',
    'default' => [
        'npd' => 'MC-TRPL-2026-0031',
        'ringkasan' => ['lunas' => ['nominal' => 'Rp 6.800.413', 'jumlah' => 2], 'belum' => ['nominal' => 'Rp 6.500.271', 'jumlah' => 1], 'menunggu_penetapan' => 1],
        'tagihan' => [
            $t('Formulir pendaftaran', 'TG-2026-000398', 'Rp 300.142', '7 hari setelah Masuk Kuota', 'Lunas'),
            $t('Semester 1', 'TG-2026-000655', 'Rp 6.500.271', '8 Februari 2027', 'Lunas'),
            $t('Semester 2', 'TG-2027-000114', 'Rp 6.500.271', '15 Februari 2027', 'Belum Bayar', true),
            $t('Semester 3', 'TG-2027-000201', 'Rp 6.500.312', 'Menunggu penetapan', 'Ditolak', true),
        ],
    ],
    'kosong' => ['npd' => null, 'ringkasan' => ['lunas' => ['nominal' => 'Rp 0', 'jumlah' => 0], 'belum' => ['nominal' => 'Rp 0', 'jumlah' => 0], 'menunggu_penetapan' => 0], 'tagihan' => []],
];
