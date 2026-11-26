<?php

use App\Actions\Kuota\AturKuota;
use App\Models\AuditLog;
use App\Models\Peminatan;
use App\Models\PeminatanPeriode;
use App\Models\PeriodePendaftaran;
use App\Models\Tagihan;
use App\Models\Tarif;
use App\Queries\KuotaQuery;
use App\Services\TarifResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Storage::fake('private');
    Notification::fake();
    $this->track = buatPengguna('admin_track');
    $this->trpl = Peminatan::where('kode', 'TRPL')->firstOrFail();
});

function simpanTarif(string $tanggal, array $nominal)
{
    return test()->post(route('admin.tarif.store', 'TRPL'), ['tanggal_efektif' => $tanggal, 'nominal' => $nominal]);
}

it('Uji wajib BR-26/AC-22.3/22.5: tarif baru = versi baru; versi lama & tagihan lama tidak berubah', function () {
    rekeningAktif();
    $pp = bukaPeriode();
    $p = pendaftaranBertagihan($pp, false);
    $tagihan = Tagihan::where('pendaftaran_id', $p->id)->firstOrFail();
    $lama = Tarif::where('peminatan_id', $this->trpl->id)->where('jenis', 'formulir')->firstOrFail();

    $this->actingAs($this->track)->get(route('admin.tarif.show', 'TRPL'))->assertOk()->assertSee('Rp 300.000');
    simpanTarif(now()->toDateString(), ['formulir' => 'Rp 350.000', 'semester_1' => '6500000'])->assertSessionHasNoErrors();

    expect($lama->fresh()->nominal)->toBe(300000)
        ->and(Tarif::where('peminatan_id', $this->trpl->id)->count())->toBe(3)
        ->and(app(TarifResolver::class)->untuk($this->trpl->id, 'formulir')->nominal)->toBe(350000)
        ->and($tagihan->fresh()->nominal_dasar)->toBe(300000)
        ->and(AuditLog::where('aksi', 'simpan_tarif')->exists())->toBeTrue();

    // Nominal sama → tidak ada versi baru; tanggal yang sama untuk item yang sama → ditolak (versi tidak dapat diubah).
    simpanTarif(now()->toDateString(), ['formulir' => '350000'])->assertSessionHasErrors('nominal');
    simpanTarif(now()->toDateString(), ['formulir' => '360000'])->assertSessionHasErrors('tanggal_efektif');
});

it('Uji wajib BR-25/AC-22.4: tarif ≤ 0 atau tanggal kosong/mundur ditolak', function () {
    $this->actingAs($this->track);
    simpanTarif(now()->toDateString(), ['semester_2' => '0'])->assertSessionHasErrors('nominal.semester_2');
    simpanTarif('', ['semester_2' => '5000000'])->assertSessionHasErrors('tanggal_efektif');
    simpanTarif(now()->subDays(2)->toDateString(), ['semester_2' => '5000000'])->assertSessionHasErrors('tanggal_efektif');
    expect(Tarif::where('peminatan_id', $this->trpl->id)->where('semester_ke', 2)->exists())->toBeFalse();
});

it('BR-27: tarif terjadwal belum dipakai sebelum tanggal efektifnya', function () {
    $this->actingAs($this->track);
    simpanTarif(now()->addDays(10)->toDateString(), ['semester_3' => '7000000'])->assertSessionHasNoErrors();

    expect(app(TarifResolver::class)->untuk($this->trpl->id, 'semester', 3))->toBeNull()
        ->and(app(TarifResolver::class)->untuk($this->trpl->id, 'semester', 3, now()->addDays(11))->nominal)->toBe(7000000);
});

it('RBAC: Admin Keuangan hanya membaca tarif; Admin SILAU tidak dapat membuka tarif', function () {
    $k = buatPengguna('admin_keuangan');
    $this->actingAs($k)->get(route('admin.tarif.index'))->assertOk();
    $this->actingAs($k)->get(route('admin.tarif.show', 'TRPL'))->assertOk()->assertDontSee('Simpan sebagai versi baru');
    $this->actingAs($k)->post(route('admin.tarif.store', 'TRPL'), ['tanggal_efektif' => now()->toDateString(), 'nominal' => ['formulir' => '1']])->assertForbidden();
    $this->actingAs(buatPengguna('admin_silau'))->get(route('admin.tarif.index'))->assertForbidden();
});

