<?php

// HW-36 · Kuota minimum per periode (P07, K-42). Kontrak = Track\KuotaController::index: `periode`, `periodeTersedia`, `baris` (KuotaQuery::baris), `belumDiatur`, `bisaUbah`, `wajibAlasan`, `jumlah`.
$b = fn (int $id, int $pm, string $nama, string $kode, string $track, int $min, int $npd, string $penanda, string $nada) => [
    'id' => $id, 'peminatan_id' => $pm, 'peminatan' => $nama, 'kode' => $kode, 'track' => $track, 'kuota_min' => $min,
    'diajukan' => 0, 'masuk_kuota' => 0, 'npd_terbit' => $npd, 'persen' => $min ? (int) round($npd / $min * 100) : null,
    'penanda' => $penanda, 'nada' => $nada, 'dibatalkan' => false,
];
$baris = [
    $b(1, 1, 'Rekayasa Multimedia', 'TRMM', 'Teknik Informatika', 15, 21, 'Tercapai', 'success'),
    $b(2, 2, 'Rekayasa Perangkat Lunak', 'TRPL', 'Teknik Informatika', 15, 0, 'Berjalan', 'info'),
];
$dasar = [
    'periode' => ['id' => 2, 'nama' => 'Gelombang 1 · 2027', 'rentang' => '1 Januari 2027 – 31 Januari 2027', 'sisa_hari' => 36, 'ditutup' => false, 'dimulai' => false],
    'periodeTersedia' => [2 => 'Gelombang 1 · 2027', 1 => 'Gelombang 2 · 2026'],
    'baris' => $baris,
    'belumDiatur' => [['peminatan_id' => 3, 'peminatan' => 'Akuntansi', 'kode' => 'AKT', 'track' => 'Manajemen & Bisnis']],
    'bisaUbah' => true, 'wajibAlasan' => false,
    'jumlah' => ['peminatan' => 2, 'kuota_min' => 30, 'npd_terbit' => 21],
];
$berjalan = ['periode' => ['dimulai' => true, 'sisa_hari' => 5] + $dasar['periode'], 'belumDiatur' => []] + $dasar;

return [
    'view' => 'admin.track.kuota.index',
    'peran' => 'Admin Track',
    'pengguna' => ['nama' => 'Dimas Prakoso', 'peran' => 'Admin Track', 'periode' => 'Pendaftaran 2026'],
    'menu' => 'Kuota per Periode',
    'default' => $dasar,
    'terkunci_admin_track' => ['bisaUbah' => false] + $berjalan,
    'super_admin_alasan' => ['bisaUbah' => true, 'wajibAlasan' => true, 'peran' => 'Super Admin', 'pengguna' => ['nama' => 'Citra Aulia', 'peran' => 'Super Admin', 'periode' => 'Pendaftaran 2026'],
        'errors' => ['alasan' => 'Alasan wajib diisi (minimal 10 karakter) karena periode pendaftaran sudah dimulai.', 'kuota.2' => 'Rekayasa Perangkat Lunak: Kuota minimum tidak boleh negatif.']] + $berjalan,
    'tanpa_periode' => ['periode' => null, 'periodeTersedia' => [], 'baris' => [], 'belumDiatur' => [], 'bisaUbah' => false] + $dasar,
];
