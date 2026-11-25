<?php

use App\Actions\Akun\KelolaAkunAdmin;
use App\Models\AuditLog;
use App\Models\User;
use App\Notifications\Akun\UndanganAktivasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Notification::fake();
    $this->super = buatPengguna('super_admin');
    pasang2fa($this->super);
});

it('FR-06/AC-27.1: Super Admin membuat akun admin; undangan aktivasi terkirim; tercatat di audit', function () {
    $this->actingAs($this->super)->post(route('admin.akun.store'), [
        'name' => 'Hendra Saputra', 'email' => 'hendra.saputra@polibatam.ac.id', 'role' => 'admin_silau',
    ])->assertSessionHas('status');

    $baru = User::where('email', 'hendra.saputra@polibatam.ac.id')->firstOrFail();
    expect($baru->namaRole())->toBe('admin_silau')
        ->and($baru->two_factor_confirmed_at)->toBeNull()
        ->and(AuditLog::where('aksi', 'buat_akun_admin')->where('entitas_id', $baru->id)->exists())->toBeTrue();
    Notification::assertSentTo($baru, UndanganAktivasi::class);
});

it('BR-06: akun peserta tidak dapat dibuat atau diberi role lewat HW-44', function () {
    $this->actingAs($this->super)->post(route('admin.akun.store'), ['name' => 'X', 'email' => 'x@contoh.test', 'role' => 'peserta'])
        ->assertSessionHasErrors('role');

    $peserta = buatPengguna('peserta');
    $this->actingAs($this->super)->put(route('admin.akun.role', $peserta), ['role' => 'admin_silau'])->assertNotFound();
    $this->actingAs($this->super)->put(route('admin.akun.status', $peserta), ['aktif' => 0, 'alasan' => 'x'])->assertNotFound();
    expect($peserta->fresh()->namaRole())->toBe('peserta');
});

it('HW-44: daftar hanya akun admin, dengan filter peran dan status', function () {
    buatPengguna('peserta', ['name' => 'Peserta Tersembunyi']);
    pasang2fa(buatPengguna('admin_keuangan', ['name' => 'Yuliana Siregar']));
    $menunggu = buatPengguna('admin_track', ['name' => 'Bayu Prasetyo']);

    $this->actingAs($this->super)->get(route('admin.akun.index'))
        ->assertOk()->assertSee('Yuliana Siregar')->assertSee('Bayu Prasetyo')->assertDontSee('Peserta Tersembunyi')
        ->assertSee('Menunggu Aktivasi');
    $this->actingAs($this->super)->get(route('admin.akun.index', ['role' => 'admin_keuangan']))
        ->assertSee('Yuliana Siregar')->assertDontSee('Bayu Prasetyo');
    $this->actingAs($this->super)->get(route('admin.akun.index', ['status' => 'menunggu_aktivasi']))
        ->assertSee($menunggu->name)->assertDontSee('Yuliana Siregar');
});

it('FR-07/AC-27.3/NFR-04: rute akun hanya untuk Super Admin (403 untuk role lain)', function (string $role) {
    $user = buatPengguna($role);
    $target = buatPengguna('admin_silau');

    $this->actingAs($user)->get(route('admin.akun.index'))->assertForbidden();
    $this->actingAs($user)->post(route('admin.akun.store'), ['name' => 'X', 'email' => 'x@contoh.test', 'role' => 'super_admin'])->assertForbidden();
    $this->actingAs($user)->put(route('admin.akun.role', $target), ['role' => 'super_admin'])->assertForbidden();
    $this->actingAs($user)->put(route('admin.akun.status', $target), ['aktif' => 0, 'alasan' => 'x'])->assertForbidden();
    expect(User::where('email', 'x@contoh.test')->exists())->toBeFalse()
        ->and($target->fresh()->namaRole())->toBe('admin_silau');
})->with(['peserta', 'admin_silau', 'admin_keuangan', 'admin_track']);

it('FR-07/NFR-04: portal admin menolak peserta dan portal peserta menolak admin (403); tamu ke login', function () {
    $peserta = buatPengguna('peserta');
    $admin = buatPengguna('admin_silau');

    $this->actingAs($peserta)->get(route('admin.dasbor'))->assertForbidden();
    $this->actingAs($admin)->get(route('dasbor'))->assertForbidden();
    auth()->logout();
    $this->get(route('akun.profil'))->assertRedirect(route('login'));
});

