<?php

namespace App\Support\Log;

/**
 * Satu-satunya aturan penyamaran data sensitif (AE-06, NFR-32, Keamanan DB §12.3 butir 4):
 * NIK, rekening, password, token, rahasia 2FA tidak boleh tercatat utuh di audit log maupun log aplikasi.
 */
final class Samarkan
{
    public const TANDA = '[disamarkan]';

    private const KUNCI = '/(nik|rekening|password|passwd|token|secret|recovery|authorization|cookie|api[_-]?key)/i';

    /** Samarkan nilai pada kunci sensitif, rekursif. */
    public static function nilai(mixed $data): mixed
    {
        if (! is_array($data)) {
            return $data;
        }

        foreach ($data as $kunci => $isi) {
            $data[$kunci] = is_string($kunci) && preg_match(self::KUNCI, $kunci)
                ? self::TANDA
                : self::nilai($isi);
        }

        return $data;
    }

    /** Samarkan NIK (16 digit) yang tertulis bebas di dalam teks, mis. pesan log. */
    public static function teks(string $teks): string
    {
        return preg_replace('/(?<!\d)\d{16}(?!\d)/', self::TANDA, $teks) ?? $teks;
    }
}
