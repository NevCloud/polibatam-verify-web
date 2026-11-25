<?php

namespace App\Support\Http;

use App\Models\User;

/** FR-03, K-39: setelah login, pengguna diarahkan ke dasbor sesuai role (peserta → HW-13, admin → HW-21). */
final class ArahPortal
{
    public static function untuk(?User $user): string
    {
        if (! $user) {
            return route('login');
        }

        return $user->adalahAdmin() ? route('admin.dasbor') : route('dasbor');
    }
}
