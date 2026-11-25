<?php

// HW-43 · Cari & koreksi tagihan (P05). Kontrak = SuperAdmin\TagihanController::index / ::show (state detail_* menimpa view).
$b = fn (string $kode, string $nama, string $nomor, string $jenis, string $nominal, string $status) => [
    'kode' => $kode, 'nama' => $nama, 'nomor' => $nomor, 'jenis' => $jenis, 'nominal' => $nominal, 'status' => $status,
    'jatuh_tempo' => '—', 'bisa_bayar' => false, 'href' => '#',
];
$tagihan = ['kode' => 'TG-2026-000415', 'jenis' => 'Formulir pendaftaran', 'nominal' => 'Rp 350.031', 'jatuh_tempo' => '7 hari setelah Masuk Kuota',
    'status' => 'Lunas', 'bisa_bayar' => false, 'href' => '#', 'versi' => '2026-11-18 07:25:00.000000', 'jenis_kode' => 'formulir',
    'nominal_dasar' => 'Rp 350.000', 'kode_unik' => '031', 'nominal_total_angka' => 350031, 'track' => 'Teknik Informatika', 'peminatan' => 'Rekayasa Perangkat Lunak',
    'nomor_pendaftaran' => 'PV-2026-000421', 'status_pendaftaran' => 'NPD Terbit', 'nama' => 'Andi Pratama', 'lunas_pada' => '18 Nov 2026, 10.20 WIB', 'rekening' => null];
$bukti = [['diunggah' => '18 Nov 2026, 08.00 WIB', 'tanggal_transfer' => '18 November 2026', 'nominal_transfer' => 'Rp 350.031', 'selisih' => false,
    'bank_pengirim' => 'Bank BNI', 'nama_pengirim' => 'Andi Pratama', 'keputusan' => 'Lunas', 'alasan' => null, 'gambar' => true, 'url' => '#']];
$detail = ['view' => 'admin.superadmin.tagihan.show', 'tagihan' => $tagihan, 'bukti' => $bukti, 'bisaKoreksi' => true];

return [
    'view' => 'admin.superadmin.tagihan.index',
    'peran' => 'Super Admin',
    'pengguna' => ['nama' => 'Citra Aulia', 'peran' => 'Super Admin', 'periode' => 'Pendaftaran 2026'],
    'menu' => 'Tagihan',
    'default' => [
        'filter' => ['q' => 'TG-2026-000415', 'jenis' => '', 'status' => ''],
        'baris' => [$b('TG-2026-000415', 'Andi Pratama', 'PV-2026-000421', 'Formulir pendaftaran', 'Rp 350.031', 'Lunas')],
        'statusTersedia' => ['belum_bayar' => 'Belum Bayar', 'menunggu_verifikasi' => 'Menunggu Verifikasi', 'lunas' => 'Lunas', 'ditolak' => 'Ditolak', 'dibatalkan' => 'Dibatalkan'],
        'total' => 1, 'rentang' => '1–1', 'tautan' => [['url' => '#', 'label' => '1', 'active' => true]], 'sebelumnya' => null, 'berikutnya' => null,
    ],
    'detail_lunas' => $detail,
    'detail_galat_alasan' => $detail + ['errors' => ['alasan' => 'Alasan koreksi wajib diisi (minimal 10 karakter) dan akan tercatat di audit log.']],
    'detail_ditolak' => ['tagihan' => ['status' => 'Ditolak', 'lunas_pada' => null] + $tagihan, 'bisaKoreksi' => false] + $detail,
];
