<?php

// HW-44 · Akun & role admin (P02). Kontrak = App\Queries\AkunAdminQuery::halaman():
// `filter` {q, role, status}, `akun` [{id, nama, inisial, email, role, peran, status, login_terakhir, saya}],
// `total`, `rentang`, `tautan` [{url, label, active}], `sebelumnya`, `berikutnya`, `peranTersedia`, `statusTersedia`.
$peran = ['admin_silau' => 'Admin SILAU', 'admin_keuangan' => 'Admin Keuangan', 'admin_track' => 'Admin Track', 'super_admin' => 'Super Admin'];
$status = ['aktif' => 'Aktif', 'menunggu_aktivasi' => 'Menunggu Aktivasi', 'nonaktif' => 'Nonaktif'];
$baris = fn (int $id, string $nama, string $inisial, string $email, string $role, string $st, string $login, bool $saya = false) => [
    'id' => $id, 'nama' => $nama, 'inisial' => $inisial, 'email' => $email, 'role' => $role, 'peran' => $peran[$role],
    'status' => $st, 'login_terakhir' => $login, 'saya' => $saya,
];
$akun = [
    $baris(2, 'Hendra Saputra', 'HS', 'hendra.saputra@polibatam.ac.id', 'admin_silau', 'Aktif', '21 November 2026, 08.42 WIB'),
    $baris(3, 'Yuliana Siregar', 'YS', 'yuliana.siregar@polibatam.ac.id', 'admin_keuangan', 'Aktif', '21 November 2026, 07.55 WIB'),
    $baris(4, 'Bayu Prasetyo', 'BP', 'bayu.prasetyo@polibatam.ac.id', 'admin_track', 'Menunggu Aktivasi', '—'),
    $baris(1, 'Ratna Wijayanti', 'RW', 'ratna.wijayanti@polibatam.ac.id', 'super_admin', 'Aktif', '21 November 2026, 09.10 WIB', true),
    $baris(5, 'Dimas Prakoso', 'DP', 'dimas.prakoso@polibatam.ac.id', 'admin_silau', 'Nonaktif', '2 Oktober 2026, 14.10 WIB'),
];
$dasar = [
    'filter' => ['q' => '', 'role' => '', 'status' => ''],
    'akun' => $akun, 'total' => 5, 'rentang' => '1–5',
    'tautan' => [['url' => '#', 'label' => '1', 'active' => true]],
    'sebelumnya' => null, 'berikutnya' => null,
    'peranTersedia' => $peran, 'statusTersedia' => $status,
];

return [
    'view' => 'admin.superadmin.akun.index',
    'peran' => 'Super Admin',
    'pengguna' => ['nama' => 'Ratna Wijayanti', 'peran' => 'Super Admin', 'periode' => 'Pendaftaran 2026'],
    'menu' => 'Kelola User',
    'default' => $dasar,
    'kosong' => ['filter' => ['q' => 'tidak ada', 'role' => '', 'status' => ''], 'akun' => [], 'total' => 0, 'rentang' => '0', 'tautan' => []] + $dasar,
    'super_admin_terakhir' => $dasar + ['errors' => ['akun' => 'Super Admin terakhir tidak dapat dinonaktifkan atau diturunkan perannya.']],
    'galat_buat' => $dasar + ['errors' => ['email' => 'Email sudah terdaftar']],
];
