<?php

it('NFR-48/AE-13: tiga koneksi MySQL memakai akun terpisah yang benar', function () {
    $koneksi = config('database.connections');

    expect($koneksi['mysql']['username'])->toBe(env('DB_USERNAME', 'pv_app'))
        ->and($koneksi['mysql_report']['username'])->toBe(env('DB_REPORT_USERNAME', 'pv_report'))
        ->and($koneksi['mysql_migrator']['username'])->toBe(env('DB_MIGRATOR_USERNAME', 'pv_migrator'));

    foreach (['mysql', 'mysql_report', 'mysql_migrator'] as $nama) {
        expect($koneksi[$nama])
            ->driver->toBe('mysql')
            ->charset->toBe('utf8mb4')
            ->collation->toBe('utf8mb4_0900_ai_ci')
            ->strict->toBeTrue()
            ->database->toBe($koneksi['mysql']['database']); // satu skema, tiga hak akses
    }
});

it('NFR-48: tidak ada koneksi yang memakai akun root', function () {
    foreach (['mysql', 'mysql_report', 'mysql_migrator'] as $nama) {
        expect(config("database.connections.{$nama}.username"))->not->toBe('root');
    }
});

it('NFR-30/NFR-34: zona waktu aplikasi UTC dan locale id', function () {
    expect(config('app.timezone'))->toBe('UTC')
        ->and(config('app.locale'))->toBe('id')
        ->and(date_default_timezone_get())->toBe('UTC');
});

it('AE-12: disk private, ekspor, arsip terpisah dan tidak publik', function () {
    $disk = config('filesystems.disks');
    $akar = [];

    foreach (['private', 'ekspor', 'arsip'] as $nama) {
        expect($disk)->toHaveKey($nama)
            ->and($disk[$nama])->not->toHaveKey('url')
            ->and($disk[$nama]['visibility'] ?? 'private')->toBe('private');
        $akar[] = $disk[$nama]['root'];
    }

    expect(array_unique($akar))->toHaveCount(3)
        ->and(implode('|', $akar))->not->toContain(public_path());
});

it('NFR-44: mailer dibaca dari .env, bukan ditulis di kode', function () {
    $isi = file_get_contents(config_path('mail.php'));

    expect($isi)->toContain("env('MAIL_MAILER'");
});

it('NFR-10/AE-16: .env diabaikan Git dan .env.example tidak berisi rahasia', function () {
    $abaikan = preg_split('/\R/', file_get_contents(base_path('.gitignore')));
    expect($abaikan)->toContain('.env');

    $contoh = file_get_contents(base_path('.env.example'));
    foreach (['APP_KEY', 'NIK_HMAC_KEY', 'DB_PASSWORD', 'DB_REPORT_PASSWORD', 'DB_MIGRATOR_PASSWORD'] as $kunci) {
        expect($contoh)->toMatch("/^{$kunci}=(\"\")?$/m");
    }
    foreach (['DB_REPORT_USERNAME', 'DB_MIGRATOR_USERNAME', 'FILESYSTEM_ARSIP_ROOT', 'PV_PREVIEW'] as $kunci) {
        expect($contoh)->toMatch("/^{$kunci}=/m");
    }
});

it('PRD §4.1: rute preview tidak terdaftar di luar APP_ENV=local', function () {
    expect(config('app.preview'))->toBeFalse();
    $this->get('/_preview')->assertNotFound();
});
