<?php

// HW-11 · Aktivasi akun admin (P02). Kontrak: `langkah` 1|2|3;
// 1: `token`, `email`, `kedaluwarsa` · 2: `qr` (SVG), `kunciManual` · 3: `kode` (daftar recovery code).
$token = str_repeat('a', 64);
$email = 'hendra.saputra@polibatam.ac.id';
$qr = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"><rect width="10" height="10" fill="white"/>'
    .'<path d="M1 1h3v3H1zM6 1h3v3H6zM1 6h3v3H1zM6 6h1v1H6zM8 8h1v1H8z" fill="black"/></svg>';
$kode = ['Xk3pQ9mT2w-Lr8vN4cYh1', 'Pq7Zs2Kd9e-Hm4Wt6Bx3a', 'Vn5Rc8Jy1u-Fg2Qp7Ls9d', 'Tb6Hw3Mz8k-Ce1Xr5Nv4q',
    'Ds9Lk4Pg7m-Wa2Yt8Hb1x', 'Mf3Qv6Zn2r-Kj8Ue5Gc7p', 'Ry1Ts7Bw4h-Na9Lm3Xd6e', 'Gh8Pc2Vk5s-Qz4Jf1Wy9t'];

return [
    'view' => 'auth.aktivasi',
    'peran' => 'Admin SILAU',
    'pengguna' => null,
    'default' => ['langkah' => 1, 'token' => $token, 'email' => $email, 'kedaluwarsa' => false],
    'kedaluwarsa' => ['langkah' => 1, 'token' => $token, 'email' => $email, 'kedaluwarsa' => true],
    'pindai_qr' => ['langkah' => 2, 'qr' => $qr, 'kunciManual' => 'JBSWY3DPEHPK3PXP'],
    'kode_salah' => ['langkah' => 2, 'qr' => $qr, 'kunciManual' => 'JBSWY3DPEHPK3PXP',
        'errors' => ['code' => 'Kode autentikator tidak valid. Periksa jam perangkat Anda lalu coba lagi.']],
    'recovery_code' => ['langkah' => 3, 'kode' => $kode],
];
