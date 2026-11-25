<?php

// HW-15 · Formulir pendaftaran 3 tab (P03). Kontrak = PendaftaranController::formulir:
// `pendaftaran` (ringkasan), `data` (isian Data Diri), `berkas` [{jenis, label, wajib, keterangan, status, berkas{nama, info, url}|null, alasan, bisa_unggah}],
// `tinjau` [label => nilai], `kekurangan` {data, berkas}, `terisi`, `tab` (data|berkas|tinjau), `batasUnggah`, `disimpan`.
$pendaftaran = fn (string $status = 'Draf', bool $draf = true) => [
    'nomor' => 'PV-2026-001284', 'status' => $status, 'track' => 'Teknik Informatika', 'peminatan' => 'Rekayasa Perangkat Lunak',
    'periode' => 'Gelombang 2 · 2026', 'tanggal_daftar' => '4 Oktober 2026', 'draf' => $draf, 'nama' => 'Atma Fauzilla',
];
$data = ['nik' => '2171020807070003', 'nama_lengkap' => 'Atma Fauzilla', 'tempat_lahir' => 'Batam', 'tanggal_lahir' => '2007-07-08', 'jenis_kelamin' => 'P',
    'no_hp' => '+6281234567890', 'alamat' => 'Jl. Raja Haji Fisabilillah, Perumahan Citra Batam Blok D No. 12, Teluk Tering, Batam Kota, Kota Batam',
    'asal_sekolah' => 'SMA Negeri 3 Batam', 'tahun_lulus' => 2026];
$slot = fn (string $jenis, string $label, bool $wajib, string $ket, string $status, ?string $nama, ?string $info, bool $bisa = true, ?string $alasan = null) => [
    'jenis' => $jenis, 'label' => $label, 'wajib' => $wajib, 'keterangan' => $ket, 'status' => $status,
    'berkas' => $nama ? ['nama' => $nama, 'info' => $info, 'url' => '#'] : null, 'alasan' => $alasan, 'bisa_unggah' => $bisa,
];
$berkasLengkap = [
    $slot('ktp', 'KTP', true, 'Seluruh informasi terbaca jelas', 'Terunggah', 'KTP_Atma_Fauzilla.jpg', 'JPG · 1,2 MB · 4 Okt 2026'),
    $slot('ijazah_skl_rapor', 'Ijazah / SKL / Rapor', true, 'Ijazah, SKL, atau Rapor semester 5', 'Terunggah', 'SKL_Atma_Fauzilla.pdf', 'PDF · 850 KB · 4 Okt 2026'),
    $slot('skhu_nilai', 'SKHU / Daftar Nilai', true, 'SKHU, Nilai UN, SKL, atau Daftar Nilai', 'Terunggah', 'Daftar_Nilai_Atma.pdf', 'PDF · 420 KB · 4 Okt 2026'),
    $slot('bukti_pendaftaran_polibatam', 'Bukti Pendaftaran Polibatam', false, 'Jika pernah mendaftar di Polibatam', 'Opsional', null, null),
];
$berkasKurang = $berkasLengkap;
$berkasKurang[2] = $slot('skhu_nilai', 'SKHU / Daftar Nilai', true, 'SKHU, Nilai UN, SKL, atau Daftar Nilai', 'Belum terunggah', null, null);
$tinjau = ['Nama lengkap' => 'Atma Fauzilla', 'NIK' => '2171 •••• •••• 0003', 'Tempat, tanggal lahir' => 'Batam, 8 Juli 2007', 'Jenis kelamin' => 'Perempuan',
    'Nomor HP' => '+6281234567890', 'Alamat' => $data['alamat'], 'Asal sekolah' => 'SMA Negeri 3 Batam', 'Tahun lulus' => '2026'];
$dasar = [
    'pendaftaran' => $pendaftaran(), 'data' => $data, 'berkas' => $berkasLengkap, 'tinjau' => $tinjau,
    'kekurangan' => ['data' => [], 'berkas' => []], 'terisi' => '9 / 9 terisi', 'tab' => 'data', 'batasUnggah' => '2 MB',
    'disimpan' => '5 Okt 2026, 10.42 WIB',
];
$kurang = ['kekurangan' => ['data' => ['tahun_lulus' => 'Tahun lulus'], 'berkas' => ['skhu_nilai' => 'SKHU / Daftar Nilai']],
    'berkas' => $berkasKurang, 'data' => ['tahun_lulus' => null] + $data, 'terisi' => '8 / 9 terisi'] + $dasar;
$terkunci = fn (string $status) => array_map(fn ($b) => ['status' => $b['berkas'] ? $status : $b['status'], 'bisa_unggah' => false] + $b, $berkasLengkap);

return [
    'view' => 'peserta.pendaftaran.formulir',
    'peran' => 'Calon Peserta',
    'pengguna' => ['nama' => 'Atma Fauzilla', 'peran' => 'Calon Peserta', 'periode' => 'Pendaftaran 2026'],
    'menu' => 'Formulir',
    'default' => $dasar,
    'berkas' => ['tab' => 'berkas'] + $kurang,
    'tinjau_siap' => ['tab' => 'tinjau'] + $dasar,
    'tinjau_belum_lengkap' => ['tab' => 'tinjau'] + $kurang,
    'ajukan_ditolak' => ['tab' => 'data', 'errors' => ['tahun_lulus' => 'Tahun lulus wajib diisi sebelum formulir diajukan.',
        'berkas.skhu_nilai' => 'Unggah SKHU / Daftar Nilai (dokumen wajib) sebelum formulir diajukan.']] + $kurang,
    'galat_format' => ['tab' => 'data', 'errors' => ['nik' => 'NIK harus 16 digit angka sesuai KTP.', 'no_hp' => 'Nomor HP harus nomor seluler Indonesia, mis. 0812xxxxxxx atau +62812xxxxxxx.']] + $dasar,
    'periode_tutup' => ['tab' => 'tinjau', 'errors' => ['formulir' => 'Formulir tidak dapat diajukan: periode pendaftaran sudah ditutup atau peminatan tidak lagi tersedia.']] + $dasar,
    'diajukan' => ['pendaftaran' => $pendaftaran('Diajukan', false), 'berkas' => $terkunci('Menunggu Verifikasi')] + $dasar,
];
