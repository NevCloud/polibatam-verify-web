<?php

// Pesan validasi Bahasa Indonesia (AE-15). Hanya aturan yang dipakai aplikasi; tambahkan saat aturan baru dipakai.
return [
    'accepted' => ':Attribute wajib disetujui.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'current_password' => 'Password lama salah.',
    'digits' => ':Attribute harus :digits digit.',
    'email' => ':Attribute harus berupa alamat email yang valid.',
    'exists' => ':Attribute tidak ditemukan.',
    'in' => ':Attribute tidak valid.',
    'integer' => ':Attribute harus berupa angka.',
    'max' => ['string' => ':Attribute maksimal :max karakter.'],
    'min' => ['string' => ':Attribute minimal :min karakter.'],
    'password' => [
        'letters' => ':Attribute harus mengandung huruf.',
        'mixed' => ':Attribute harus mengandung huruf besar dan huruf kecil.',
        'numbers' => ':Attribute harus mengandung angka.',
        'symbols' => ':Attribute harus mengandung simbol.',
        'uncompromised' => ':Attribute ini pernah bocor di internet. Gunakan password lain.',
    ],
    'required' => ':Attribute wajib diisi.',
    'string' => ':Attribute harus berupa teks.',
    'unique' => ':Attribute sudah terdaftar.',

    'custom' => [
        'email' => ['unique' => 'Email sudah terdaftar'], // AC-02.2
    ],

    'attributes' => [
        'name' => 'nama lengkap',
        'email' => 'email',
        'password' => 'password',
        'password_confirmation' => 'konfirmasi password',
        'current_password' => 'password lama',
        'persetujuan_privasi' => 'persetujuan kebijakan privasi',
        'code' => 'kode autentikator',
        'recovery_code' => 'recovery code',
        'role' => 'peran',
        'alasan' => 'alasan',
    ],
];
