<?php

// HW-12 · Profil & keamanan akun (P02). Kontrak = ProfilController::show:
// `akun` {nama, inisial, email, peran, bergabung, email_terverifikasi, admin}, `loginTerakhir`, `sisaKode`, `kode`,
// `sesi` [{perangkat, ip, terakhir, ini}].
$akun = ['nama' => 'Hendra Saputra', 'inisial' => 'HS', 'email' => 'hendra.saputra@polibatam.ac.id', 'peran' => 'Admin SILAU',
    'bergabung' => '15 Januari 2026', 'email_terverifikasi' => true, 'admin' => true];
$sesi = [
    ['perangkat' => 'Chrome · Windows', 'ip' => '182.253.14.77', 'terakhir' => '21 November 2026, 08.42 WIB', 'ini' => true],
    ['perangkat' => 'Edge · Windows', 'ip' => '10.10.4.21', 'terakhir' => '20 November 2026, 16.05 WIB', 'ini' => false],
];
$dasar = ['akun' => $akun, 'loginTerakhir' => '20 November 2026, 16.05 WIB · IP 10.10.4.21', 'sisaKode' => 8, 'kode' => null, 'sesi' => $sesi];

return [
    'view' => 'akun.profil',
    'peran' => 'Admin SILAU',
    'pengguna' => ['nama' => 'Hendra Saputra', 'peran' => 'Admin SILAU', 'periode' => 'Pendaftaran 2026'],
    'menu' => 'Profil',
    'default' => $dasar,
    'kode_tampil' => ['kode' => ['Xk3pQ9mT2w-Lr8vN4cYh1', 'Pq7Zs2Kd9e-Hm4Wt6Bx3a', 'Vn5Rc8Jy1u-Fg2Qp7Ls9d', 'Tb6Hw3Mz8k-Ce1Xr5Nv4q']] + $dasar,
    'galat' => $dasar + ['errors' => ['name' => 'Nama lengkap wajib diisi.']],
    'peserta' => ['akun' => ['admin' => false, 'peran' => 'Calon Peserta'] + $akun, 'sisaKode' => null] + $dasar,
];
