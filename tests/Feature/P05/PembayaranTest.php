<?php

use App\Actions\Pendaftaran\PilihPeminatan;
use App\Actions\Tagihan\VerifikasiPembayaran;
use App\Models\AuditLog;
use App\Models\BuktiPembayaran;
use App\Models\PesertaDidik;
use App\Models\Tagihan;
use App\Notifications\Pemberitahuan;
use App\Services\CekVersiData;
use App\Support\Ui\Format;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Storage::fake('private');
    Notification::fake();
    $this->pp = bukaPeriode();
    rekeningAktif('formulir');
    rekeningAktif('semester', 'Bank Rakyat Indonesia (BRI)', '009801000123304');
    tarifSemester($this->pp->peminatan_id, 1, 6500000);
    $this->keuangan = buatPengguna('admin_keuangan');
    pasang2fa($this->keuangan);
});

/** Tagihan formulir pendaftaran Masuk Kuota yang sudah diberi bukti (Menunggu Verifikasi). */
function tagihanMenunggu(bool $masukKuota = true): Tagihan
{
    $p = pendaftaranBertagihan(test()->pp, $masukKuota);
    $t = Tagihan::where('pendaftaran_id', $p->id)->firstOrFail();
    test()->actingAs($p->user)->post(route('tagihan.unggah', $t), ['berkas' => berkasPdf()] + dataPengiriman($t->nominal_total))->assertSessionHasNoErrors();

    return $t->fresh();
}

function putuskan(Tagihan $t, string $keputusan, array $tambahan = []): TestResponse
{
    return test()->actingAs(test()->keuangan)->post(route('admin.verifikasi-pembayaran.putuskan', $t),
        ['versi' => CekVersiData::versi($t), 'keputusan' => $keputusan] + $tambahan);
}

it('FR-29/BR-72: unggah bukti + data pengiriman → Menunggu Verifikasi; selisih nominal hanya peringatan', function () {
    $p = pendaftaranBertagihan($this->pp, false);
    $t = Tagihan::where('pendaftaran_id', $p->id)->firstOrFail();

    $this->actingAs($p->user)->post(route('tagihan.unggah', $t), ['berkas' => berkasJpg()] + dataPengiriman($t->nominal_total - 1))
        ->assertRedirect(route('tagihan.show', $t))->assertSessionHas('peringatan');

    $b = BuktiPembayaran::where('tagihan_id', $t->id)->firstOrFail();
    expect($t->fresh()->status->getValue())->toBe('menunggu_verifikasi')
        ->and($b->nominal_transfer)->toBe($t->nominal_total - 1)->and($b->bank_pengirim)->toBe('Bank BNI')->and($b->keputusan)->toBe('menunggu');
    Storage::disk('private')->assertExists($b->path);
});

it('Uji wajib BR-36/BR-37: unggah terkunci saat Menunggu Verifikasi; bukti ber-hash sama ditolak; data pengiriman wajib', function () {
    $t = tagihanMenunggu();
    $this->actingAs($t->user)->post(route('tagihan.unggah', $t), ['berkas' => berkasPdf()] + dataPengiriman($t->nominal_total))
        ->assertSessionHasErrors(['berkas' => 'Bukti hanya dapat diunggah untuk tagihan berstatus Belum Bayar atau Ditolak.']);

    $p2 = pendaftaranBertagihan($this->pp, false);
    $t2 = Tagihan::where('pendaftaran_id', $p2->id)->firstOrFail();
    $isi = "%PDF-1.4\n% bukti sama\n%%EOF\n";
    $this->actingAs($p2->user)->post(route('tagihan.unggah', $t2), ['berkas' => berkasUji('a.pdf', $isi)] + dataPengiriman($t2->nominal_total));
    $p3 = pendaftaranBertagihan($this->pp, false);
    $t3 = Tagihan::where('pendaftaran_id', $p3->id)->firstOrFail();
    $this->actingAs($p3->user)->post(route('tagihan.unggah', $t3), ['berkas' => berkasUji('b.pdf', $isi)] + dataPengiriman($t3->nominal_total))
        ->assertSessionHasErrors(['berkas' => 'Bukti ini sudah pernah digunakan. Unggah bukti transfer yang lain.']);

    $this->actingAs($p3->user)->post(route('tagihan.unggah', $t3), ['berkas' => berkasPdf(), 'tanggal_transfer' => now()->addDay()->toDateString()])
        ->assertSessionHasErrors(['tanggal_transfer', 'nominal_transfer', 'bank_pengirim', 'nama_pengirim']);
    expect($t3->fresh()->status->getValue())->toBe('belum_bayar');
});

