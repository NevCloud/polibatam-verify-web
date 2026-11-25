<?php

use App\Actions\Tagihan\KelolaRekening;
use App\Actions\Tagihan\TerbitkanTagihan;
use App\Models\AuditLog;
use App\Models\RekeningInstitusi;
use App\Models\Tagihan;
use App\Notifications\Pemberitahuan;
use App\Support\Ui\Format;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Storage::fake('private');
    Notification::fake();
    $this->pp = bukaPeriode();
    $this->rekening = rekeningAktif('formulir');
});

it('FR-26/BR-29/BR-31/BR-71: formulir diajukan → tagihan formulir terbit dengan kode referensi, kode unik, snapshot rekening', function () {
    $p = pendaftaranBertagihan($this->pp, false);
    $t = Tagihan::where('pendaftaran_id', $p->id)->firstOrFail();
    $tahun = Format::wib(now())->year;

    expect($t->kode_referensi)->toBe("TG-{$tahun}-000001")
        ->and($t->jenis)->toBe('formulir')
        ->and($t->status->getValue())->toBe('belum_bayar')
        ->and($t->nominal_dasar)->toBe(300000)
        ->and($t->kode_unik)->toBeGreaterThanOrEqual(1)->toBeLessThanOrEqual(999)
        ->and($t->nominal_total)->toBe(300000 + $t->kode_unik)
        ->and($t->rekening_nomor)->toBe('0123456789')
        ->and($t->rekening_bank)->toBe('Bank Negara Indonesia (BNI)')
        ->and(AuditLog::where('aksi', 'terbitkan_tagihan')->where('entitas_id', $t->id)->exists())->toBeTrue();
    Notification::assertSentTo($p->user, Pemberitahuan::class, fn ($n) => str_contains($n->judul, 'Tagihan Formulir pendaftaran terbit'));
});

it('BR-33/idempoten: penerbitan ulang mengembalikan tagihan yang sama', function () {
    $p = pendaftaranBertagihan($this->pp, false);
    app(TerbitkanTagihan::class)->formulir($p);
    app(TerbitkanTagihan::class)->formulir($p);

    expect(Tagihan::where('pendaftaran_id', $p->id)->count())->toBe(1);
});

it('Uji wajib BR-28/BR-71: snapshot nominal & rekening tidak berubah saat tarif atau rekening diubah', function () {
    $p = pendaftaranBertagihan($this->pp, false);
    $t = Tagihan::where('pendaftaran_id', $p->id)->firstOrFail();

    DB::table('tarifs')->insert(['peminatan_id' => $this->pp->peminatan_id, 'jenis' => 'formulir', 'semester_ke' => null, 'nominal' => 999000,
        'tanggal_efektif' => now()->toDateString(), 'dibuat_oleh' => buatPengguna('admin_track')->id, 'created_at' => now(), 'updated_at' => now()]);
    app(KelolaRekening::class)->simpan(['jenis_tagihan' => 'formulir', 'nama_bank' => 'Bank Baru', 'nomor_rekening' => '99999999', 'atas_nama' => 'X'],
        $this->rekening, buatPengguna('admin_keuangan'));

    $t->refresh();
    expect($t->nominal_dasar)->toBe(300000)->and($t->rekening_nomor)->toBe('0123456789')->and($t->rekening_bank)->toBe('Bank Negara Indonesia (BNI)');
});

it('BR-71/A-87: tanpa rekening aktif tagihan tidak terbit + peringatan; setelah rekening diaktifkan tagihan tertunda terbit', function () {
    $keuangan = buatPengguna('admin_keuangan');
    app(KelolaRekening::class)->ubahStatus($this->rekening, false, $keuangan);

    $p = pendaftaranBertagihan($this->pp, false);
    expect(Tagihan::where('pendaftaran_id', $p->id)->exists())->toBeFalse();
    Notification::assertSentTo($keuangan, Pemberitahuan::class, fn ($n) => $n->jenis === 'peringatan_rekening');

    app(KelolaRekening::class)->ubahStatus($this->rekening, true, $keuangan);
    expect(Tagihan::where('pendaftaran_id', $p->id)->where('jenis', 'formulir')->exists())->toBeTrue();
});

