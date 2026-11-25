<?php

use App\Support\Ui\Format;
use Carbon\CarbonImmutable;

it('NFR-34: rupiah memakai titik ribuan tanpa desimal', function (int $nominal, string $hasil) {
    expect(Format::rupiah($nominal))->toBe($hasil);
})->with([
    [1500000, 'Rp 1.500.000'],
    [0, 'Rp 0'],
    [1500123, 'Rp 1.500.123'],
    [-250000, '-Rp 250.000'],
]);

it('NFR-30/NFR-34: waktu UTC ditampilkan sebagai tanggal WIB berbahasa Indonesia', function () {
    // 20 Nov 18.00 UTC = 21 Nov 01.00 WIB → tanggal berpindah hari
    expect(Format::tanggal('2026-11-20 18:00:00'))->toBe('21 November 2026')
        ->and(Format::tanggal(CarbonImmutable::parse('2026-11-05 07:25:00', 'UTC')))->toBe('5 November 2026');
});

it('NFR-30: tanggal dan jam tabel admin dalam WIB', function () {
    expect(Format::tanggalJam('2026-11-05 07:25:00'))->toBe('5 Nov 2026, 14.25 WIB');
});

it('NFR-30: konversi WIB tidak mengubah instan waktu', function () {
    $utc = CarbonImmutable::parse('2026-11-30 17:00:00', 'UTC');

    expect(Format::wib($utc)->getTimezone()->getName())->toBe('Asia/Jakarta')
        ->and(Format::wib($utc)->equalTo($utc))->toBeTrue()
        ->and(Format::wib($utc)->format('Y-m-d H:i'))->toBe('2026-12-01 00:00');
});
