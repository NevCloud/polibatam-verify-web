<?php

// HW-51 · halaman sistem 410. Kontrak data view errors/410: tidak ada kunci wajib
// ($judul, $deskripsi, $masaBerlakuJam opsional). Halaman publik: tanpa pengguna.
return [
    'view' => 'errors.410',
    'peran' => null,
    'pengguna' => null,
    // Di produksi dari Pengaturan masa_berlaku_ekspor_jam (AE-14); di sini contoh nilai seed.
    'default' => ['masaBerlakuJam' => 24],
    'tanpa-masa-berlaku' => [],
];