it('Uji wajib BR-39/FR-33: Lunas pada formulir yang belum Masuk Kuota ditolak "Menunggu verifikasi berkas"', function () {
    $t = tagihanMenunggu(false);

    putuskan($t, 'lunas')->assertSessionHasErrors(['keputusan' => VerifikasiPembayaran::PESAN_GERBANG]);
    expect($t->fresh()->status->getValue())->toBe('menunggu_verifikasi');
    $this->actingAs($this->keuangan)->get(route('admin.verifikasi-pembayaran.show', $t))->assertOk()->assertSee('Menunggu verifikasi berkas');
});

it('Uji wajib FR-34/BR-41/A-72: formulir Lunas → NPD terbit atomik, Belum Aktif Semester 1, tagihan Semester 1; role tetap peserta, label Peserta Didik', function () {
    $t = tagihanMenunggu();
    $user = $t->user;
    $tahun = Format::wib(now())->year;

    putuskan($t, 'lunas')->assertSessionHasNoErrors()->assertRedirect(route('admin.verifikasi-pembayaran.index'));

    $pd = PesertaDidik::where('pendaftaran_id', $t->pendaftaran_id)->firstOrFail();
    $smt1 = Tagihan::where('peserta_didik_id', $pd->id)->where('semester_ke', 1)->firstOrFail();
    expect($t->fresh()->status->getValue())->toBe('lunas')->and($t->fresh()->lunas_pada)->not->toBeNull()
        ->and($pd->npd)->toBe("MC-TRPL-{$tahun}-0001")
        ->and($pd->status->getValue())->toBe('belum_aktif')->and($pd->semester_berjalan)->toBe(1)
        ->and($t->pendaftaran->fresh()->status->getValue())->toBe('npd_terbit')
        ->and($smt1->nominal_dasar)->toBe(6500000)->and($smt1->rekening_nomor)->toBe('009801000123304')
        ->and($user->fresh()->namaRole())->toBe('peserta')->and($user->fresh()->labelRole())->toBe('Peserta Didik')
        ->and(BuktiPembayaran::where('tagihan_id', $t->id)->value('keputusan'))->toBe('diterima')
        ->and(AuditLog::whereIn('aksi', ['verifikasi_pembayaran', 'terbitkan_npd'])->count())->toBe(2);
});

it('Uji wajib NFR-27: NPD unik — pelunasan kedua memakai nomor urut berikutnya', function () {
    $a = tagihanMenunggu();
    $b = tagihanMenunggu();
    putuskan($a, 'lunas');
    putuskan($b, 'lunas');

    expect(PesertaDidik::pluck('npd')->sort()->values()->map(fn ($n) => substr($n, -4))->all())->toBe(['0001', '0002']);
});

it('BR-38/A-64: Tolak wajib alasan; dengan alasan baku → Ditolak dan peserta dapat mengunggah ulang', function () {
    $t = tagihanMenunggu();

    putuskan($t, 'tolak')->assertSessionHasErrors('catatan');
    putuskan($t, 'tolak', ['alasan' => ['nominal_tidak_sesuai'], 'catatan' => 'transfer kurang'])->assertSessionHasNoErrors();

    expect($t->fresh()->status->getValue())->toBe('ditolak')
        ->and(BuktiPembayaran::where('tagihan_id', $t->id)->value('alasan'))->toBe('Nominal tidak sesuai. Catatan: transfer kurang');
    $this->actingAs($t->user)->post(route('tagihan.unggah', $t), ['berkas' => berkasPdf()] + dataPengiriman($t->nominal_total))->assertSessionHasNoErrors();
    expect($t->fresh()->status->getValue())->toBe('menunggu_verifikasi');
});

