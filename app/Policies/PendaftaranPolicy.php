<?php

namespace App\Policies;

use App\Models\Pendaftaran;
use App\Models\User;

/**
 * Portal peserta (BR-64, NFR-12, FR-68): pendaftaran — termasuk Draf, berkas, dan kartu — hanya untuk pemiliknya.
 * Admin tidak pernah lewat rute peserta (middleware role); akses admin ke data non-Draf diatur di P04.
 */
class PendaftaranPolicy
{
    public function view(User $user, Pendaftaran $pendaftaran): bool
    {
        return $pendaftaran->user_id === $user->id;
    }

    public function update(User $user, Pendaftaran $pendaftaran): bool
    {
        return $pendaftaran->user_id === $user->id;
    }

    /** BR-73: kartu pendaftar tidak tersedia untuk Draf. */
    public function unduhKartu(User $user, Pendaftaran $pendaftaran): bool
    {
        return $pendaftaran->user_id === $user->id && ! $pendaftaran->adalahDraf();
    }
}
