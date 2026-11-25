<?php

use Laravel\Fortify\Features;

/*
 * Fortify dipakai untuk registrasi, verifikasi email, dan reset password (P02).
 * Login memakai rute /login Fortify dengan pipeline sendiri (FortifyServiceProvider):
 * kunci akun BR-03, akun nonaktif, dan 2FA wajib admin (BR-59) yang tantangan & pendaftarannya
 * ditangani App\Http\Controllers\Web\Auth\*. Fitur 2FA bawaan Fortify sengaja tidak diaktifkan
 * agar tidak ada rute untuk mematikan 2FA admin.
 */
return [
    'guard' => 'web',
    'passwords' => 'users',
    'username' => 'email',
    'email' => 'email',
    'lowercase_usernames' => true,
    'home' => '/dasbor',
    'prefix' => '',
    'domain' => null,
    'middleware' => ['web'],
    // NFR-08 ditangani middleware BatasiPermintaanSensitif (10/menit/IP) untuk semua rute sensitif.
    'limiters' => [
        'login' => null,
    ],
    'views' => true,
    'features' => [
        Features::registration(),
        Features::resetPasswords(),
        Features::emailVerification(),
    ],
];
