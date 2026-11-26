<?php

// HW-46 · CMS landing page (P08). Kontrak = SuperAdmin\CmsController::index: `aktif`, `bagian` [kunci => {label, format, judul, isi, status, terbit_pada, bisa_terbit}].
$b = fn (string $label, string $judul, string $isi, string $status, ?string $terbit, bool $bisa) => [
    'label' => $label, 'format' => 'Satu butir per baris.', 'judul' => $judul, 'isi' => $isi, 'status' => $status, 'terbit_pada' => $terbit, 'bisa_terbit' => $bisa,
];
$bagian = [
    'pengumuman' => $b('Pengumuman', 'Pengumuman', "Gelombang 2 ditutup 31 Januari 2027 pukul 23.59 WIB.\nVerifikasi berkas diproses maksimal 3 hari kerja.", 'Ada perubahan draf belum diterbitkan', '20 Nov 2026, 09.00 WIB', true),
    'jadwal' => $b('Jadwal', 'Jadwal pendaftaran', 'Pelunasan formulir paling lambat 7 hari setelah Masuk Kuota', 'Terbit', '1 Okt 2026, 08.00 WIB', true),
    'alur' => $b('Alur pendaftaran', '5 langkah mudah', "Buat akun | Daftarkan email.\nPengisian data | Lengkapi biodata.", 'Terbit', '1 Okt 2026, 08.00 WIB', true),
    'faq' => $b('FAQ', 'Pertanyaan yang sering ditanyakan.', 'Bagaimana cara mendaftar? | Buat akun lalu lengkapi formulir.', 'Belum diterbitkan (memakai isi bawaan)', null, false),
    'kontak' => $b('Kontak bantuan', 'Kontak bantuan', 'Email: info@polibatam.ac.id', 'Terbit', '1 Okt 2026, 08.00 WIB', true),
];

return [
    'view' => 'admin.superadmin.cms.index',
    'peran' => 'Super Admin',
    'pengguna' => ['nama' => 'Citra Aulia', 'peran' => 'Super Admin', 'periode' => 'Pendaftaran 2026'],
    'menu' => 'CMS Landing Page',
    'default' => ['aktif' => 'pengumuman', 'bagian' => $bagian],
    'faq_belum_terbit' => ['aktif' => 'faq', 'bagian' => $bagian],
    'galat' => ['aktif' => 'jadwal', 'bagian' => $bagian, 'errors' => ['isi' => 'Isi wajib diisi.']],
];
