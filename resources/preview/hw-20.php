<?php

// HW-20 · Notifikasi peserta (P03 FE; jenis notifikasi bertambah di P05/P12). Kontrak = NotifikasiController::index:
// `belumDibaca`, `notifikasi` [{judul, isi, waktu, dibaca, href}], `sebelumnya`, `berikutnya`.
$n = fn (string $judul, string $isi, string $waktu, bool $dibaca) => compact('judul', 'isi', 'waktu', 'dibaca') + ['href' => '#'];

return [
    'view' => 'peserta.notifikasi',
    'peran' => 'Calon Peserta',
    'pengguna' => ['nama' => 'Atma Fauzilla', 'peran' => 'Calon Peserta', 'periode' => 'Pendaftaran 2026'],
    'menu' => 'Notifikasi',
    'default' => [
        'belumDibaca' => 3,
        'notifikasi' => [
            $n('Tagihan formulir TG-2026-000412 terbit — bayar sebelum 28 Nov 2026', 'Nominal Rp 300.412 termasuk kode unik.', '20 Nov 2026, 09.12 WIB', false),
            $n('Berkas Anda dinyatakan valid. Pendaftaran Masuk Kuota.', 'Lunasi tagihan formulir dalam 7 hari.', '19 Nov 2026, 15.40 WIB', false),
            $n('Pengingat: periode pendaftaran ditutup 30 November 2026', 'Ajukan formulir sebelum periode ditutup.', '18 Nov 2026, 08.00 WIB', false),
            $n('Formulir PV-2026-000418 berhasil diajukan', 'Menunggu verifikasi berkas oleh Admin SILAU.', '12 Nov 2026, 10.24 WIB', true),
            $n('Email berhasil diverifikasi', 'Akun Anda siap digunakan.', '10 Nov 2026, 19.03 WIB', true),
        ],
        'sebelumnya' => null, 'berikutnya' => null,
    ],
    'kosong' => ['belumDibaca' => 0, 'notifikasi' => [], 'sebelumnya' => null, 'berikutnya' => null],
];
