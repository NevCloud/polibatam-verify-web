<?php

use App\Actions\Master\KelolaPeminatan;
use App\Actions\Master\KelolaTrack;
use App\Models\AuditLog;
use App\Models\Peminatan;
use App\Models\PeminatanPeriode;
use App\Models\Track;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Storage::fake('private');
    Notification::fake();
    $this->track = buatPengguna('admin_track');
});

it('FR-45/BR-52: tambah & ubah track, kode unik (huruf besar otomatis), tercatat di audit', function () {
    $this->actingAs($this->track)->get(route('admin.track.index'))->assertOk()->assertSee('Teknik Informatika');

    $this->post(route('admin.track.store'), ['kode' => 'ds', 'nama' => 'Data Science'])->assertSessionHasNoErrors();
    $baru = Track::where('kode', 'DS')->firstOrFail();
    expect($baru->is_active)->toBeTrue()->and(AuditLog::where('aksi', 'tambah_track')->where('entitas_id', $baru->id)->exists())->toBeTrue();

    $this->post(route('admin.track.store'), ['kode' => 'TI', 'nama' => 'Duplikat'])->assertSessionHasErrorsIn('track', ['kode' => 'Kode track sudah digunakan.']);
    $this->put(route('admin.track.update', $baru->id), ['kode' => 'DS', 'nama' => 'Sains Data'])->assertSessionHasNoErrors();
    expect($baru->fresh()->nama)->toBe('Sains Data');
});

it('FR-46/BR-52/AC-21.1/21.2: tambah peminatan (durasi ≥ 1, kode unik)', function () {
    $ti = Track::where('kode', 'TI')->firstOrFail();
    $this->actingAs($this->track)->post(route('admin.peminatan.store'), ['track_id' => $ti->id, 'kode' => 'trai', 'nama' => 'Kecerdasan Artifisial', 'durasi_semester' => 2])
        ->assertSessionHasNoErrors();
    expect(Peminatan::where('kode', 'TRAI')->firstOrFail()->is_active)->toBeTrue();

    $this->post(route('admin.peminatan.store'), ['track_id' => $ti->id, 'kode' => 'TRPL', 'nama' => 'X', 'durasi_semester' => 0])
        ->assertSessionHasErrorsIn('peminatan', ['kode', 'durasi_semester']);
});

it('BR-53/AC-21.3/21.4: peminatan terpakai tidak dapat dihapus (hanya dinonaktifkan → hilang dari katalog); kodenya terkunci', function () {
    $pp = bukaPeriode();
    pendaftaranBertagihan($pp, false);
    $trpl = Peminatan::where('kode', 'TRPL')->firstOrFail();

    $this->actingAs($this->track)->delete(route('admin.peminatan.destroy', $trpl->id))->assertSessionHasErrors(['peminatan' => KelolaPeminatan::PESAN_HAPUS]);
    $this->put(route('admin.peminatan.update', $trpl->id), ['track_id' => $trpl->track_id, 'kode' => 'TRPX', 'nama' => $trpl->nama, 'durasi_semester' => 5])
        ->assertSessionHasErrors(['kode' => KelolaPeminatan::PESAN_KODE]);
    expect(Peminatan::find($trpl->id))->not->toBeNull();

    $this->put(route('admin.peminatan.status', $trpl->id), ['aktif' => 0])->assertSessionHasNoErrors();
    expect(PeminatanPeriode::terbuka()->whereKey($pp->id)->exists())->toBeFalse();
    $this->actingAs(buatPengguna('peserta'))->get(route('pendaftaran.baru'))->assertDontSee('Rekayasa Perangkat Lunak');
});

it('BR-53: peminatan & track yang belum pernah dipakai dapat dihapus; track berpeminatan ditolak', function () {
    $te = Track::where('kode', 'TE')->firstOrFail();
    $baru = app(KelolaPeminatan::class)->simpan(['track_id' => $te->id, 'kode' => 'HAPUS', 'nama' => 'Coba', 'durasi_semester' => 1], null, $this->track);

    $this->actingAs($this->track)->delete(route('admin.track.destroy', $te->id))->assertSessionHasErrors(['track' => KelolaTrack::PESAN_HAPUS]);
    $this->delete(route('admin.peminatan.destroy', $baru->id))->assertSessionHasNoErrors();
    expect(Peminatan::find($baru->id))->toBeNull()->and(AuditLog::where('aksi', 'hapus_peminatan')->exists())->toBeTrue();

    $kosong = app(KelolaTrack::class)->simpan(['kode' => 'KSG', 'nama' => 'Kosong'], null, $this->track);
    $this->delete(route('admin.track.destroy', $kosong->id))->assertSessionHasNoErrors();
    expect(Track::find($kosong->id))->toBeNull();
});

it('NFR-04: kelola track & peminatan hanya Admin Track & Super Admin', function (string $role) {
    $u = buatPengguna($role);
    $this->actingAs($u)->get(route('admin.track.index'))->assertForbidden();
    $this->actingAs($u)->post(route('admin.peminatan.store'), [])->assertForbidden();
})->with(['admin_silau', 'admin_keuangan', 'peserta']);

it('Super Admin juga dapat membuka halaman data master', function () {
    $sa = buatPengguna('super_admin');
    foreach (['admin.track.index', 'admin.peminatan.index', 'admin.tarif.index', 'admin.kuota.index', 'admin.kuota.dasbor'] as $rute) {
        $this->actingAs($sa)->get(route($rute))->assertOk();
    }
});
