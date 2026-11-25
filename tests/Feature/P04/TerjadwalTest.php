<?php

use App\Actions\Pendaftaran\AjukanFormulir;
use App\Actions\Pendaftaran\PilihPeminatan;
use App\Events\PeriodeDitutup;
use App\Models\AuditLog;
use App\Models\Pendaftaran;
use App\Notifications\Pemberitahuan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Storage::fake('private');
    Notification::fake();
    $this->pp = bukaPeriode();
});

/** Pendaftaran Masuk Kuota sejak $hari hari lalu (+ tagihan formulir berstatus $tagihan bila diisi). */
function masukKuota(int $hari, ?string $tagihan = null): Pendaftaran
{
    $p = drafLengkap(buatPengguna('peserta'), test()->pp);
    app(AjukanFormulir::class)($p);
    DB::table('pendaftarans')->where('id', $p->id)->update(['status' => 'masuk_kuota', 'waktu_masuk_kuota' => now()->subDays($hari)->subMinute()]);

    if ($tagihan) {
        DB::table('tagihans')->insert([
            'kode_referensi' => 'TG-UJI-'.$p->id, 'user_id' => $p->user_id, 'pendaftaran_id' => $p->id,
            'tarif_id' => DB::table('tarifs')->where('peminatan_id', test()->pp->peminatan_id)->value('id'),
            'jenis' => 'formulir', 'nominal_dasar' => 300000, 'kode_unik' => 100 + $p->id, 'nominal_total' => 300100 + $p->id,
            'status' => $tagihan, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    return $p->fresh();
}

it('Uji wajib BR-21/FR-24: Masuk Kuota > 7 hari tanpa pelunasan → Dibatalkan; tagihan formulir ikut dibatalkan; idempoten', function () {
    $kedaluwarsa = masukKuota(7, 'belum_bayar');
    $tanpaTagihan = masukKuota(8);
    $masihWaktu = masukKuota(6);
    $menungguVerifikasi = masukKuota(10, 'menunggu_verifikasi'); // dikecualikan BR-21

    $this->artisan('pendaftaran:batalkan-kedaluwarsa')->expectsOutput('2 pendaftaran dibatalkan.')->assertSuccessful();
    $this->artisan('pendaftaran:batalkan-kedaluwarsa')->expectsOutput('0 pendaftaran dibatalkan.')->assertSuccessful();

    expect($kedaluwarsa->fresh()->status->getValue())->toBe('dibatalkan')
        ->and($kedaluwarsa->fresh()->alasan_batal)->toContain('7 hari')
        ->and(DB::table('tagihans')->where('pendaftaran_id', $kedaluwarsa->id)->value('status'))->toBe('dibatalkan')
        ->and($tanpaTagihan->fresh()->status->getValue())->toBe('dibatalkan')
        ->and($masihWaktu->fresh()->status->getValue())->toBe('masuk_kuota')
        ->and($menungguVerifikasi->fresh()->status->getValue())->toBe('masuk_kuota')
        ->and(AuditLog::where('aksi', 'batalkan_kedaluwarsa')->count())->toBe(2);
    Notification::assertSentTo($kedaluwarsa->user, Pemberitahuan::class, fn ($n) => $n->judul === 'Pendaftaran dibatalkan');
});

it('Uji wajib BR-65/FR-25: periode:tutup membatalkan Draf, tidak menyentuh yang diajukan, memicu PeriodeDitutup; dijalankan dua kali tanpa efek ganda', function () {
    Event::fake([PeriodeDitutup::class]);
    $draf = app(PilihPeminatan::class)(buatPengguna('peserta'), $this->pp->id);
    $diajukan = drafLengkap(buatPengguna('peserta'), $this->pp);
    app(AjukanFormulir::class)($diajukan);
    $lainBelumSelesai = bukaPeriode('TRMM');
    $drafLain = app(PilihPeminatan::class)(buatPengguna('peserta'), $lainBelumSelesai->id);

    $this->pp->periode->update(['tanggal_selesai' => now()->subMinute()]);

    $this->artisan('periode:tutup')->expectsOutput('1 periode ditutup.')->assertSuccessful();
    $this->artisan('periode:tutup')->expectsOutput('0 periode ditutup.')->assertSuccessful();

    $periode = $this->pp->periode->fresh();
    expect($periode->status)->toBe('ditutup')->and($periode->ditutup_pada)->not->toBeNull()
        ->and($draf->fresh()->status->getValue())->toBe('dibatalkan')
        ->and($draf->fresh()->alasan_batal)->toBe('Periode pendaftaran ditutup sebelum formulir diajukan')
        ->and($diajukan->fresh()->status->getValue())->toBe('diajukan')
        ->and($drafLain->fresh()->status->getValue())->toBe('draf')
        ->and(AuditLog::where('aksi', 'tutup_periode')->count())->toBe(1)
        ->and(DB::table('tagihans')->count())->toBe(0); // BR-65: tanpa tagihan maupun refund
    Event::assertDispatchedTimes(PeriodeDitutup::class, 1);
    Notification::assertSentTo($draf->user, Pemberitahuan::class, fn ($n) => $n->judul === 'Draf pendaftaran dibatalkan');

    // Formulir Draf yang dibatalkan terkunci (BR-65)
    $this->actingAs($draf->user)->post(route('pendaftaran.simpan', $draf), ['tempat_lahir' => 'X'])->assertSessionHasErrors('formulir');
});

it('NFR-25: kedua perintah terdaftar di penjadwal', function () {
    $this->artisan('schedule:list')->expectsOutputToContain('pendaftaran:batalkan-kedaluwarsa')->expectsOutputToContain('periode:tutup');
});
