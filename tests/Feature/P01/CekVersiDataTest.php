<?php

use App\Exceptions\SudahDiverifikasi;
use App\Exceptions\TransisiTidakSah;
use App\Models\Dokumen;
use App\Models\User;
use App\Services\CekVersiData;
use App\States\Dokumen\Ditolak;
use App\States\Dokumen\Valid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $user = User::factory()->create();
    $periode = DB::table('periode_pendaftarans')->insertGetId(['nama' => 'Gelombang 2 · 2026', 'tanggal_mulai' => '2026-11-01 00:00:00', 'tanggal_selesai' => '2026-11-30 16:59:59']);
    $pp = DB::table('peminatan_periodes')->insertGetId(['periode_pendaftaran_id' => $periode, 'peminatan_id' => DB::table('peminatans')->value('id'), 'kuota_min' => 10]);
    $pendaftaran = DB::table('pendaftarans')->insertGetId(['nomor_pendaftaran' => 'PV-2026-000426', 'user_id' => $user->id, 'peminatan_periode_id' => $pp, 'status' => 'diajukan']);
    $this->dokumen = Dokumen::query()->create(['pendaftaran_id' => $pendaftaran, 'jenis' => 'ktp']);
});

it('BR-61: keputusan dengan versi terbaru berhasil dan versi berubah', function () {
    $versi = CekVersiData::versi($this->dokumen);
    $baru = app(CekVersiData::class)->transisi($this->dokumen, Valid::class, $versi);

    expect($baru)->not->toBe($versi)
        ->and($this->dokumen->fresh()->status->getValue())->toBe('valid')
        ->and(CekVersiData::versi($this->dokumen->fresh()))->toBe($baru);
});

it('FR-61: dua keputusan atas versi yang sama → yang kedua SUDAH_DIVERIFIKASI', function () {
    $versiDilihatKeduaAdmin = CekVersiData::versi($this->dokumen);
    $adminA = Dokumen::query()->find($this->dokumen->id);
    $adminB = Dokumen::query()->find($this->dokumen->id);

    app(CekVersiData::class)->transisi($adminA, Valid::class, $versiDilihatKeduaAdmin);

    expect(fn () => app(CekVersiData::class)->transisi($adminB, Ditolak::class, $versiDilihatKeduaAdmin))
        ->toThrow(SudahDiverifikasi::class);
    expect($this->dokumen->fresh()->status->getValue())->toBe('valid');
});

it('AE-04: transisi di luar tabel ditolak sebelum menyentuh DB', function () {
    $versi = app(CekVersiData::class)->transisi($this->dokumen, Valid::class, CekVersiData::versi($this->dokumen));

    expect(fn () => app(CekVersiData::class)->transisi($this->dokumen, Ditolak::class, $versi))
        ->toThrow(TransisiTidakSah::class, 'valid');
    expect($this->dokumen->fresh()->status->getValue())->toBe('valid');
});
