<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Format respons API v1 (Arsitektur §6.1–§6.2): {success, message, data, meta}; waktu ISO 8601 UTC; uang integer Rupiah. */
abstract class ApiController extends Controller
{
    /** @param  array<string, mixed>|null  $meta */
    protected function ok(string $pesan, mixed $data = null, ?array $meta = null, int $status = 200): JsonResponse
    {
        return response()->json(array_filter(['success' => true, 'message' => $pesan, 'data' => $data, 'meta' => $meta], fn ($v) => $v !== null), $status);
    }

    /**
     * @template T
     *
     * @param  LengthAwarePaginator<int, T>  $halaman
     * @param  callable(T): array<string, mixed>  $ubah
     */
    protected function daftar(LengthAwarePaginator $halaman, string $pesan, callable $ubah): JsonResponse
    {
        return $this->ok($pesan, array_map($ubah, $halaman->items()), [
            'page' => $halaman->currentPage(), 'per_page' => $halaman->perPage(), 'total' => $halaman->total(),
        ]);
    }

    /** `?per_page=` 1–100, bawaan 20. */
    protected static function perHalaman(Request $request): int
    {
        return min(100, max(1, (int) $request->query('per_page', '20')));
    }

    protected static function iso(?CarbonInterface $waktu): ?string
    {
        return $waktu?->copy()->utc()->toIso8601ZuluString();
    }

    protected static function admin(Request $request): User
    {
        /** @var User $user */
        $user = $request->user();

        return $user;
    }
}
