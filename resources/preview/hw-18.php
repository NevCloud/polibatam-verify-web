<?php

// HW-18 · Detail tagihan & unggah bukti (P05). Kontrak = Peserta\TagihanController::show:
// `tagihan` (TampilanTagihan::rincian), `bukti` [TampilanTagihan::bukti], `pendaftaran` (ringkasan), `menungguBerkas`, `batasUnggah`.
$tagihan = [
    'kode' => 'TG-2026-000412', 'jenis' => 'Formulir pendaftaran', 'nominal' => 'Rp 350.284', 'jatuh_tempo' => '7 hari setelah Masuk Kuota',
    'status' => 'Belum Bayar', 'bisa_bayar' => true, 'href' => '#', 'versi' => '2026-10-05 07:25:00.000000', 'jenis_kode' => 'formulir',
    'nominal_dasar' => 'Rp 350.000', 'kode_unik' => '284', 'nominal_total_angka' => 350284, 'track' => 'Teknik Informatika',
    'peminatan' => 'Rekayasa Perangkat Lunak', 'nomor_pendaftaran' => 'PV-2026-001284', 'status_pendaftaran' => 'Diajukan', 'nama' => 'Atma Fauzilla',
    'lunas_pada' => null, 'rekening' => ['bank' => 'Bank Negara Indonesia (BNI)', 'nomor' => '0123456789', 'atas_nama' => 'Politeknik Negeri Batam'],
];
$ditolak = ['diunggah' => '5 Okt 2026, 10.12 WIB', 'tanggal_transfer' => '5 Oktober 2026', 'nominal_transfer' => 'Rp 350.000', 'selisih' => true,
    'bank_pengirim' => 'Bank BNI', 'nama_pengirim' => 'Atma Fauzilla', 'keputusan' => 'Ditolak',
    'alasan' => 'Nominal tidak sesuai. Catatan: transfer Rp 350.000, seharusnya Rp 350.284.', 'gambar' => true, 'url' => '#'];
$pendaftaran = ['nomor' => 'PV-2026-001284', 'status' => 'Diajukan', 'track' => 'Teknik Informatika', 'peminatan' => 'Rekayasa Perangkat Lunak',
    'periode' => 'Gelombang 2 · 2026', 'tanggal_daftar' => '4 Oktober 2026', 'draf' => false, 'nama' => 'Atma Fauzilla'];
$dasar = ['tagihan' => $tagihan, 'bukti' => [], 'pendaftaran' => $pendaftaran, 'menungguBerkas' => true, 'batasUnggah' => '2 MB'];

return [
    'view' => 'peserta.tagihan.show',
    'peran' => 'Calon Peserta',
    'pengguna' => ['nama' => 'Atma Fauzilla', 'peran' => 'Calon Peserta', 'periode' => 'Pendaftaran 2026'],
    'menu' => 'Tagihan',
    'default' => $dasar,
    'ditolak_unggah_ulang' => ['tagihan' => ['status' => 'Ditolak'] + $tagihan, 'bukti' => [$ditolak]] + $dasar,
    'menunggu_verifikasi' => ['tagihan' => ['status' => 'Menunggu Verifikasi', 'bisa_bayar' => false] + $tagihan,
        'bukti' => [['keputusan' => 'Menunggu Verifikasi', 'alasan' => null, 'selisih' => false, 'nominal_transfer' => 'Rp 350.284'] + $ditolak, $ditolak]] + $dasar,
    'galat' => $dasar + ['errors' => ['berkas' => 'Bukti ini sudah pernah digunakan. Unggah bukti transfer yang lain.', 'tanggal_transfer' => 'Tanggal transfer tidak boleh melebihi hari ini.']],
];
