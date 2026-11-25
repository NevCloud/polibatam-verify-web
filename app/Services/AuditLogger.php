<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Support\Log\Samarkan;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Satu-satunya penulis audit_logs (AE-10, FR-55, BR-58, NFR-31; Keamanan DB §12):
 * append-only, hash chain SHA-256 (isi entri + prev_hash), diserialkan GET_LOCK('audit_chain').
 *
 * - `berhasil` dipanggil DI DALAM transaksi pemanggil: ikut commit/rollback, kunci dilepas setelah commit.
 * - `gagal`/`ditolak` dipanggil SETELAH rollback (di luar transaksi pemanggil) agar catatannya tidak ikut hilang.
 */
class AuditLogger
{
    public const PREV_AWAL = '0000000000000000000000000000000000000000000000000000000000000000';

    private const KUNCI = 'audit_chain';

    private bool $kunciDipegang = false;

    /** Tingkat transaksi saat kunci diambil; rollback ke bawah tingkat ini melepas kunci. */
    private int $levelKunci = 0;

    /**
     * @param  array<string, mixed>|null  $sebelum
     * @param  array<string, mixed>|null  $sesudah
     */
    public function catat(
        string $aksi,
        string $entitasType,
        int|string|null $entitasId = null,
        string $hasil = 'berhasil',
        ?array $sebelum = null,
        ?array $sesudah = null,
        ?string $alasan = null,
        ?string $kanal = null,
        ?Authenticatable $pelaku = null,
    ): AuditLog {
        $pelaku ??= Auth::user();
        $http = ! app()->runningInConsole() || app()->runningUnitTests();

        $baris = [
            'user_id' => $pelaku?->getAuthIdentifier(),
            'role' => $pelaku && method_exists($pelaku, 'getRoleNames') ? $pelaku->getRoleNames()->first() : null,
            'aksi' => $aksi,
            'hasil' => $hasil,
            'entitas_type' => $entitasType,
            'entitas_id' => $entitasId === null ? null : (int) $entitasId,
            'nilai_sebelum' => Samarkan::nilai($sebelum),
            'nilai_sesudah' => Samarkan::nilai($sesudah),
            'alasan' => $alasan,
            'ip_address' => $http ? request()->ip() : null,
            'user_agent' => $http ? (mb_substr((string) request()->userAgent(), 0, 255) ?: null) : null,
            'kanal' => $kanal ?? $this->kanal($http),
        ];

        if (DB::transactionLevel() > 0) {
            $log = $this->tulis($baris);
            DB::afterCommit(fn () => $this->lepasKunci());

            return $log;
        }

        try {
            return DB::transaction(fn () => $this->tulis($baris));
        } finally {
            $this->lepasKunci();
        }
    }

    /** Dipanggil pendengar TransactionRolledBack: kunci tidak boleh tertahan setelah rollback (worker antrean). */
    public function setelahRollback(int $levelSekarang): void
    {
        if ($levelSekarang < $this->levelKunci) {
            $this->lepasKunci();
        }
    }

    public function lepasKunci(): void
    {
        if ($this->kunciDipegang) {
            DB::selectOne('SELECT RELEASE_LOCK(?) AS r', [self::KUNCI]);
            $this->kunciDipegang = false;
        }
    }

    /**
     * Hash entri = SHA-256 dari JSON kanonik (urutan kolom tetap, kunci objek diurutkan) + prev_hash.
     * Dipakai juga oleh perintah audit:verifikasi-hash.
     *
     * @param  array<string, mixed>  $b
     */
    public static function hitungHash(array $b): string
    {
        $kanonik = [
            'prev_hash' => $b['prev_hash'],
            'user_id' => $b['user_id'] === null ? null : (int) $b['user_id'],
            'role' => $b['role'],
            'aksi' => $b['aksi'],
            'hasil' => $b['hasil'],
            'entitas_type' => $b['entitas_type'],
            'entitas_id' => $b['entitas_id'] === null ? null : (int) $b['entitas_id'],
            'nilai_sebelum' => self::urutkan($b['nilai_sebelum']),
            'nilai_sesudah' => self::urutkan($b['nilai_sesudah']),
            'alasan' => $b['alasan'],
            'ip_address' => $b['ip_address'],
            'user_agent' => $b['user_agent'],
            'kanal' => $b['kanal'],
            'created_at' => (string) $b['created_at'],
        ];

        return hash('sha256', json_encode($kanonik, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    /** @param  array<string, mixed>  $baris */
    private function tulis(array $baris): AuditLog
    {
        $this->ambilKunci();

        $baris['prev_hash'] = AuditLog::query()->orderByDesc('id')->value('hash') ?? self::PREV_AWAL;
        // Detik penuh UTC: sama persis dengan yang tersimpan di kolom TIMESTAMP, agar hash dapat dihitung ulang.
        $baris['created_at'] = now('UTC')->format('Y-m-d H:i:s');
        $baris['hash'] = self::hitungHash($baris);

        return AuditLog::query()->create($baris);
    }

    private function ambilKunci(): void
    {
        if ($this->kunciDipegang) {
            return;
        }

        if ((int) DB::selectOne('SELECT GET_LOCK(?, 5) AS r', [self::KUNCI])->r !== 1) {
            throw new RuntimeException('Rantai audit sedang sibuk; coba lagi.');
        }
        $this->kunciDipegang = true;
        $this->levelKunci = DB::transactionLevel();
    }

    private function kanal(bool $http): string
    {
        return match (true) {
            ! $http => 'sistem',
            request()->is('api/*') => 'mobile', // API v1 hanya untuk aplikasi mobile admin (K-29)
            default => 'web',
        };
    }

    private static function urutkan(mixed $nilai): mixed
    {
        if (! is_array($nilai)) {
            return $nilai;
        }

        if (! array_is_list($nilai)) {
            ksort($nilai);
        }

        return array_map(self::urutkan(...), $nilai);
    }
}