it('BR-34: tarif belum diatur → tagihan tidak terbit dan Admin Track diperingatkan', function () {
    $track = buatPengguna('admin_track');
    $p = drafLengkap(buatPengguna('peserta'), $this->pp);
    DB::table('tarifs')->where('peminatan_id', $this->pp->peminatan_id)->update(['tanggal_efektif' => now()->addYear()->toDateString()]);

    expect(app(TerbitkanTagihan::class)->formulir($p))->toBeNull();
    Notification::assertSentTo($track, Pemberitahuan::class, fn ($n) => $n->jenis === 'peringatan_tarif');
});

it('Uji wajib BR-29: kode unik tidak kembar di antara tagihan terbuka bernominal sama', function () {
    DB::table('pengaturans')->where('kunci', 'kode_unik_rentang')->update(['nilai' => json_encode(['min' => 1, 'maks' => 2])]);
    $a = pendaftaranBertagihan($this->pp, false);
    $b = pendaftaranBertagihan($this->pp, false);

    $kode = Tagihan::whereIn('pendaftaran_id', [$a->id, $b->id])->pluck('kode_unik')->sort()->values()->all();
    expect($kode)->toBe([1, 2]);

    $c = drafLengkap(buatPengguna('peserta'), $this->pp);
    expect(fn () => app(TerbitkanTagihan::class)->formulir($c))->toThrow(RuntimeException::class, 'Kode unik habis');
});

it('FR-37/BR-04: HW-17 hanya menampilkan tagihan milik sendiri; detail tagihan orang lain 403', function () {
    $p = pendaftaranBertagihan($this->pp, false);
    $t = Tagihan::where('pendaftaran_id', $p->id)->firstOrFail();
    $lain = pendaftaranBertagihan($this->pp, false);

    $this->actingAs($p->user)->get(route('tagihan.index'))->assertOk()
        ->assertSee($t->kode_referensi)->assertSee(Format::rupiah($t->nominal_total))
        ->assertDontSee(Tagihan::where('pendaftaran_id', $lain->id)->value('kode_referensi'));
    $this->actingAs($lain->user)->get(route('tagihan.show', $t))->assertForbidden();
    $this->actingAs($p->user)->get(route('tagihan.show', $t))->assertOk()->assertSee('0123456789')->assertSee('Kirim Bukti Pembayaran');
});

it('HW-13: dasbor peserta menampilkan tagihan formulir yang terbit', function () {
    $p = pendaftaranBertagihan($this->pp, false);
    $t = Tagihan::where('pendaftaran_id', $p->id)->firstOrFail();

    $this->actingAs($p->user)->get(route('dasbor'))->assertOk()->assertSee($t->kode_referensi)->assertSee('Bayar &amp; unggah bukti', false);
});

it('FR-67/BR-70: mengaktifkan rekening lain menonaktifkan rekening aktif sebelumnya; tercatat di audit; selain Keuangan/SA 403', function () {
    $keuangan = buatPengguna('admin_keuangan');
    $this->actingAs($keuangan)->post(route('admin.rekening.store'), ['jenis_tagihan' => 'formulir', 'nama_bank' => 'Bank Mandiri', 'nomor_rekening' => '1080-0123-45678', 'atas_nama' => 'Politeknik Negeri Batam'])
        ->assertSessionHasNoErrors();
    $baru = RekeningInstitusi::where('nama_bank', 'Bank Mandiri')->firstOrFail();
    expect($baru->nomor_rekening)->toBe('1080012345678')->and($baru->status->getValue())->toBe('nonaktif');

    $this->actingAs($keuangan)->put(route('admin.rekening.status', $baru->id), ['aktif' => 1])->assertSessionHasNoErrors();
    expect($baru->fresh()->status->getValue())->toBe('aktif')->and($this->rekening->fresh()->status->getValue())->toBe('nonaktif')
        ->and(AuditLog::whereIn('aksi', ['tambah_rekening', 'aktifkan_rekening'])->count())->toBe(2);

    $this->actingAs($keuangan)->post(route('admin.rekening.store'), ['jenis_tagihan' => 'formulir', 'nama_bank' => 'X', 'nomor_rekening' => '12', 'atas_nama' => 'Y'])
        ->assertSessionHasErrorsIn('rekening', 'nomor_rekening');
    $this->actingAs(buatPengguna('admin_silau'))->get(route('admin.rekening.index'))->assertForbidden();
    $this->actingAs(buatPengguna('peserta'))->get(route('admin.rekening.index'))->assertForbidden();
});