it('Uji wajib BR-61: keputusan dengan versi usang → 409; tidak ada perubahan', function () {
    $t = tagihanMenunggu();
    $versiLama = CekVersiData::versi($t);
    putuskan($t, 'tolak', ['alasan' => ['bukti_tidak_terbaca']]);

    $this->actingAs($this->keuangan)->postJson(route('admin.verifikasi-pembayaran.putuskan', $t), ['versi' => $versiLama, 'keputusan' => 'lunas'])
        ->assertStatus(409);
    expect($t->fresh()->status->getValue())->toBe('ditolak');
});

it('FR-35: tagihan Semester N Lunas → peserta Aktif Semester N', function () {
    $t = tagihanMenunggu();
    putuskan($t, 'lunas');
    $smt1 = Tagihan::where('jenis', 'semester')->firstOrFail();
    $this->actingAs($smt1->user)->post(route('tagihan.unggah', $smt1), ['berkas' => berkasPdf()] + dataPengiriman($smt1->nominal_total))->assertSessionHasNoErrors();

    putuskan($smt1->fresh(), 'lunas')->assertSessionHasNoErrors();
    expect(PesertaDidik::firstOrFail()->status->getValue())->toBe('aktif');
});

it('FR-31/BR-66: antrean per tab status, hanya Keuangan & Super Admin', function () {
    $t = tagihanMenunggu();

    $this->actingAs($this->keuangan)->get(route('admin.verifikasi-pembayaran.index'))->assertOk()->assertSee($t->kode_referensi)->assertSee('Verifikasi');
    $this->actingAs($this->keuangan)->get(route('admin.verifikasi-pembayaran.index', ['tab' => 'lunas']))->assertOk()->assertDontSee($t->kode_referensi);
    $this->actingAs(buatPengguna('admin_silau'))->get(route('admin.verifikasi-pembayaran.index'))->assertForbidden();
    $this->actingAs(buatPengguna('peserta'))->get(route('admin.verifikasi-pembayaran.show', $t))->assertForbidden();
});

it('FR-36/BR-40/A-74: koreksi Lunas hanya Super Admin dengan alasan → Menunggu Verifikasi + audit', function () {
    $t = tagihanMenunggu();
    putuskan($t, 'lunas');
    $sa = buatPengguna('super_admin');

    $this->actingAs($sa)->post(route('admin.tagihan.koreksi', $t), ['versi' => CekVersiData::versi($t->fresh()), 'alasan' => 'pendek'])
        ->assertSessionHasErrors('alasan');
    $this->actingAs($this->keuangan)->post(route('admin.tagihan.koreksi', $t), ['alasan' => 'Mutasi ternyata dari rekening lain'])->assertForbidden();
    $this->actingAs($sa)->post(route('admin.tagihan.koreksi', $t), ['versi' => CekVersiData::versi($t->fresh()), 'alasan' => 'Mutasi ternyata dari rekening lain'])
        ->assertSessionHasNoErrors();

    expect($t->fresh()->status->getValue())->toBe('menunggu_verifikasi')->and($t->fresh()->lunas_pada)->toBeNull()
        ->and(AuditLog::where('aksi', 'koreksi_lunas')->where('user_id', $sa->id)->value('alasan'))->toBe('Mutasi ternyata dari rekening lain');
    Notification::assertSentTo($t->user, Pemberitahuan::class, fn ($n) => $n->judul === 'Status pembayaran dikoreksi');
    Notification::assertSentTo($this->keuangan, Pemberitahuan::class, fn ($n) => $n->judul === 'Koreksi Lunas perlu diverifikasi ulang');

    // Defect: tagihan hasil koreksi dapat ditandai Lunas lagi (tombol aktif); NPD tidak diterbitkan dua kali.
    $this->actingAs($this->keuangan)->get(route('admin.verifikasi-pembayaran.show', $t))->assertOk()
        ->assertSee('verifikasi ulang setelah koreksi')->assertDontSee('Menunggu verifikasi berkas');
    putuskan($t->fresh(), 'lunas')->assertSessionHasNoErrors();
    expect($t->fresh()->status->getValue())->toBe('lunas')
        ->and(PesertaDidik::where('pendaftaran_id', $t->pendaftaran_id)->count())->toBe(1)
        ->and(Tagihan::where('pendaftaran_id', $t->pendaftaran_id)->where('jenis', 'semester')->count())->toBe(1);
});

