<?php

use App\Actions\Pendaftaran\AjukanFormulir;
use App\Actions\Pendaftaran\PilihPeminatan;
use App\Services\Notifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Storage::fake('private');
    $this->pp = bukaPeriode();
    $this->peserta = buatPengguna('peserta', ['name' => 'Atma Fauzilla']);
});

it('HW-13: tanpa pendaftaran → ajakan mulai mendaftar; menu Status mengarah ke HW-14', function () {
    $this->actingAs($this->peserta)->get(route('dasbor'))->assertOk()
        ->assertSee('Selamat datang, Atma!')->assertSee('Belum ada pendaftaran')->assertSee(route('pendaftaran.baru'));
    $this->actingAs($this->peserta)->get(route('status'))->assertRedirect(route('pendaftaran.baru'));
    $this->actingAs($this->peserta)->get(route('pendaftaran.saya'))->assertRedirect(route('pendaftaran.baru'));
});

it('FR-15/AC-07.1: dasbor Draf menampilkan nomor, tracker tahap 1, pengingat batas periode; tanpa tombol kartu', function () {
    $p = app(PilihPeminatan::class)($this->peserta, $this->pp->id);

    $this->actingAs($this->peserta)->get(route('dasbor'))->assertOk()
        ->assertSee($p->nomor_pendaftaran)->assertSee('Tahap 1 dari 5 · Draf, belum diajukan')
        ->assertSee('Ajukan formulir sebelum')->assertSee('0 dari 3 berkas wajib diunggah')
        ->assertDontSee('Unduh Kartu Pendaftar');
});

it('AC-07.1/AC-07.3/FR-68: setelah Diajukan tracker tahap 2, halaman status berisi riwayat & tombol kartu', function () {
    $p = drafLengkap($this->peserta, $this->pp);
    app(AjukanFormulir::class)($p);

    $this->actingAs($this->peserta)->get(route('dasbor'))->assertOk()
        ->assertSee('Tahap 2 dari 5 · Diajukan')->assertSee(route('pendaftaran.kartu', $p));
    $this->actingAs($this->peserta)->get(route('status'))->assertOk()
        ->assertSee('Draf disimpan')->assertSee('Formulir diajukan')->assertSee('Verifikasi Berkas')
        ->assertSee(route('pendaftaran.kartu', $p));
});

it('AC-07.3/K-42: Masuk Kuota tanpa informasi kuota/Daftar Tunggu; Dibatalkan tampil dengan alasan', function () {
    $p = drafLengkap($this->peserta, $this->pp);
    app(AjukanFormulir::class)($p);

    DB::table('pendaftarans')->where('id', $p->id)->update(['status' => 'masuk_kuota', 'waktu_masuk_kuota' => now()]);
    $this->actingAs($this->peserta)->get(route('status'))->assertSee('Berkas valid dan Anda Masuk Kuota')
        ->assertDontSee('Daftar Tunggu')->assertDontSee('slot');
    $this->actingAs($this->peserta)->get(route('dasbor'))->assertSee('Tahap 4 dari 5 · Masuk Kuota')->assertDontSee('Daftar Tunggu');

    DB::table('pendaftarans')->where('id', $p->id)->update(['status' => 'dibatalkan', 'alasan_batal' => 'Periode pendaftaran ditutup sebelum formulir diajukan']);
    $this->actingAs($this->peserta)->get(route('status'))->assertSee('Pendaftaran dibatalkan')->assertSee('Periode pendaftaran ditutup sebelum formulir diajukan');
});

it('HW-15: formulir Draf dapat diedit; setelah Diajukan semua isian baca-saja', function () {
    $p = drafLengkap($this->peserta, $this->pp);
    $this->actingAs($this->peserta)->get(route('pendaftaran.formulir', $p))->assertOk()
        ->assertSee('Ajukan formulir')->assertSee('2171020807070003')->assertDontSee('<fieldset disabled', false);

    app(AjukanFormulir::class)($p);
    $this->actingAs($this->peserta)->get(route('pendaftaran.formulir', $p))->assertOk()
        ->assertSee('Formulir sudah diajukan')->assertSee('<fieldset disabled', false)->assertDontSee('Ajukan formulir</button>', false);
});

it('FR-53/HW-20: notifikasi milik sendiri, buka = tandai dibaca, tandai semua dibaca; notifikasi akun lain 404', function () {
    $notifier = app(Notifier::class);
    $notifier->kirim($this->peserta, 'status_pendaftaran', 'Formulir berhasil diajukan', 'Isi uji', route('status'));
    $notifier->kirim($this->peserta, 'status_pendaftaran', 'Notifikasi kedua', 'Isi uji', 'https://situs-lain.test/jebakan');
    $lain = buatPengguna('peserta');
    $notifier->kirim($lain, 'status_pendaftaran', 'Milik orang lain', 'Rahasia', null);

    $this->actingAs($this->peserta)->get(route('notifikasi'))->assertOk()
        ->assertSee('2 belum dibaca')->assertSee('Formulir berhasil diajukan')->assertDontSee('Milik orang lain');

    $satu = $this->peserta->notifications()->where('data->judul', 'Formulir berhasil diajukan')->firstOrFail();
    $this->actingAs($this->peserta)->get(route('notifikasi.buka', $satu->id))->assertRedirect(route('status'));
    expect($satu->fresh()->read_at)->not->toBeNull();

    $kedua = $this->peserta->notifications()->where('data->judul', 'Notifikasi kedua')->firstOrFail();
    $this->actingAs($this->peserta)->get(route('notifikasi.buka', $kedua->id))->assertRedirect(route('notifikasi')); // bukan open redirect

    $this->actingAs($this->peserta)->get(route('notifikasi.buka', $lain->notifications()->firstOrFail()->id))->assertNotFound();

    $notifier->kirim($this->peserta, 'status_pendaftaran', 'Ketiga', 'x', null);
    $this->actingAs($this->peserta)->post(route('notifikasi.baca-semua'))->assertRedirect();
    expect($this->peserta->unreadNotifications()->count())->toBe(0);
});

it('BR-09: selama Draf formulir punya tautan "Ubah pilihan" ke HW-14 (Batal kembali ke formulir); setelah Diajukan tautan hilang', function () {
    $p = drafLengkap($this->peserta, $this->pp);
    $lain = bukaPeriode('TRMM', [], ['periode_pendaftaran_id' => $this->pp->periode_pendaftaran_id]);

    $this->actingAs($this->peserta)->get(route('pendaftaran.formulir', $p))->assertOk()
        ->assertSee('Ubah track')->assertSee(route('pendaftaran.baru'));
    $this->actingAs($this->peserta)->get(route('pendaftaran.baru'))->assertOk()
        ->assertSee(route('pendaftaran.formulir', $p)); // tombol Batal

    $this->actingAs($this->peserta)->post(route('pendaftaran.store'), ['peminatan_periode_id' => $lain->id])
        ->assertRedirect(route('pendaftaran.formulir', $p));
    expect($p->fresh()->peminatan_periode_id)->toBe($lain->id)->and($p->fresh()->nomor_pendaftaran)->toBe($p->nomor_pendaftaran);

    app(AjukanFormulir::class)($p);
    $this->actingAs($this->peserta)->get(route('pendaftaran.formulir', $p))->assertOk()
        ->assertDontSee('Ubah track')->assertDontSee('Ubah pilihan');
});
