<?php

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function skema(): string
{
    return DB::connection()->getDatabaseName();
}

it('migration membuat 36 tabel (01 + 01b rekening_institusis), semuanya InnoDB utf8mb4_0900_ai_ci', function () {
    $tabel = DB::select(
        "SELECT table_name AS nama, engine AS engine, table_collation AS kolasi FROM information_schema.tables
         WHERE table_schema = ? AND table_type = 'BASE TABLE' AND table_name <> 'migrations'",
        [skema()]
    );

    expect($tabel)->toHaveCount(36);
    foreach ($tabel as $t) {
        expect($t->engine)->toBe('InnoDB', $t->nama)->and($t->kolasi)->toBe('utf8mb4_0900_ai_ci', $t->nama);
    }
});

it('NFR-29: tidak ada kolom DECIMAL/FLOAT/DOUBLE; uang memakai BIGINT UNSIGNED', function () {
    $pecahan = DB::select(
        "SELECT CONCAT(table_name, '.', column_name) AS kolom FROM information_schema.columns
         WHERE table_schema = ? AND data_type IN ('decimal', 'float', 'double')",
        [skema()]
    );

    expect($pecahan)->toBe([]);
});

it('migration membuat 4 VIEW laporan tanpa kolom sensitif (Keamanan DB §7)', function () {
    $view = collect(DB::select(
        'SELECT table_name AS nama FROM information_schema.views WHERE table_schema = ?', [skema()]
    ))->pluck('nama')->sort()->values()->all();

    expect($view)->toBe(['v_antrean_verifikasi_berkas', 'v_data_pendaftaran', 'v_rekap_pembayaran', 'v_status_refund']);

    $kolomSensitif = DB::select(
        "SELECT CONCAT(table_name, '.', column_name) AS kolom FROM information_schema.columns
         WHERE table_schema = ? AND table_name LIKE 'v\\_%'
           AND (column_name REGEXP '(^|_)nik(_|$)' OR column_name LIKE '%alamat%' OR column_name LIKE '%rekening%'
             OR column_name LIKE '%lahir%' OR column_name IN ('path', 'password', 'token'))",
        [skema()]
    );
    expect($kolomSensitif)->toBe([]);
});

it('migration membuat 6 trigger integritas dari 03_trigger.sql', function () {
    $trigger = collect(DB::select('SELECT trigger_name AS nama FROM information_schema.triggers WHERE trigger_schema = ?', [skema()]))
        ->pluck('nama')->sort()->values()->all();

    expect($trigger)->toBe([
        'trg_arsip_periodes_kunci', 'trg_arsip_periodes_tolak_delete', 'trg_audit_logs_tolak_delete',
        'trg_audit_logs_tolak_update', 'trg_pendaftarans_nomor_tetap', 'trg_peserta_didiks_npd_tetap',
    ]);
});

it('AE-10: trigger menolak UPDATE dan DELETE audit_logs (append-only)', function () {
    DB::table('audit_logs')->insert([
        'aksi' => 'uji', 'entitas_type' => 'uji', 'prev_hash' => str_repeat('0', 64), 'hash' => str_repeat('a', 64),
    ]);

    expect(fn () => DB::table('audit_logs')->update(['aksi' => 'ubah']))->toThrow(QueryException::class, 'append-only')
        ->and(fn () => DB::table('audit_logs')->delete())->toThrow(QueryException::class, 'append-only');
});

it('CHECK constraint aktif: hasil audit di luar berhasil/gagal/ditolak ditolak', function () {
    DB::table('audit_logs')->insert([
        'aksi' => 'uji', 'hasil' => 'sukses', 'entitas_type' => 'uji', 'prev_hash' => str_repeat('0', 64), 'hash' => str_repeat('b', 64),
    ]);
})->throws(QueryException::class, 'chk_audit_hasil');

it('migration dapat di-rollback dan dijalankan ulang (reset lokal)', function () {
    $this->artisan('migrate:rollback', ['--force' => true])->assertSuccessful();
    expect(DB::select("SELECT 1 FROM information_schema.tables WHERE table_schema = ? AND table_name <> 'migrations'", [skema()]))->toBe([]);

    $this->artisan('migrate', ['--force' => true])->assertSuccessful();
    expect(DB::select("SELECT 1 FROM information_schema.tables WHERE table_schema = ? AND table_type = 'BASE TABLE' AND table_name <> 'migrations'", [skema()]))->toHaveCount(36);
});
