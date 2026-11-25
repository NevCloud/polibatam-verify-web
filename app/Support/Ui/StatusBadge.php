<?php

namespace App\Support\Ui;

use InvalidArgumentException;

/**
 * Satu-satunya pemetaan status resmi → nada warna badge (design-memory §6.2, PRD §3.2).
 */
final class StatusBadge
{
    public const SUCCESS = 'success';

    public const WARNING = 'warning';

    public const ERROR = 'error';

    public const NEUTRAL = 'neutral';

    public const INFO = 'info';

    /** @var array<string, string> */
    private const TETAP = [
        // Pendaftaran
        'Draf' => self::NEUTRAL,
        'Diajukan' => self::WARNING,
        'Perlu Revisi' => self::ERROR,
        'Masuk Kuota' => self::SUCCESS,
        'NPD Terbit' => self::SUCCESS,
        'Dibatalkan' => self::ERROR, // juga Tagihan & Peserta Didik
        // Dokumen & Tagihan
        'Menunggu Verifikasi' => self::WARNING,
        'Valid' => self::SUCCESS,
        'Ditolak' => self::ERROR, // Dokumen, Tagihan, Refund
        'Belum Bayar' => self::NEUTRAL,
        'Lunas' => self::SUCCESS,
        // Peserta Didik
        'Tertahan (Tunggakan)' => self::ERROR,
        'Selesai Masa Program' => self::SUCCESS,
        // Refund
        'Menunggu Data Rekening' => self::NEUTRAL,
        'Diproses' => self::WARNING,
        'Selesai' => self::SUCCESS,
        // Label bantu non-status
        'Belum terunggah' => self::NEUTRAL,
        'Belum terbit' => self::NEUTRAL,
        'Menunggu verifikasi berkas' => self::NEUTRAL,
        'Terunggah' => self::SUCCESS, // slot berkas terisi saat Draf (Figma HW-15 Tab Berkas)
        'Opsional' => self::NEUTRAL, // slot dokumen opsional kosong (BR-11)
        'Diganti' => self::NEUTRAL, // versi berkas lama yang diganti saat Draf (riwayat versi HW-16)
        'Revisi masuk' => self::WARNING, // Diajukan ulang setelah Perlu Revisi (Figma HW-25 kolom Jenis)
    ];

    /** Status berparameter: "Aktif Semester 2", "Belum Aktif Semester 2". */
    private const POLA = [
        '/^Belum Aktif Semester \d+$/u' => self::NEUTRAL,
        '/^Aktif Semester \d+$/u' => self::SUCCESS,
    ];

    public static function nada(string $status): string
    {
        if (isset(self::TETAP[$status])) {
            return self::TETAP[$status];
        }

        foreach (self::POLA as $pola => $nada) {
            if (preg_match($pola, $status)) {
                return $nada;
            }
        }

        // AE-15: status di luar nama resmi adalah bug, bukan sesuatu yang diberi warna default.
        throw new InvalidArgumentException("Status tidak resmi: [{$status}]");
    }
}
