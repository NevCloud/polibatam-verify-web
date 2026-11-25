<?php

// HW-10 · Reset password (P02). Kontrak: `token`, `email` (wajib), `errors`.
$dasar = ['token' => str_repeat('a', 64), 'email' => 'nabila.ramadhani@mail.com'];

return [
    'view' => 'auth.reset-password',
    'peran' => null,
    'pengguna' => null,
    'default' => $dasar,
    'kedaluwarsa' => $dasar + ['errors' => ['email' => 'Tautan tidak valid atau sudah kedaluwarsa. Minta tautan baru.']],
    'galat' => $dasar + ['errors' => ['password' => 'Konfirmasi password tidak cocok.']],
];
