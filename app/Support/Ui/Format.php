<?php

namespace App\Support\Ui;

use Carbon\CarbonImmutable;
use DateTimeInterface;

/**
 * Format tampilan (NFR-30, NFR-34): uang integer Rupiah, waktu disimpan UTC dan ditampilkan WIB.
 */
final class Format
{
    public const ZONA_TAMPIL = 'Asia/Jakarta';

    /** 1500000 → "Rp 1.500.000" */
    public static function rupiah(int $nominal): string
    {
        $tanda = $nominal < 0 ? '-' : '';

        return $tanda.'Rp '.number_format(abs($nominal), 0, ',', '.');
    }

    /** Waktu apa pun (diasumsikan UTC bila tanpa zona) → instan yang sama di WIB. */
    public static function wib(DateTimeInterface|string $waktu): CarbonImmutable
    {
        $carbon = $waktu instanceof DateTimeInterface
            ? CarbonImmutable::instance($waktu)
            : CarbonImmutable::parse($waktu, 'UTC');

        return $carbon->setTimezone(self::ZONA_TAMPIL)->locale('id');
    }

    /** → "21 November 2026" (WIB) */
    public static function tanggal(DateTimeInterface|string $waktu): string
    {
        return self::wib($waktu)->translatedFormat('j F Y');
    }

    /** → "5 Nov 2026, 14.25 WIB" (format tabel admin, design-memory §9.3) */
    public static function tanggalJam(DateTimeInterface|string $waktu): string
    {
        return self::wib($waktu)->translatedFormat('j M Y, H.i').' WIB';
    }
}
