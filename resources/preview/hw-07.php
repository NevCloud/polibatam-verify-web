<?php

// HW-07 · Login (P02). Kontrak: `status` (opsional, mis. sesi berakhir), `errors`.
return [
    'view' => 'auth.login',
    'peran' => null,
    'pengguna' => null,
    'default' => [],
    'kredensial_salah' => ['errors' => ['email' => 'Email atau password salah']],
    'terkunci' => ['errors' => ['email' => 'Akun dikunci sementara. Coba lagi dalam 15 menit.']],
    'nonaktif' => ['errors' => ['email' => 'Akun Anda dinonaktifkan. Hubungi Super Admin.']],
    'sesi_berakhir' => ['status' => 'Sesi berakhir karena tidak aktif. Silakan masuk kembali.'],
];
