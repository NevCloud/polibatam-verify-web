<?php

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function pencatat(): AuditLogger
{
    return app(AuditLogger::class);
}

function kunciBebas(): bool
{
    return (int) DB::selectOne("SELECT IS_FREE_LOCK('audit_chain') AS r")->r === 1;
}

it('FR-55/BR-58: mencatat pelaku, kanal, aksi, entitas, nilai sebelum/sesudah, alasan, hasil', function () {
    $admin = User::factory()->create();

    $log = pencatat()->catat('verifikasi_dokumen', 'dokumen', 7, 'berhasil', ['status' => 'menunggu_verifikasi'], ['status' => 'ditolak'], 'Dokumen buram', 'web', $admin);

    expect($log->fresh())
        ->user_id->toBe($admin->id)
        ->aksi->toBe('verifikasi_dokumen')
        ->entitas_type->toBe('dokumen')
        ->entitas_id->toBe(7)
        ->hasil->toBe('berhasil')
        ->kanal->toBe('web')
        ->alasan->toBe('Dokumen buram')
        ->nilai_sebelum->toBe(['status' => 'menunggu_verifikasi'])
        ->nilai_sesudah->toBe(['status' => 'ditolak']);
});

it('NFR-31: hash chain berurutan dan audit:verifikasi-hash lulus', function () {
    $a = pencatat()->catat('ubah_tarif', 'tarif', 1, sesudah: ['nominal' => 1500000]);
    $b = pencatat()->catat('ubah_kuota', 'peminatan_periode', 2, sesudah: ['kuota_maks' => 30, 'kuota_min' => 10]);
    $c = pencatat()->catat('akses_ditolak', 'http', hasil: 'ditolak');

    expect($a->prev_hash)->toBe(AuditLogger::PREV_AWAL)
        ->and($b->prev_hash)->toBe($a->hash)
        ->and($c->prev_hash)->toBe($b->hash)
        ->and(strlen($c->hash))->toBe(64);

    $this->artisan('audit:verifikasi-hash')->expectsOutputToContain('Rantai audit utuh: 3 entri')->assertSuccessful();
});

it('NFR-31/TST-36: entri yang isinya dirusak terdeteksi audit:verifikasi-hash', function () {
    $a = pencatat()->catat('ubah_tarif', 'tarif', 1, sesudah: ['nominal' => 1500000]);
    // Simulasi DBA merusak data: baris tersambung ke rantai, tetapi isinya tidak sama dengan hash-nya.
    $palsu = ['user_id' => null, 'role' => null, 'aksi' => 'ubah_tarif', 'hasil' => 'berhasil', 'entitas_type' => 'tarif', 'entitas_id' => 1,
        'nilai_sebelum' => null, 'nilai_sesudah' => ['nominal' => 1500000], 'alasan' => null, 'ip_address' => null, 'user_agent' => null,
        'kanal' => 'sistem', 'created_at' => now('UTC')->format('Y-m-d H:i:s'), 'prev_hash' => $a->hash];
    $hashAsli = AuditLogger::hitungHash($palsu);
    $palsu['nilai_sesudah'] = ['nominal' => 1];
    AuditLog::query()->create($palsu + ['hash' => $hashAsli]);

    $this->artisan('audit:verifikasi-hash')->expectsOutputToContain('Rantai putus pada id')->assertFailed();
});

it('NFR-31: entri yang dihapus/disisipkan (prev_hash tidak menyambung) terdeteksi', function () {
    pencatat()->catat('ubah_tarif', 'tarif', 1);
    AuditLog::query()->create(['aksi' => 'sisipan', 'hasil' => 'berhasil', 'entitas_type' => 'x', 'kanal' => 'sistem',
        'created_at' => now('UTC'), 'prev_hash' => str_repeat('f', 64), 'hash' => str_repeat('e', 64)]);

    $this->artisan('audit:verifikasi-hash')->expectsOutputToContain('prev_hash tidak menunjuk entri sebelumnya')->assertFailed();
});

it('NFR-32/§12.3: NIK, rekening, password, token disamarkan sebelum dicatat', function () {
    $log = pencatat()->catat('akses_rekening_lengkap', 'refund', 1, sebelum: ['nik' => '2171000000000427', 'data' => ['nomor_rekening' => '1234567890', 'bank' => 'BRI']],
        sesudah: ['password' => 'rahasia', 'api_token' => 'abc', 'status' => 'diproses']);

    // toEqual: MySQL JSON menyusun ulang urutan kunci.
    expect($log->fresh()->nilai_sebelum)->toEqual(['nik' => '[disamarkan]', 'data' => ['nomor_rekening' => '[disamarkan]', 'bank' => 'BRI']])
        ->and($log->fresh()->nilai_sesudah)->toEqual(['password' => '[disamarkan]', 'api_token' => '[disamarkan]', 'status' => 'diproses']);
});

it('§12.3: entri berhasil ikut rollback, entri gagal setelah rollback tetap tersimpan; kunci dilepas', function () {
    try {
        DB::transaction(function () {
            pencatat()->catat('terbit_npd', 'pendaftaran', 45);
            expect(kunciBebas())->toBeFalse(); // kunci ditahan sampai transaksi selesai
            throw new RuntimeException('NPD bentrok');
        });
    } catch (RuntimeException) {
        pencatat()->catat('terbit_npd', 'pendaftaran', 45, 'gagal', alasan: 'NPD bentrok');
    }

    expect(AuditLog::query()->pluck('hasil')->all())->toBe(['gagal'])
        ->and(kunciBebas())->toBeTrue();
    $this->artisan('audit:verifikasi-hash')->assertSuccessful();
});

it('kunci rantai dilepas setelah commit', function () {
    DB::transaction(fn () => pencatat()->catat('ubah_cms', 'konten_cms', 1));

    expect(kunciBebas())->toBeTrue();
});