it('FR-06/AC-27.2/AC-27.6: menonaktifkan akun mencabut sesi web dan token mobile, alasan wajib, tercatat', function () {
    $admin = buatPengguna('admin_silau');
    $admin->createToken('mobile');
    DB::table('sessions')->insert(['id' => 'sesi-admin', 'user_id' => $admin->id, 'payload' => '', 'last_activity' => time()]);

    $this->actingAs($this->super)->put(route('admin.akun.status', $admin), ['aktif' => 0, 'alasan' => ''])->assertSessionHasErrors('alasan');
    $this->actingAs($this->super)->put(route('admin.akun.status', $admin), ['aktif' => 0, 'alasan' => 'Pindah unit'])->assertSessionHas('status');

    expect($admin->fresh()->is_active)->toBeFalse()
        ->and($admin->tokens()->count())->toBe(0)
        ->and(DB::table('sessions')->where('user_id', $admin->id)->count())->toBe(0)
        ->and(AuditLog::where('aksi', 'nonaktifkan_akun')->where('entitas_id', $admin->id)->value('alasan'))->toBe('Pindah unit');
});

it('BR-60/AC-27.6: mengubah role mencabut token mobile dan tercatat sebelum/sesudah', function () {
    $admin = buatPengguna('admin_silau');
    $admin->createToken('mobile');

    $this->actingAs($this->super)->put(route('admin.akun.role', $admin), ['role' => 'admin_keuangan'])->assertSessionHas('status');

    $log = AuditLog::where('aksi', 'ubah_role')->where('entitas_id', $admin->id)->firstOrFail();
    expect($admin->fresh()->namaRole())->toBe('admin_keuangan')
        ->and($admin->tokens()->count())->toBe(0)
        ->and($log->nilai_sebelum)->toEqual(['role' => 'admin_silau'])
        ->and($log->nilai_sesudah)->toEqual(['role' => 'admin_keuangan']);
});

it('BR-05/AC-27.4: Super Admin terakhir tidak dapat dinonaktifkan atau diturunkan; penolakan tercatat', function () {
    $this->actingAs($this->super)->put(route('admin.akun.status', $this->super), ['aktif' => 0, 'alasan' => 'uji'])
        ->assertSessionHasErrors(['akun' => KelolaAkunAdmin::PESAN_SUPER_ADMIN_TERAKHIR]);
    $this->actingAs($this->super)->put(route('admin.akun.role', $this->super), ['role' => 'admin_silau'])
        ->assertSessionHasErrors('akun');

    expect($this->super->fresh()->is_active)->toBeTrue()
        ->and($this->super->fresh()->namaRole())->toBe('super_admin')
        ->and(AuditLog::where('hasil', 'ditolak')->where('alasan', 'super_admin_terakhir')->count())->toBe(2);
});

it('BR-05: Super Admin boleh dinonaktifkan bila masih ada Super Admin aktif lain; nonaktif tidak dihitung', function () {
    $kedua = buatPengguna('super_admin');
    $nonaktif = buatPengguna('super_admin', ['is_active' => false]);

    $this->actingAs($this->super)->put(route('admin.akun.status', $kedua), ['aktif' => 0, 'alasan' => 'Rotasi'])->assertSessionHas('status');
    $this->actingAs($this->super)->put(route('admin.akun.role', $this->super), ['role' => 'admin_silau'])->assertSessionHasErrors('akun');
    expect($nonaktif->fresh()->is_active)->toBeFalse();
});

it('FR-06: akun nonaktif dapat diaktifkan kembali dengan alasan', function () {
    $admin = buatPengguna('admin_track', ['is_active' => false]);

    $this->actingAs($this->super)->put(route('admin.akun.status', $admin), ['aktif' => 1, 'alasan' => 'Kembali bertugas'])->assertSessionHas('status');
    expect($admin->fresh()->is_active)->toBeTrue()
        ->and(AuditLog::where('aksi', 'aktifkan_akun')->where('entitas_id', $admin->id)->exists())->toBeTrue();
});

it('FR-06: undangan dapat dikirim ulang hanya untuk akun yang belum menyelesaikan aktivasi', function () {
    $baru = buatPengguna('admin_track');
    $sudah = buatPengguna('admin_silau');
    pasang2fa($sudah);

    $this->actingAs($this->super)->post(route('admin.akun.undangan', $baru))->assertSessionHas('status');
    Notification::assertSentTo($baru, UndanganAktivasi::class);
    expect(Password::broker('aktivasi')->tokenExists($baru, 'salah'))->toBeFalse();

    $this->actingAs($this->super)->post(route('admin.akun.undangan', $sudah))->assertSessionHasErrors('akun');
});
