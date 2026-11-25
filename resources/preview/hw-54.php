<?php

// HW-54 · halaman sistem 500. Kontrak data view errors/500: tidak ada kunci wajib
// ($judul dan $deskripsi opsional). Halaman publik: tanpa pengguna.
return [
    'view' => 'errors.500',
    'peran' => null,
    'pengguna' => null,
    'default' => [],
];
