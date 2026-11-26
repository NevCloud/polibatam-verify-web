<?php

// HW-01 · Beranda (P08). Kontrak = Publik\PublikController::dataBeranda: `periode` (KatalogQuery::infoPeriode), `konten` (KatalogQuery::konten), `track`, `tautanDaftar`, `pratinjau`.
$konten = [
    'pengumuman' => ['judul' => 'Pengumuman', 'baris' => ['Gelombang 2 ditutup 31 Januari 2027 pukul 23.59 WIB.', 'Verifikasi berkas diproses maksimal 3 hari kerja.'], 'pasangan' => []],
    'jadwal' => ['judul' => 'Jadwal pendaftaran', 'baris' => ['Verifikasi berkas berjalan bertahap setelah formulir diajukan', 'Pelunasan formulir paling lambat 7 hari setelah Masuk Kuota'], 'pasangan' => []],
    'alur' => ['judul' => '5 langkah mudah, satu perjalanan baru.', 'baris' => [], 'pasangan' => [
        ['Buat akun', 'Daftarkan email dan siapkan akun Polibatam Verify.'], ['Pengisian data', 'Lengkapi biodata, pilih track dan peminatan.'],
        ['Verifikasi berkas admin', 'Unggah berkas dan pantau hasil pemeriksaan.'], ['Verifikasi pembayaran keuangan', 'Bayar tagihan formulir dan unggah bukti.'],
        ['Lihat hasil pendaftaran', 'Cek NPD dan tagihan semester di dasbor.'],
    ]],
    'faq' => ['judul' => 'Pertanyaan yang sering ditanyakan.', 'baris' => [], 'pasangan' => [
        ['Bagaimana cara mendaftar di Polibatam Verify?', 'Buat akun, verifikasi email, lalu lengkapi formulir dan unggah berkas.'],
        ['Di mana saya dapat melihat jadwal pendaftaran?', 'Di beranda dan katalog peminatan.'],
    ]],
    'kontak' => ['judul' => 'Kontak bantuan', 'baris' => ['Jl. Ahmad Yani, Batam Centre, Kota Batam 29461', 'Email: info@polibatam.ac.id'], 'pasangan' => []],
];
$dasar = [
    'pratinjau' => false,
    'periode' => ['buka' => true, 'nama' => 'Gelombang 2 · 2026', 'teks' => 'Pendaftaran dibuka 1 Oktober 2026 – 31 Januari 2027'],
    'konten' => $konten,
    'track' => [
        ['kode' => 'TI', 'nama' => 'Teknik Informatika', 'peminatan' => ['Rekayasa Perangkat Lunak', 'Rekayasa Multimedia'], 'href' => '#'],
        ['kode' => 'MB', 'nama' => 'Manajemen & Bisnis', 'peminatan' => ['Administrasi Bisnis', 'Akuntansi'], 'href' => '#'],
        ['kode' => 'TE', 'nama' => 'Teknik Elektro', 'peminatan' => ['Elektronika'], 'href' => '#'],
        ['kode' => 'TM', 'nama' => 'Teknik Mesin', 'peminatan' => ['Pengelasan', 'AMTO Basic License'], 'href' => '#'],
    ],
    'tautanDaftar' => '#',
    'navAktif' => 'Beranda',
];

return [
    'view' => 'publik.beranda',
    'peran' => null,
    'pengguna' => null,
    'default' => $dasar,
    'ditutup' => ['periode' => ['buka' => false, 'nama' => 'Gelombang 1 · 2027', 'teks' => 'Pendaftaran ditutup. Periode berikutnya (Gelombang 1 · 2027) dibuka 1 Maret 2027.']] + $dasar,
    'pratinjau_cms' => ['pratinjau' => true] + $dasar,
];
