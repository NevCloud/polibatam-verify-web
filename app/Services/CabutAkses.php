<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * NFR-03, BR-60, AC-27.2/27.6: mengakhiri sesi web (tabel sessions) dan mencabut token mobile (Sanctum).
 * Sesi & token bukan data domain, jadi boleh DELETE (GRANT 05).
 */
class CabutAkses
{
    public function semua(User $user, ?string $kecualiSesi = null): void
    {
        $this->sesiWeb($user, $kecualiSesi);
        $this->tokenMobile($user);
    }

    public function sesiWeb(User $user, ?string $kecualiSesi = null): int
    {
        return DB::table('sessions')->where('user_id', $user->id)
            ->when($kecualiSesi, fn ($q) => $q->where('id', '!=', $kecualiSesi))
            ->delete();
    }

    public function tokenMobile(User $user): int
    {
        return $user->tokens()->delete();
    }
}
