<?php

namespace App\Services;

use RuntimeException;

/**
 * nik_hash = HMAC-SHA256(NIK, NIK_HMAC_KEY) untuk cek duplikat & pencarian tanpa dekripsi (BR-62, NFR-06, K-36).
 * Kunci TERPISAH dari APP_KEY: rotasi APP_KEY tidak mengubah nik_hash.
 */
class NikHasher
{
    public function hash(string $nik): string
    {
        return hash_hmac('sha256', self::normalisasi($nik), $this->kunci());
    }

    /** "2171000000000427" → "2171 •••• •••• 0427" (design-memory §9.2). */
    public static function samarkan(?string $nik): string
    {
        $nik = self::normalisasi((string) $nik);

        return strlen($nik) < 8 ? '••••' : substr($nik, 0, 4).' •••• •••• '.substr($nik, -4);
    }

    private static function dekode(string $kunci): string
    {
        return str_starts_with($kunci, 'base64:') ? (string) base64_decode(substr($kunci, 7), true) : $kunci;
    }

    private static function normalisasi(string $nik): string
    {
        return preg_replace('/\D/', '', $nik) ?? '';
    }

    private function kunci(): string
    {
        $kunci = self::dekode((string) config('app.nik_hmac_key'));

        if (strlen($kunci) < 32) {
            throw new RuntimeException('NIK_HMAC_KEY belum diatur atau kurang dari 32 byte.');
        }

        if (hash_equals($kunci, self::dekode((string) config('app.key')))) {
            throw new RuntimeException('NIK_HMAC_KEY tidak boleh sama dengan APP_KEY (K-36).');
        }

        return $kunci;
    }
}
