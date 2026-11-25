<?php

// HW-13 · Dasbor peserta + halaman Status & riwayat (P03). Kontrak = DasborController::index / ::status
// (bentuk array dari App\Support\Ui\TampilanPendaftaran). State `status_*` menimpa view & menu (A-69).
$pendaftaran = fn (string $status, bool $draf = false) => [
    'nomor' => 'PV-2026-001284', 'status' => $status, 'track' => 'Teknik Informatika', 'peminatan' => 'Rekayasa Perangkat Lunak',
    'periode' => 'Gelombang 2 · 2026', 'tanggal_daftar' => '4 Oktober 2026', 'draf' => $draf, 'nama' => 'Atma Fauzilla',
];
$langkah = function (int $aktif) {
    $label = ['Formulir Diajukan', 'Verifikasi Berkas', 'Masuk Kuota', 'Verifikasi Pembayaran', 'NPD Terbit'];

    return array_map(fn ($i) => ['label' => $label[$i], 'state' => $i < $aktif ? 'selesai' : ($i === $aktif ? 'aktif' : 'belum')], range(0, 4));
};
$notifikasi = [
    ['judul' => 'Draf formulir disimpan', 'isi' => '', 'waktu' => '5 Okt 2026, 10.42 WIB', 'dibaca' => false, 'href' => '#'],
    ['judul' => 'Nomor pendaftaran PV-2026-001284 dibuat', 'isi' => '', 'waktu' => '4 Okt 2026, 09.15 WIB', 'dibaca' => true, 'href' => '#'],
    ['judul' => 'Email berhasil diverifikasi', 'isi' => '', 'waktu' => '4 Okt 2026, 08.40 WIB', 'dibaca' => true, 'href' => '#'],
];
$dasbor = fn (string $status, int $aktif, array $banner, bool $draf = false) => [
    'sapaan' => 'Atma',
    'notifikasi' => $notifikasi,
    'pendaftaran' => $pendaftaran($status, $draf),
    'tracker' => ['langkah' => $langkah($aktif), 'keterangan' => $draf ? 'Tahap 1 dari 5 · Draf, belum diajukan' : 'Tahap '.($aktif + 1).' dari 5 · '.$status],
    'banner' => $banner,
    'kartu' => $draf ? ['kolom_terisi' => 8, 'kolom_total' => 9, 'berkas_terunggah' => 2, 'berkas_total' => 3] : ['kolom_terisi' => 9, 'kolom_total' => 9, 'berkas_terunggah' => 3, 'berkas_total' => 3],
    'biodata' => ['nama' => 'Atma Fauzilla', 'nik' => '2171 •••• •••• 0003', 'email' => 'maba.atma@polibatam.ac.id', 'no_hp' => '+62 812-3456-7890'],
    'tautan' => ['formulir' => '#', 'berkas' => '#', 'kartu' => $draf ? null : '#'],
];
$riwayatDasar = [
    ['judul' => 'Draf disimpan', 'waktu' => '4 Okt 2026, 14.20 WIB', 'teks' => 'Nomor pendaftaran PV-2026-001284 dibuat.'],
    ['judul' => 'Formulir diajukan', 'waktu' => '5 Okt 2026, 11.05 WIB', 'teks' => 'Formulir dan berkas wajib diajukan untuk diverifikasi.'],
];
$valid = ['judul' => 'Verifikasi berkas selesai', 'waktu' => '5 Okt 2026, 13.10 WIB', 'teks' => 'Seluruh berkas wajib dinyatakan Valid oleh Admin SILAU.'];
$status = fn (string $st, array $banner, array $riwayat, array $langkah) => [
    'view' => 'peserta.status', 'menu' => 'Status',
    'pendaftaran' => $pendaftaran($st), 'banner' => $banner, 'riwayat' => $riwayat, 'diperbarui' => '5 Okt 2026, 13.30 WIB',
    'langkah' => $langkah, 'tautan' => ['kartu' => '#', 'berkas' => '#'],
];

