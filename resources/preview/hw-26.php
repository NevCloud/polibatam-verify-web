<?php

// HW-26 · Detail verifikasi berkas (P04). Kontrak = VerifikasiBerkasController::show:
// `pendaftar` (ringkasan + inisial, diajukan), `kuota` {minimum, npd}, `biodata` [label => nilai],
// `dokumen` [{id, jenis, label, status, berkas{nama, info, gambar, url}, versi, bisa_diputuskan, kriteria [{id, teks}], riwayat [{waktu, keputusan, alasan}]}],
// `bisaDiputuskan`. Flash `kode_galat` = SUDAH_DIVERIFIKASI → dialog konflik 409.
$pendaftar = fn (string $status) => [
    'nomor' => 'PV-2026-001284', 'status' => $status, 'track' => 'Teknik Informatika', 'peminatan' => 'Rekayasa Perangkat Lunak',
    'periode' => 'Gelombang 2 · 2026', 'tanggal_daftar' => '4 Oktober 2026', 'draf' => false, 'nama' => 'Atma Fauzila', 'inisial' => 'AF',
    'diajukan' => '5 Okt 2026, 14.25 WIB',
];
$k = fn (array $teks, int $mulai) => array_map(fn ($t, $i) => ['id' => $mulai + $i, 'teks' => $t], $teks, array_keys($teks));
$dok = fn (int $id, string $jenis, string $label, string $nama, string $info, bool $gambar, array $kriteria, string $status = 'Menunggu Verifikasi', array $riwayat = []) => [
    'id' => $id, 'jenis' => $jenis, 'label' => $label, 'status' => $status,
    'berkas' => ['nama' => $nama, 'info' => $info, 'gambar' => $gambar, 'url' => '/images/logo-polibatam-verify.png'],
    'versi' => '2026-10-05 07:25:00.000000', 'bisa_diputuskan' => $status === 'Menunggu Verifikasi', 'kriteria' => $kriteria, 'riwayat' => $riwayat,
];
$dokumen = [
    $dok(1, 'ktp', 'KTP', 'KTP_Atma_Fauzila.jpg', 'JPG · 4 Oktober 2026', true, $k(['Foto KTP terbaca jelas', 'NIK sesuai dengan NIK di formulir', 'Nama dan tanggal lahir sesuai dengan data formulir'], 6)),
    $dok(2, 'ijazah_skl_rapor', 'Ijazah / SKL / Rapor', 'SKL_Atma_Fauzila.pdf', 'PDF · 4 Oktober 2026', false, $k(['Dokumen terbaca jelas dan tidak terpotong', 'Nama sesuai dengan data formulir', 'Terdapat cap/tanda tangan sekolah'], 1)),
    $dok(3, 'skhu_nilai', 'SKHU / Daftar Nilai', 'Daftar_Nilai_Atma_Fauzila.pdf', 'PDF · 4 Oktober 2026', false, $k(['Dokumen terbaca jelas dan tidak terpotong', 'Nama sesuai dengan data formulir'], 4)),
    $dok(4, 'bukti_pendaftaran_polibatam', 'Bukti Pendaftaran Polibatam (opsional)', 'Bukti_Daftar_Polibatam.pdf', 'PDF · 4 Oktober 2026', false, $k(['Dokumen terbaca jelas'], 9)),
];
$dasar = [
    'pendaftar' => $pendaftar('Diajukan'),
    'kuota' => ['minimum' => 10, 'npd' => 4],
    'biodata' => ['Nama lengkap' => 'Atma Fauzila', 'NIK' => '2171 •••• •••• 0003', 'Tempat, tanggal lahir' => 'Batam, 12 Juni 2007',
        'Asal sekolah' => 'SMA Negeri 3 Batam · lulus 2026', 'Email' => 'atma.fauzila@gmail.com', 'No. HP' => '081234567890'],
    'dokumen' => $dokumen,
    'bisaDiputuskan' => true,
];
$revisi = $dokumen;
$revisi[0] = $dok(1, 'ktp', 'KTP', 'KTP_Atma_Fauzila.jpg', 'JPG · 4 Oktober 2026', true, [], 'Valid', [['waktu' => '5 Okt 2026, 13.10 WIB', 'keputusan' => 'Valid', 'alasan' => null]]);
$revisi[2] = $dok(3, 'skhu_nilai', 'SKHU / Daftar Nilai', 'Daftar_Nilai_v2.pdf', 'PDF · 21 November 2026', false, $k(['Dokumen terbaca jelas dan tidak terpotong', 'Nama sesuai dengan data formulir'], 4),
    'Menunggu Verifikasi', [['waktu' => '5 Okt 2026, 13.10 WIB', 'keputusan' => 'Ditolak', 'alasan' => 'Tidak terpenuhi: Dokumen terbaca jelas dan tidak terpotong. Catatan: nilai semester 5 buram.']]);
$selesai = array_map(fn ($d) => ['status' => 'Valid', 'bisa_diputuskan' => false] + $d, $dokumen);

return [
    'view' => 'admin.silau.verifikasi-berkas.show',
    'peran' => 'Admin SILAU',
    'pengguna' => ['nama' => 'Fahad Arifin', 'peran' => 'Admin SILAU', 'periode' => 'Pendaftaran 2026'],
    'menu' => 'Verifikasi Berkas',
    'default' => $dasar,
    'revisi_masuk' => ['dokumen' => $revisi] + $dasar,
    'galat_alasan' => $dasar + ['errors' => [
        'dokumen.3.catatan' => 'SKHU / Daftar Nilai: Alasan penolakan wajib: hilangkan centang kriteria yang tidak terpenuhi dan/atau isi catatan.',
        'dokumen.1.keputusan' => 'KTP: Dokumen hanya dapat dinyatakan Valid bila seluruh kriteria checklist terpenuhi.',
    ]],
    'konflik_409' => ['konflik' => true] + $dasar,
    'sudah_diputuskan' => ['pendaftar' => $pendaftar('Masuk Kuota'), 'dokumen' => $selesai, 'bisaDiputuskan' => false] + $dasar,
];
