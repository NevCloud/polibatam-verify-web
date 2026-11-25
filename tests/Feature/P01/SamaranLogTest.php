<?php

use Illuminate\Support\Facades\Log;

it('NFR-32: log aplikasi tidak memuat NIK, rekening, password, atau token', function () {
    $berkas = storage_path('logs/uji-samaran-'.uniqid().'.log');
    config(['logging.channels.single.path' => $berkas]);

    Log::channel('single')->error('Gagal memproses NIK 2171000000000427', [
        'nik' => '2171000000000427',
        'refund' => ['nomor_rekening' => '1234567890', 'bank' => 'BRI'],
        'password' => 'rahasia',
        'headers' => ['authorization' => 'Bearer abc.def'],
    ]);

    $isi = file_get_contents($berkas);
    @unlink($berkas);

    expect($isi)->toContain('[disamarkan]')->toContain('BRI')
        ->not->toContain('2171000000000427')
        ->not->toContain('1234567890')
        ->not->toContain('rahasia')
        ->not->toContain('Bearer abc.def');
});
