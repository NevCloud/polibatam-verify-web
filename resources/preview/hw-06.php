<?php

// HW-06 · Verifikasi email (P02). Kontrak: `email` (mode nyata dari akun login), `status` ('verification-link-sent').
return [
    'view' => 'auth.verify-email',
    'peran' => 'Calon Peserta',
    'pengguna' => null,
    'default' => ['email' => 'nabila.ramadhani@mail.com'],
    'terkirim' => ['email' => 'nabila.ramadhani@mail.com', 'status' => 'verification-link-sent'],
];
