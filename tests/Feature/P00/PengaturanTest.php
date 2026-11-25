<?php

use App\Services\Pengaturan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('seeder 04: 5 role (T-07), 26 permission, pemetaan role ↔ permission', function () {
    expect(DB::table('roles')->orderBy('name')->pluck('name')->all())
        ->toBe(['admin_keuangan', 'admin_silau', 'admin_track', 'peserta', 'super_admin'])
        ->and(DB::table('permissions')->count())->toBe(26);

    $milik = fn (string $role) => DB::table('role_has_permissions')
        ->join('roles', 'roles.id', '=', 'role_has_permissions.role_id')
        ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
        ->where('roles.name', $role)->pluck('permissions.name');

    // BR-67: ekspor hanya SILAU & Super Admin; K-29: API mobile hanya SILAU & Keuangan
    expect($milik('admin_silau'))->toContain('data-pendaftaran.ekspor', 'api-mobile.akses')
        ->and($milik('admin_keuangan'))->not->toContain('data-pendaftaran.ekspor')
        ->and($milik('super_admin'))->toContain('data-pendaftaran.ekspor')->not->toContain('api-mobile.akses')
        ->and($milik('peserta'))->toHaveCount(3);
});

it('seeder 04: kriteria checklist, track, dan peminatan contoh', function () {
    expect(DB::table('kriteria_checklists')->count())->toBe(10)
        ->and(DB::table('tracks')->where('kode', 'TI')->value('nama'))->toBe('Teknik Informatika') // T-08
        ->and(DB::table('tracks')->count())->toBe(4)
        ->and(DB::table('peminatans')->count())->toBe(8);
});

it('NFR-40/AE-14: parameter bisnis dibaca dari tabel pengaturans hasil seeder', function () {
    $p = app(Pengaturan::class);
    $tahun = now('UTC')->year;

    expect($p->int('batas_pelunasan_formulir_hari'))->toBe(7)
        ->and($p->int('jatuh_tempo_semester_hari_sebelum'))->toBe(7)
        ->and($p->int('rollover_peringatan_hari'))->toBe(14)
        ->and($p->int('masa_berlaku_ekspor_jam'))->toBe(24)
        ->and($p->int('ukuran_maks_unggah_kb'))->toBe(2048)
        ->and($p->get('format_npd'))->toBe('MC-{KODE_PEMINATAN}-{TAHUN}-{URUT4}')
        ->and($p->get('kode_unik_rentang'))->toBe(['min' => 1, 'maks' => 999])
        ->and($p->int("urut_nomor_pendaftaran_{$tahun}"))->toBe(0)
        ->and($p->int("urut_npd_{$tahun}"))->toBe(0);
});

it('NFR-40: perubahan nilai terbaca tanpa mengubah kode', function () {
    $p = app(Pengaturan::class);
    expect($p->int('batas_pelunasan_formulir_hari'))->toBe(7);

    DB::table('pengaturans')->where('kunci', 'batas_pelunasan_formulir_hari')->update(['nilai' => json_encode(10)]);
    $p->segarkan();

    expect($p->int('batas_pelunasan_formulir_hari'))->toBe(10);
});

it('AE-14: kunci yang belum ada adalah galat, bukan angka bawaan', function () {
    app(Pengaturan::class)->get('tidak_ada');
})->throws(RuntimeException::class, 'tidak_ada');
