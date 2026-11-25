<?php

// HW-05 · Registrasi (P02). Kontrak data view auth.register: tidak ada kunci wajib; `errors` = galat validasi.
return [
    'view' => 'auth.register',
    'peran' => null,
    'pengguna' => null,
    'default' => [],
    'galat' => ['errors' => [
        'email' => 'Email sudah terdaftar',
        'password' => 'Password harus mengandung angka.',
        'persetujuan_privasi' => 'Persetujuan kebijakan privasi wajib disetujui.',
    ]],
];