it('Defect P05: Peserta Didik yang masih menjalani program tidak dapat membuat pendaftaran baru', function () {
    $t = tagihanMenunggu();
    putuskan($t, 'lunas');
    $user = $t->user;

    $this->actingAs($user)->get(route('pendaftaran.baru'))->assertRedirect(route('dasbor'));
    $this->actingAs($user)->post(route('pendaftaran.store'), ['peminatan_periode_id' => $this->pp->id])
        ->assertSessionHasErrors(['peminatan_periode_id' => PilihPeminatan::PESAN_PESERTA_DIDIK]);
});

it('FR-57/BR-35: tagihan semester tanpa periode akademik → tanpa jatuh tempo + SA diberi tahu; SA menetapkan massal', function () {
    $sa = buatPengguna('super_admin');
    $t = tagihanMenunggu();
    putuskan($t, 'lunas');
    $smt1 = Tagihan::where('jenis', 'semester')->firstOrFail();
    expect($smt1->jatuh_tempo)->toBeNull();
    Notification::assertSentTo($sa, Pemberitahuan::class, fn ($n) => $n->jenis === 'tagihan_tanpa_jatuh_tempo');

    $this->actingAs($sa)->get(route('admin.tagihan.tanpa-jatuh-tempo'))->assertOk()->assertSee($smt1->kode_referensi);
    $this->actingAs($sa)->post(route('admin.tagihan.tanpa-jatuh-tempo.simpan'), ['tagihan' => [$smt1->id], 'jatuh_tempo' => '2027-02-08', 'catatan' => 'Genap'])
        ->assertSessionHasNoErrors();
    expect($smt1->fresh()->jatuh_tempo->toDateString())->toBe('2027-02-08')->and($smt1->fresh()->jatuh_tempo_manual)->toBeTrue()
        ->and(AuditLog::where('aksi', 'tetapkan_jatuh_tempo')->count())->toBe(1);
});

it('BR-35: periode akademik berikutnya ada → jatuh tempo Semester 1 otomatis H-7', function () {
    DB::table('periode_akademiks')->insert(['nama' => 'Ganjil', 'tanggal_mulai' => now()->addMonths(2)->toDateString(), 'tanggal_selesai' => now()->addMonths(7)->toDateString(), 'created_at' => now(), 'updated_at' => now()]);
    putuskan(tagihanMenunggu(), 'lunas');

    expect(Tagihan::where('jenis', 'semester')->firstOrFail()->jatuh_tempo->toDateString())->toBe(now()->addMonths(2)->subDays(7)->toDateString());
});

it('FR-38: rekap menghitung per status & tunggakan; ekspor Excel dan PDF terunduh', function () {
    $t = tagihanMenunggu();
    putuskan($t, 'lunas');

    $this->actingAs($this->keuangan)->get(route('admin.rekap'))->assertOk()->assertSee(Format::rupiah($t->nominal_total))->assertSee('Rekayasa Perangkat Lunak');
    $this->actingAs($this->keuangan)->get(route('admin.rekap.excel'))->assertOk()->assertDownload();
    $this->actingAs($this->keuangan)->get(route('admin.rekap.pdf'))->assertOk()->assertHeader('content-type', 'application/pdf');
    expect(AuditLog::where('aksi', 'ekspor_rekap_keuangan')->count())->toBe(2);
});

it('NFR-07/NFR-12: bukti hanya lewat URL bertanda tangan; peserta lain 403', function () {
    $t = tagihanMenunggu();
    $b = BuktiPembayaran::where('tagihan_id', $t->id)->firstOrFail();
    $url = URL::temporarySignedRoute('tagihan.bukti', now()->addMinutes(15), ['tagihan' => $t, 'bukti' => $b->id]);

    $this->actingAs($t->user)->get($url)->assertOk();
    $this->actingAs(buatPengguna('peserta'))->get($url)->assertForbidden();
    $this->actingAs($t->user)->get(route('tagihan.bukti', [$t, $b->id]))->assertForbidden();
});
