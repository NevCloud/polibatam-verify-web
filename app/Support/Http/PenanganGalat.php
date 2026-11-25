<?php

namespace App\Support\Http;

use App\Exceptions\SudahDiverifikasi;
use App\Exceptions\TransisiTidakSah;
use App\Services\AuditLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\ModelStates\Exceptions\CouldNotPerformTransition;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Pemetaan galat → HTTP (Arsitektur §6.2–§6.3): JSON untuk API, halaman/redirect untuk web.
 * 403 dan 409 dicatat ke audit log sebagai `ditolak` (NFR-31, Keamanan DB §12.2).
 */
final class PenanganGalat
{
    /** status => [kode, pesan bawaan] */
    private const PETA = [
        401 => ['UNAUTHENTICATED', 'Silakan masuk terlebih dahulu.'],
        403 => ['FORBIDDEN', 'Anda tidak berhak melakukan tindakan ini.'],
        404 => ['NOT_FOUND', 'Data tidak ditemukan.'],
        409 => ['CONFLICT', 'Permintaan bertentangan dengan status data saat ini.'],
        410 => ['GONE', 'Sumber sudah tidak tersedia.'],
        419 => ['SESSION_EXPIRED', 'Sesi Anda berakhir. Silakan muat ulang halaman.'],
        422 => ['VALIDATION_ERROR', 'Data yang dikirim tidak valid.'],
        429 => ['TOO_MANY_REQUESTS', 'Terlalu banyak permintaan. Coba lagi beberapa saat lagi.'],
        500 => ['SERVER_ERROR', 'Terjadi kesalahan pada sistem.'],
    ];

    public static function daftarkan(Exceptions $exceptions): void
    {
        $exceptions->shouldRenderJsonWhen(fn (Request $r) => $r->is('api/*') || $r->expectsJson());

        $exceptions->render(function (Throwable $e, Request $request) {
            $konflik = self::konflik($e);

            if ($request->is('api/*') || $request->expectsJson()) {
                return self::json($e, $konflik);
            }

            // Web: konflik (keputusan dikirim lewat form) → kembali ke halaman dengan pesan & data terbaru (FR-61).
            if ($konflik) {
                return back()->with('galat', $e->getMessage())->with('kode_galat', $konflik);
            }

            return null;
        });

        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            $aksi = match (true) {
                $response->getStatusCode() === 403 => 'akses_ditolak',
                $e instanceof SudahDiverifikasi => 'konflik_verifikasi',
                default => null,
            };

            if ($aksi !== null) {
                // Ditulis setelah rollback, di transaksi terpisah (§12.3 butir 2). Kegagalan mencatat tidak boleh menutupi respons.
                rescue(fn () => app(AuditLogger::class)->catat($aksi, 'http', null, 'ditolak', null, ['metode' => $request->method(), 'path' => '/'.$request->path()], $e->getMessage() ?: null), report: true);
            }

            return $response;
        });
    }

    /** 403 dari Action dengan pesan Bahasa Indonesia sendiri (mis. login mobile role lain, AC-30.2); bukan pesan bawaan framework. */
    private static function pesanSendiri(Throwable $e): bool
    {
        $asal = $e->getPrevious() ?? $e; // Laravel membungkus AuthorizationException menjadi AccessDeniedHttpException

        return $asal instanceof AuthorizationException && ! in_array($asal->getMessage(), ['', 'This action is unauthorized.'], true);
    }

    private static function konflik(Throwable $e): ?string
    {
        return match (true) {
            $e instanceof SudahDiverifikasi => SudahDiverifikasi::KODE,
            $e instanceof TransisiTidakSah, $e instanceof CouldNotPerformTransition => TransisiTidakSah::KODE,
            default => null,
        };
    }

    private static function json(Throwable $e, ?string $konflik): JsonResponse
    {
        $status = match (true) {
            $konflik !== null => 409,
            $e instanceof ValidationException => 422,
            $e instanceof AuthenticationException => 401,
            $e instanceof AuthorizationException => 403,
            $e instanceof ModelNotFoundException => 404,
            $e instanceof HttpExceptionInterface => $e->getStatusCode(),
            default => 500,
        };

        [$kode, $pesan] = self::PETA[$status] ?? ['ERROR', 'Permintaan tidak dapat diproses.'];

        $badan = [
            'success' => false,
            // NFR-34/35: pesan baku Bahasa Indonesia, tanpa detail teknis; hanya konflik domain (409) membawa pesannya sendiri.
            'message' => $konflik !== null || self::pesanSendiri($e) ? $e->getMessage() : $pesan,
            'code' => $konflik ?? $kode,
        ];

        if ($e instanceof ValidationException) {
            $badan['message'] = $pesan;
            $badan['errors'] = $e->errors();
        }

        $headers = $e instanceof HttpExceptionInterface ? $e->getHeaders() : [];

        return new JsonResponse($badan, $status, $headers);
    }
}
