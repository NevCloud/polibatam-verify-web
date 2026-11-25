<?php

// HW-16 · Detail berkas & revisi (P03). Kontrak = BerkasController::index:
// `pendaftaran` (ringkasan), `berkas` (lihat hw-15), `riwayat` [{dokumen, versi, berkas, status, alasan}], `batasUnggah`.
$pendaftaran = fn (string $status) => [
    'nomor' => 'PV-2026-001284', 'status' => $status, 'track' => 'Teknik Informatika', 'peminatan' => 'Rekayasa Perangkat Lunak',
    'periode' => 'Gelombang 2 · 2026', 'tanggal_daftar' => '4 Oktober 2026', 'draf' => false, 'nama' => 'Atma Fauzilla',
];
$slot = fn (string $jenis, string $label, bool $wajib, string $status, ?string $nama, bool $bisa = false, ?string $alasan = null) => [
    'jenis' => $jenis, 'label' => $label, 'wajib' => $wajib, 'keterangan' => '', 'status' => $status,
    'berkas' => $nama ? ['nama' => $nama, 'info' => 'PDF · 420 KB · 13 Nov 2026', 'url' => '#'] : null, 'alasan' => $alasan, 'bisa_unggah' => $bisa,
];
$revisi = [
    $slot('ktp', 'KTP', true, 'Valid', 'ktp_dimas.jpg'),
    $slot('ijazah_skl_rapor', 'Ijazah / SKL / Rapor', true, 'Valid', 'ijazah_dimas.pdf'),
    $slot('skhu_nilai', 'SKHU / Daftar Nilai', true, 'Ditolak', 'skhu_dimas.jpg', true, 'Dokumen buram, nilai tidak terbaca.'),
    $slot('bukti_pendaftaran_polibatam', 'Bukti Pendaftaran Polibatam', false, 'Opsional', null),
];
$riwayat = [
    ['dokumen' => 'KTP', 'versi' => 'v1', 'berkas' => 'ktp_dimas.jpg · 13 November 2026', 'status' => 'Valid', 'alasan' => null],
    ['dokumen' => 'Ijazah / SKL / Rapor', 'versi' => 'v1', 'berkas' => 'ijazah_dimas.pdf · 13 November 2026', 'status' => 'Valid', 'alasan' => null],
    ['dokumen' => 'SKHU / Daftar Nilai', 'versi' => 'v1', 'berkas' => 'skhu_dimas.jpg · 13 November 2026', 'status' => 'Ditolak', 'alasan' => 'Dokumen buram, nilai tidak terbaca.'],
];
$diajukanUlang = $revisi;
$diajukanUlang[2] = $slot('skhu_nilai', 'SKHU / Daftar Nilai', true, 'Menunggu Verifikasi', 'skhu_dimas_v2.pdf');

return [
    'view' => 'peserta.pendaftaran.berkas',
    'peran' => 'Calon Peserta',
    'pengguna' => ['nama' => 'Dimas Prakoso', 'peran' => 'Calon Peserta', 'periode' => 'Pendaftaran 2026'],
    'menu' => 'Status',
    'default' => ['pendaftaran' => $pendaftaran('Perlu Revisi'), 'berkas' => $revisi, 'riwayat' => $riwayat, 'batasUnggah' => '2 MB'],
    'diajukan_ulang' => ['pendaftaran' => $pendaftaran('Diajukan'), 'berkas' => $diajukanUlang, 'batasUnggah' => '2 MB', 'riwayat' => [
        ['dokumen' => 'SKHU / Daftar Nilai', 'versi' => 'v2', 'berkas' => 'skhu_dimas_v2.pdf · 21 November 2026', 'status' => 'Menunggu Verifikasi', 'alasan' => null],
        ...$riwayat,
    ]],
    'galat_unggah' => ['pendaftaran' => $pendaftaran('Perlu Revisi'), 'berkas' => $revisi, 'riwayat' => $riwayat, 'batasUnggah' => '2 MB',
        'errors' => ['berkas' => 'Format berkas harus PDF, JPG, atau PNG dengan ukuran maksimal 2 MB.']],
];
