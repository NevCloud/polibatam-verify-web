<?php

// HW-29 · Detail verifikasi pembayaran (P05). Kontrak = VerifikasiPembayaranController::show:
// `tagihan` (rincian), `bukti` [..], `gerbang`, `bisaDiputuskan`, `pengirimBerbeda`, `alasanBaku`. `konflik` = state preview 409.
$tagihan = [
    'kode' => 'TG-2026-000412', 'jenis' => 'Formulir pendaftaran', 'nominal' => 'Rp 350.284', 'jatuh_tempo' => '7 hari setelah Masuk Kuota',
    'status' => 'Menunggu Verifikasi', 'bisa_bayar' => false, 'href' => '#', 'versi' => '2026-10-05 07:25:00.000000', 'jenis_kode' => 'formulir',
    'nominal_dasar' => 'Rp 350.000', 'kode_unik' => '284', 'nominal_total_angka' => 350284, 'track' => 'Teknik Informatika',
    'peminatan' => 'Rekayasa Perangkat Lunak', 'nomor_pendaftaran' => 'PV-2026-001284', 'status_pendaftaran' => 'Masuk Kuota', 'nama' => 'Atma Fauzila',
    'lunas_pada' => null, 'rekening' => ['bank' => 'Bank Negara Indonesia (BNI)', 'nomor' => '0123456789', 'atas_nama' => 'Politeknik Negeri Batam'],
];
$bukti = ['diunggah' => '4 Okt 2026, 13.12 WIB', 'tanggal_transfer' => '4 Oktober 2026', 'nominal_transfer' => 'Rp 350.284', 'selisih' => false,
    'bank_pengirim' => 'Bank Negara Indonesia (BNI)', 'nama_pengirim' => 'Budi Santoso', 'keputusan' => 'Menunggu Verifikasi', 'alasan' => null,
    'gambar' => true, 'url' => '/images/logo-polibatam-verify.png'];
$alasan = ['mutasi_tidak_ditemukan' => 'Mutasi tidak ditemukan', 'nominal_tidak_sesuai' => 'Nominal tidak sesuai', 'bukti_tidak_terbaca' => 'Bukti tidak terbaca'];
$dasar = ['tagihan' => $tagihan, 'bukti' => [$bukti], 'gerbang' => true, 'bisaDiputuskan' => true, 'pengirimBerbeda' => true, 'alasanBaku' => $alasan];

return [
    'view' => 'admin.keuangan.verifikasi-pembayaran.show',
    'peran' => 'Admin Keuangan',
    'pengguna' => ['nama' => 'Carlos Simatupang', 'peran' => 'Admin Keuangan', 'periode' => 'Pendaftaran 2026'],
    'menu' => 'Verifikasi Pembayaran',
    'default' => $dasar,
    'gerbang_tertutup' => ['tagihan' => ['status_pendaftaran' => 'Diajukan'] + $tagihan, 'gerbang' => false] + $dasar,
    'galat_alasan' => $dasar + ['errors' => ['catatan' => 'Alasan penolakan wajib: pilih alasan baku dan/atau isi catatan.']],
    'konflik_409' => ['konflik' => true] + $dasar,
    'sudah_lunas' => ['tagihan' => ['status' => 'Lunas', 'lunas_pada' => '5 Okt 2026, 09.00 WIB'] + $tagihan, 'bisaDiputuskan' => false, 'pengirimBerbeda' => false,
        'bukti' => [['keputusan' => 'Lunas', 'nama_pengirim' => 'Atma Fauzila'] + $bukti]] + $dasar,
];