it('FR-48/BR-15/AC-23.1/23.2: Admin Track mengatur kuota minimum sebelum periode dimulai; negatif ditolak; tanpa kuota maksimum', function () {
    $periode = PeriodePendaftaran::create(['nama' => 'Gelombang Depan', 'tanggal_mulai' => now()->addWeek(), 'tanggal_selesai' => now()->addMonths(2)]);

    $this->actingAs($this->track)->get(route('admin.kuota.index', ['periode' => $periode->id]))->assertOk()->assertSee('Belum diatur');
    $this->post(route('admin.kuota.simpan'), ['periode' => $periode->id, 'kuota' => [$this->trpl->id => -1]])->assertSessionHasErrors("kuota.{$this->trpl->id}");
    $this->post(route('admin.kuota.simpan'), ['periode' => $periode->id, 'kuota' => [$this->trpl->id => 20]])->assertSessionHasNoErrors();

    $pp = PeminatanPeriode::where('periode_pendaftaran_id', $periode->id)->where('peminatan_id', $this->trpl->id)->firstOrFail();
    expect($pp->kuota_min)->toBe(20)->and(AuditLog::where('aksi', 'atur_kuota')->where('entitas_id', $pp->id)->exists())->toBeTrue();

    $this->delete(route('admin.kuota.lepas', $pp->id))->assertSessionHasNoErrors();
    expect(PeminatanPeriode::find($pp->id))->toBeNull();
});

it('Uji wajib BR-20/AC-23.3: setelah periode dimulai Admin Track tidak dapat mengubah kuota minimum; Super Admin wajib alasan', function () {
    $pp = bukaPeriode(kuota: ['kuota_min' => 10]);

    $this->actingAs($this->track)->post(route('admin.kuota.simpan'), ['periode' => $pp->periode_pendaftaran_id, 'kuota' => [$this->trpl->id => 5]])
        ->assertSessionHasErrors("kuota.{$this->trpl->id}");
    expect($pp->fresh()->kuota_min)->toBe(10);
    $this->get(route('admin.kuota.index', ['periode' => $pp->periode_pendaftaran_id]))->assertSee('Kuota minimum terkunci');

    $sa = buatPengguna('super_admin');
    $this->actingAs($sa)->post(route('admin.kuota.simpan'), ['periode' => $pp->periode_pendaftaran_id, 'kuota' => [$this->trpl->id => 5]])
        ->assertSessionHasErrors(['alasan' => AturKuota::PESAN_ALASAN]);
    $this->actingAs($sa)->post(route('admin.kuota.simpan'), ['periode' => $pp->periode_pendaftaran_id, 'kuota' => [$this->trpl->id => 5], 'alasan' => 'Penyesuaian kapasitas laboratorium'])
        ->assertSessionHasNoErrors();
    expect($pp->fresh()->kuota_min)->toBe(5)
        ->and(AuditLog::where('aksi', 'atur_kuota')->where('user_id', $sa->id)->value('alasan'))->toBe('Penyesuaian kapasitas laboratorium');
});

it('RBAC kuota: Admin SILAU hanya membaca (kuota & dasbor); Admin Keuangan tidak berhak', function () {
    bukaPeriode();
    $silau = buatPengguna('admin_silau');
    $this->actingAs($silau)->get(route('admin.kuota.index'))->assertOk()->assertDontSee('Simpan kuota');
    $this->actingAs($silau)->get(route('admin.kuota.dasbor'))->assertOk();
    $this->actingAs($silau)->post(route('admin.kuota.simpan'), [])->assertForbidden();
    $this->actingAs(buatPengguna('admin_keuangan'))->get(route('admin.kuota.dasbor'))->assertForbidden();
});

it('FR-49/AC-24.1/24.2: dasbor kuota menghitung per status; Peringatan bila ≤ 7 hari & NPD < minimum; Tercapai bila cukup', function () {
    rekeningAktif();
    $pp = bukaPeriode(periode: ['tanggal_selesai' => now()->addDays(5)], kuota: ['kuota_min' => 2]);
    pendaftaranBertagihan($pp);          // Masuk Kuota
    pendaftaranBertagihan($pp, false);   // Diajukan

    $this->actingAs($this->track)->get(route('admin.kuota.dasbor'))->assertOk()
        ->assertSee('Peringatan')->assertSee('H-5 penutupan');

    DB::table('pendaftarans')->where('peminatan_periode_id', $pp->id)->update(['status' => 'npd_terbit']);
    $baris = KuotaQuery::baris($pp->periode);
    expect($baris[0])->toMatchArray(['npd_terbit' => 2, 'kuota_min' => 2, 'persen' => 100, 'penanda' => 'Tercapai']);
});
