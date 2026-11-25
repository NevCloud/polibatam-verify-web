<?php

// HW-55 · halaman sistem 503. Kontrak data view errors/503: tidak ada kunci wajib
// ($judul dan $deskripsi opsional). Halaman publik: tanpa pengguna.
return [
    'view' => 'errors.503',
    'peran' => null,
    'pengguna' => null,
    'default' => [],
    'terjadwal' => ['deskripsi' => 'Polibatam Verify sedang dalam pemeliharaan terjadwal hingga 22 November 2026, 06.00 WIB.'],
];
