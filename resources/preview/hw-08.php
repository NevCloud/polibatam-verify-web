<?php

// HW-08 · Verifikasi 2FA admin (P02). Kontrak: `email` (wajib), `errors`.
return [
    'view' => 'auth.two-factor',
    'peran' => 'Admin SILAU',
    'pengguna' => null,
    'default' => ['email' => 'hendra.saputra@polibatam.ac.id'],
    'galat' => ['email' => 'hendra.saputra@polibatam.ac.id', 'errors' => ['code' => 'Kode autentikator tidak valid.']],
    'recovery_salah' => ['email' => 'hendra.saputra@polibatam.ac.id', 'errors' => ['recovery_code' => 'Recovery code tidak valid.']],
];
