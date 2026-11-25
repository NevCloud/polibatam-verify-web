<?php

use App\Services\NikHasher;

it('NFR-06: hash HMAC deterministik dan tidak peka spasi', function () {
    $h = app(NikHasher::class);

    expect($h->hash('2171000000000427'))->toBe($h->hash('2171 0000 0000 0427'))
        ->and($h->hash('2171000000000427'))->not->toBe($h->hash('2171000000000428'))
        ->and($h->hash('2171000000000427'))->toMatch('/^[0-9a-f]{64}$/');
});

it('K-36: memakai NIK_HMAC_KEY, bukan APP_KEY (rotasi APP_KEY tidak mengubah hash)', function () {
    $h = app(NikHasher::class);
    $awal = $h->hash('2171000000000427');

    config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);
    expect($h->hash('2171000000000427'))->toBe($awal);

    config(['app.nik_hmac_key' => 'base64:'.base64_encode(random_bytes(32))]);
    expect($h->hash('2171000000000427'))->not->toBe($awal);
});

it('K-36: menolak kunci kosong, pendek, atau sama dengan APP_KEY', function (?string $kunci) {
    config(['app.nik_hmac_key' => $kunci === 'APP_KEY' ? config('app.key') : $kunci]);
    app(NikHasher::class)->hash('2171000000000427');
})->with(['kosong' => null, 'pendek' => 'base64:'.base64_encode('pendek'), 'sama dengan APP_KEY' => 'APP_KEY'])
    ->throws(RuntimeException::class);

it('AE-06: NIK ditampilkan tersamar', function () {
    expect(NikHasher::samarkan('2171000000000427'))->toBe('2171 •••• •••• 0427')
        ->and(NikHasher::samarkan(null))->toBe('••••');
});
