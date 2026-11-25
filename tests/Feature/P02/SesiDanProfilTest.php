<?php

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('NFR-03: sesi admin berakhir setelah 30 menit tidak aktif', function () {
    $admin = buatPengguna('admin_silau');

    $this->actingAs($admin)->get(route('akun.profil'))->assertOk();
    $this->travel(29)->minutes();
    $this->get(route('akun.profil'))->assertOk();
    $this->travel(31)->minutes();
    $this->get(route('akun.profil'))->assertRedirect(route('login'))->assertSessionHas('status');
    $this->assertGuest();
});

it('NFR-03: sesi peserta berakhir setelah 60 menit tidak aktif', function () {
    $peserta = buatPengguna('peserta');

    $this->actingAs($peserta)->get(route('dasbor'))->assertOk();
    $this->travel(59)->minutes();
    $this->get(route('dasbor'))->assertOk();
    $this->travel(61)->minutes();
    $this->get(route('dasbor'))->assertRedirect(route('login'));
});

it('NFR-03/AC-27.2: akun yang dinonaktifkan langsung keluar pada permintaan berikutnya', function () {
    $admin = buatPengguna('admin_keuangan');
    $this->actingAs($admin)->get(route('akun.profil'))->assertOk();

    $admin->update(['is_active' => false]); // di aplikasi nyata model dimuat ulang tiap permintaan
    $this->get(route('akun.profil'))->assertRedirect(route('login'));
    $this->assertGuest();
});

it('NFR-03: cookie sesi HttpOnly dan SameSite; Secure aktif di produksi', function () {
    expect(config('session.http_only'))->toBeTrue()
        ->and(config('session.same_site'))->toBe('lax')
        ->and(file_get_contents(config_path('session.php')))->toContain("env('APP_ENV') === 'production'");
});

it('HW-12: profil menampilkan data akun, peran, dan sesi; nama dapat diubah', function () {
    $peserta = buatPengguna('peserta', ['name' => 'Nabila Putri Ramadhani']);

    $this->actingAs($peserta)->get(route('akun.profil'))->assertOk()
        ->assertSee('Nabila Putri Ramadhani')->assertSee('Calon Peserta')->assertDontSee('Recovery code 2FA');
    $this->actingAs($peserta)->put(route('akun.profil.update'), ['name' => 'Nabila Ramadhani'])->assertSessionHas('status');
    expect($peserta->fresh()->name)->toBe('Nabila Ramadhani');
});

it('HW-12: ganti password butuh password lama, mencabut sesi & token lain, tercatat', function () {
    $peserta = buatPengguna('peserta');
    $peserta->createToken('lain');
    DB::table('sessions')->insert(['id' => 'sesi-lain', 'user_id' => $peserta->id, 'payload' => '', 'last_activity' => time()]);

    $this->actingAs($peserta)->put(route('akun.password.update'), ['current_password' => 'salah', 'password' => 'BaruSekali1', 'password_confirmation' => 'BaruSekali1'])
        ->assertSessionHasErrorsIn('sandi', 'current_password');
    $this->actingAs($peserta)->put(route('akun.password.update'), ['current_password' => 'password', 'password' => 'BaruSekali1', 'password_confirmation' => 'BaruSekali1'])
        ->assertSessionHas('status');

    expect(Hash::check('BaruSekali1', $peserta->fresh()->password))->toBeTrue()
        ->and(DB::table('sessions')->where('id', 'sesi-lain')->exists())->toBeFalse()
        ->and($peserta->tokens()->count())->toBe(0)
        ->and(AuditLog::where('aksi', 'ubah_password')->where('user_id', $peserta->id)->exists())->toBeTrue();
});

it('FR-59/HW-12: admin melihat ulang dan membuat ulang recovery code setelah memasukkan password', function () {
    $admin = buatPengguna('admin_silau');
    pasang2fa($admin);
    $lama = $admin->recoveryCodes();

    $this->actingAs($admin)->post(route('akun.kode-pemulihan'), ['current_password' => 'salah', 'aksi' => 'lihat'])
        ->assertSessionHasErrorsIn('kode', 'current_password');
    $this->actingAs($admin)->post(route('akun.kode-pemulihan'), ['current_password' => 'password', 'aksi' => 'lihat'])
        ->assertSessionHas('kode_pemulihan', $lama);
    $this->actingAs($admin)->post(route('akun.kode-pemulihan'), ['current_password' => 'password', 'aksi' => 'buat_ulang']);

    expect(array_intersect($lama, $admin->fresh()->recoveryCodes()))->toBe([])
        ->and(AuditLog::where('aksi', 'buat_ulang_kode_pemulihan')->exists())->toBeTrue();
});

it('HW-12: peserta tidak punya recovery code (403)', function () {
    $this->actingAs(buatPengguna('peserta'))->post(route('akun.kode-pemulihan'), ['current_password' => 'password', 'aksi' => 'lihat'])->assertForbidden();
});

it('HW-12: akhiri semua sesi lain', function () {
    $peserta = buatPengguna('peserta');
    $peserta->createToken('mobile');
    DB::table('sessions')->insert(['id' => 'sesi-lain', 'user_id' => $peserta->id, 'payload' => '', 'last_activity' => time()]);

    $this->actingAs($peserta)->post(route('akun.sesi.akhiri'), ['current_password' => 'password'])->assertSessionHas('status');
    expect(DB::table('sessions')->where('id', 'sesi-lain')->exists())->toBeFalse()->and($peserta->tokens()->count())->toBe(0);
});

it('NFR-43: tidak ada ketergantungan SSO; semua akun dikelola di tabel users sendiri', function () {
    expect(config('auth.providers.users.model'))->toBe(User::class)
        ->and(config('fortify.guard'))->toBe('web');
});
