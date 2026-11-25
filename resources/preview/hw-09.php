<?php

// HW-09 · Lupa password (P02, K-32). Kontrak: `status` (tautan terkirim), `errors`.
return [
    'view' => 'auth.forgot-password',
    'peran' => null,
    'pengguna' => null,
    'default' => [],
    'terkirim' => ['status' => 'Tautan reset berhasil dikirim.'],
    'tidak_terdaftar' => ['errors' => ['email' => 'Email tidak terdaftar']],
];
