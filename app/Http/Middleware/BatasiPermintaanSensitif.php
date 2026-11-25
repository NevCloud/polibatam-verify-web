<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * NFR-08: maksimal 10 permintaan per menit per IP pada rute sensitif. Dipasang di grup `web` karena rute
 * Fortify tidak bisa diberi middleware per rute. Rute unggah file (P03) ditambahkan ke RUTE.
 */
class BatasiPermintaanSensitif
{
    public const MAKS_PER_MENIT = 10;

    public const RUTE = [
        'login.store', 'register.store', 'password.email', 'password.update',
        'two-factor.login.store', 'aktivasi.store', 'aktivasi.autentikator.store',
        'pendaftaran.berkas.unggah', // P03: unggah dokumen (NFR-07, NFR-08)
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethodSafe() || ! $request->routeIs(self::RUTE)) {
            return $next($request);
        }

        $kunci = 'sensitif:'.$request->ip();

        if (RateLimiter::tooManyAttempts($kunci, self::MAKS_PER_MENIT)) {
            throw new ThrottleRequestsException(headers: ['Retry-After' => RateLimiter::availableIn($kunci)]);
        }
        RateLimiter::hit($kunci, 60);

        return $next($request);
    }
}
