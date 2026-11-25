<?php

// HW-50 · halaman sistem 404. Kontrak data view errors/404: tidak ada kunci wajib
// ($judul dan $deskripsi opsional). Halaman publik: tanpa pengguna.
return [
    'view' => 'errors.404',
    'peran' => null,
    'pengguna' => null,
    'default' => [],
];