return [
    'view' => 'peserta.dasbor',
    'peran' => 'Calon Peserta',
    'pengguna' => ['nama' => 'Atma Fauzilla', 'peran' => 'Calon Peserta', 'periode' => 'Pendaftaran 2026'],
    'menu' => 'Dashboard',
    'default' => $dasbor('Draf', 0, ['nada' => 'info', 'judul' => 'Ajukan formulir sebelum 30 November 2026',
        'teks' => 'Lengkapi tahun lulus dan unggah SKHU / Daftar Nilai, lalu ajukan dari tab Tinjau & Ajukan. Draf yang belum diajukan otomatis dibatalkan saat periode ditutup.'], true),
    'diajukan' => $dasbor('Diajukan', 1, ['nada' => 'info', 'judul' => 'Formulir sudah diajukan',
        'teks' => 'Berkas Anda menunggu verifikasi Admin SILAU. Anda akan menerima notifikasi setiap ada perubahan status.']),
    'masuk_kuota' => $dasbor('Masuk Kuota', 3, ['nada' => 'success', 'judul' => 'Berkas valid dan Anda Masuk Kuota',
        'teks' => 'Lunasi tagihan formulir paling lambat 12 Oktober 2026 agar pendaftaran tidak dibatalkan (batas 7 hari).']),
    'kosong' => ['sapaan' => 'Atma', 'notifikasi' => [], 'pendaftaran' => null, 'periodeBuka' => ['nama' => 'Gelombang 2 · 2026', 'tutup' => '30 November 2026']],
    'kosong_tanpa_periode' => ['sapaan' => 'Atma', 'notifikasi' => [], 'pendaftaran' => null, 'periodeBuka' => null],

    'status_masuk_kuota' => $status('Masuk Kuota', ['nada' => 'success', 'judul' => 'Berkas valid dan Anda Masuk Kuota',
        'teks' => 'Lunasi tagihan formulir paling lambat 12 Oktober 2026 agar pendaftaran tidak dibatalkan (batas 7 hari).'],
        [...$riwayatDasar, $valid, ['judul' => 'Masuk Kuota', 'waktu' => '5 Okt 2026, 13.30 WIB', 'teks' => 'Berkas valid. Lunasi tagihan formulir untuk mendapatkan NPD.']],
        ['badge' => 'Verifikasi Pembayaran Formulir', 'teks' => 'Bayar sesuai nominal tagihan (termasuk kode unik), lalu unggah bukti. NPD terbit setelah pembayaran disahkan Admin Keuangan.', 'tombol' => 'Lihat tagihan formulir', 'href' => '#']),
    'status_perlu_revisi' => $status('Perlu Revisi', ['nada' => 'error', 'judul' => '1 dokumen perlu diperbaiki',
        'teks' => 'Admin SILAU menolak sebagian berkas. Periksa alasannya lalu unggah ulang dokumen yang ditolak.'],
        [...$riwayatDasar, ['judul' => 'Perlu Revisi', 'waktu' => '5 Okt 2026, 13.10 WIB', 'teks' => '1 dokumen ditolak Admin SILAU. Lihat alasannya di halaman Berkas.']],
        ['badge' => 'Perlu Revisi', 'teks' => 'Unggah ulang hanya dokumen yang ditolak. Dokumen Valid terkunci.', 'tombol' => 'Buka berkas & revisi', 'href' => '#']),
    'status_npd_terbit' => $status('NPD Terbit', ['nada' => 'success', 'judul' => 'NPD terbit · MC-TRPL-2026-0001',
        'teks' => 'Selamat, Anda resmi menjadi Peserta Didik. Tagihan Semester 1 dapat dilihat di menu Tagihan.'],
        [...$riwayatDasar, $valid, ['judul' => 'Masuk Kuota', 'waktu' => '5 Okt 2026, 13.30 WIB', 'teks' => 'Berkas valid. Lunasi tagihan formulir untuk mendapatkan NPD.'],
            ['judul' => 'NPD Terbit', 'waktu' => '7 Okt 2026, 09.00 WIB', 'teks' => 'Nomor Peserta Didik MC-TRPL-2026-0001 terbit.']],
        ['badge' => 'NPD Terbit', 'teks' => 'Pantau tagihan semester dan status keaktifan Anda dari dasbor.', 'tombol' => null, 'href' => null]),
    'status_dibatalkan' => $status('Dibatalkan', ['nada' => 'error', 'judul' => 'Pendaftaran dibatalkan',
        'teks' => 'Tagihan formulir tidak dilunasi dalam 7 hari setelah Masuk Kuota. Anda dapat mendaftar kembali pada periode pendaftaran berikutnya.'],
        [...$riwayatDasar, $valid, ['judul' => 'Dibatalkan', 'waktu' => '12 Okt 2026, 13.30 WIB', 'teks' => 'Tagihan formulir tidak dilunasi dalam 7 hari setelah Masuk Kuota.']],
        ['badge' => 'Dibatalkan', 'teks' => 'Pendaftaran ini sudah berakhir. Bila tagihan sudah lunas, refund diproses lewat menu Refund. Anda dapat mendaftar lagi saat periode baru dibuka.', 'tombol' => null, 'href' => null]),
    'status_peminatan_dibatalkan' => $status('Dibatalkan', ['nada' => 'error', 'judul' => 'Pendaftaran dibatalkan',
        'teks' => 'Peminatan dibatalkan karena jumlah pendaftar terverifikasi di bawah kuota minimum. Anda dapat mendaftar kembali pada periode pendaftaran berikutnya.'],
        [...$riwayatDasar, $valid, ['judul' => 'Dibatalkan', 'waktu' => '2 Des 2026, 10.00 WIB', 'teks' => 'Peminatan dibatalkan karena jumlah pendaftar terverifikasi di bawah kuota minimum.']],
        ['badge' => 'Dibatalkan', 'teks' => 'Pendaftaran ini sudah berakhir. Bila tagihan sudah lunas, refund diproses lewat menu Refund. Anda dapat mendaftar lagi saat periode baru dibuka.', 'tombol' => null, 'href' => null]),
];
