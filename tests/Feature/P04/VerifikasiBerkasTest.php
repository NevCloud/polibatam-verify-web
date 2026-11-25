<?php

use App\Actions\Pendaftaran\AjukanFormulir;
use App\Actions\Pendaftaran\PilihPeminatan;
use App\Actions\Pendaftaran\VerifikasiDokumen;
use App\Exceptions\SudahDiverifikasi;
use App\Models\AuditLog;
use App\Models\Dokumen;
use App\Models\KriteriaChecklist;
use App\Models\Pendaftaran;
use App\Models\VerifikasiDokumen as Keputusan;
use App\Notifications\Pemberitahuan;
use App\Services\CekVersiData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Storage::fake('private');
    Notification::fake();
    $this->pp = bukaPeriode();
    $this->silau = buatPengguna('admin_silau');
    pasang2fa($this->silau);
});

/** Pendaftaran Diajukan milik peserta baru (3 dokumen wajib terunggah). */
function diajukan(string $nama = 'Atma Fauzilla'): Pendaftaran
{
    $p = drafLengkap(buatPengguna('peserta', ['name' => $nama]), test()->pp);
    app(AjukanFormulir::class)($p);

    return $p->fresh();
}

/** Isian form HW-26: $keputusan = [jenis => 'valid'|'ditolak'|[keputusan, kriteria yang dicentang, catatan]]. Bawaan: valid, semua kriteria. */
function isianVerifikasi(Pendaftaran $p, array $keputusan = []): array
{
    $isian = [];
    foreach (Dokumen::where('pendaftaran_id', $p->id)->where('status', 'menunggu_verifikasi')->whereNotNull('versi_aktif_id')->get() as $d) {
        $semua = KriteriaChecklist::aktifUntuk($d->jenis)->pluck('id')->all();
        [$k, $centang, $catatan] = array_pad((array) ($keputusan[$d->jenis] ?? 'valid'), 3, null);
        $isian[$d->id] = ['keputusan' => $k, 'kriteria' => $centang ?? $semua, 'catatan' => $catatan, 'versi' => CekVersiData::versi($d)];
    }

    return ['dokumen' => $isian];
}

it('AC-09.1/BR-64/BR-62: antrean hanya Diajukan, urut pengajuan terlama, tanpa Draf, NIK tersamar; filter jenis', function () {
    $lama = diajukan('Siti Aisyah');
    $this->travel(5)->minutes();
    $baru = diajukan('Andi Pratama');
    $draf = app(PilihPeminatan::class)(buatPengguna('peserta', ['name' => 'Rahasia Draf']), $this->pp->id);

    $this->actingAs($this->silau)->get(route('admin.verifikasi-berkas.index'))->assertOk()
        ->assertSeeInOrder([$lama->nomor_pendaftaran, $baru->nomor_pendaftaran])
        ->assertSee('2171 •••• •••• 0003')->assertDontSee('2171020807070003')
        ->assertDontSee($draf->nomor_pendaftaran)->assertDontSee('Rahasia Draf')
        ->assertSee('2 menunggu');

    $this->actingAs($this->silau)->get(route('admin.verifikasi-berkas.index', ['jenis' => 'revisi']))->assertOk()
        ->assertDontSee($lama->nomor_pendaftaran)->assertSee('Tidak ada berkas yang menunggu');
    $this->actingAs($this->silau)->get(route('admin.verifikasi-berkas.index', ['q' => '2171020807070003']))->assertOk()
        ->assertSee($lama->nomor_pendaftaran); // BR-62: cari NIK lewat hash
});

it('AC-09.4/FR-07: peserta dan Admin Keuangan ditolak 403; Super Admin boleh', function () {
    $p = diajukan();
    foreach (['peserta', 'admin_keuangan', 'admin_track'] as $role) {
        $this->actingAs(buatPengguna($role))->get(route('admin.verifikasi-berkas.index'))->assertForbidden();
        $this->actingAs(buatPengguna($role))->get(route('admin.verifikasi-berkas.show', $p))->assertForbidden();
    }
    $this->actingAs(buatPengguna('super_admin'))->get(route('admin.verifikasi-berkas.show', $p))->assertOk();
});

it('BR-64: Draf tidak dapat dibuka admin (404), termasuk berkasnya', function () {
    $draf = drafLengkap(buatPengguna('peserta'), $this->pp);
    $versi = Dokumen::where('pendaftaran_id', $draf->id)->firstOrFail()->versi_aktif_id;

    $this->actingAs($this->silau)->get(route('admin.verifikasi-berkas.show', $draf))->assertNotFound();
    $this->actingAs($this->silau)->post(route('admin.verifikasi-berkas.simpan', $draf), [])->assertNotFound();
    $url = URL::temporarySignedRoute('admin.verifikasi-berkas.berkas', now()->addMinutes(15), ['pendaftaran' => $draf, 'versi' => $versi]);
    $this->actingAs($this->silau)->get($url)->assertNotFound();
});

it('FR-19/NFR-07: HW-26 menampilkan checklist, biodata tersamar, info kuota minimum; berkas lewat URL bertanda tangan', function () {
    $p = diajukan();
    $versi = Dokumen::where('pendaftaran_id', $p->id)->where('jenis', 'ktp')->firstOrFail()->versi_aktif_id;

    $this->actingAs($this->silau)->get(route('admin.verifikasi-berkas.show', $p))->assertOk()
        ->assertSee('NIK sesuai dengan NIK di formulir')->assertSee('2171 •••• •••• 0003')->assertDontSee('2171020807070003')
        ->assertSee('Kuota minimum Rekayasa Perangkat Lunak: 1 · NPD Terbit: 0');

    $url = URL::temporarySignedRoute('admin.verifikasi-berkas.berkas', now()->addMinutes(15), ['pendaftaran' => $p, 'versi' => $versi]);
    $this->actingAs($this->silau)->get($url)->assertOk()->assertHeader('content-type', 'application/pdf');
    $this->actingAs($this->silau)->get(route('admin.verifikasi-berkas.berkas', [$p, $versi]))->assertForbidden(); // tanpa tanda tangan
});

it('AC-10.2/BR-17/AC-10.5: semua Valid → Masuk Kuota; keputusan, checklist, audit, notifikasi tercatat', function () {
    $p = diajukan();

    $this->actingAs($this->silau)->post(route('admin.verifikasi-berkas.simpan', $p), isianVerifikasi($p))
        ->assertRedirect(route('admin.verifikasi-berkas.index'))->assertSessionHas('status');

    $p->refresh();
    expect($p->status->getValue())->toBe('masuk_kuota')
        ->and($p->waktu_masuk_kuota)->not->toBeNull()
        ->and(Keputusan::where('keputusan', 'valid')->where('diverifikasi_oleh', $this->silau->id)->count())->toBe(3)
        ->and(Keputusan::first()->hasil_checklist[0])->toHaveKeys(['kriteria_id', 'teks', 'terpenuhi'])
        ->and(AuditLog::where('aksi', 'verifikasi_dokumen')->count())->toBe(3)
        ->and(AuditLog::where('aksi', 'selesaikan_verifikasi')->where('entitas_id', $p->id)->exists())->toBeTrue();
    Notification::assertSentTo($p->user, Pemberitahuan::class, fn ($n) => $n->judul === 'Berkas valid · Masuk Kuota');
});

it('Uji wajib A-64/AC-10.3: Ditolak tanpa alasan → 422 dan tidak ada keputusan yang tersimpan', function () {
    $p = diajukan();

    $this->actingAs($this->silau)->post(route('admin.verifikasi-berkas.simpan', $p), isianVerifikasi($p, ['skhu_nilai' => 'ditolak']))
        ->assertSessionHasErrors(); // semua kriteria dicentang + catatan kosong
    $d = Dokumen::where('pendaftaran_id', $p->id)->where('jenis', 'skhu_nilai')->firstOrFail();
    $this->actingAs($this->silau)->post(route('admin.verifikasi-berkas.simpan', $p), isianVerifikasi($p, ['skhu_nilai' => 'ditolak']))
        ->assertSessionHasErrors("dokumen.{$d->id}.catatan");

    $this->actingAs($this->silau)->postJson(route('admin.verifikasi-berkas.simpan', $p), isianVerifikasi($p, ['skhu_nilai' => 'ditolak']))
        ->assertUnprocessable();

    expect(Keputusan::count())->toBe(0)->and($p->fresh()->status->getValue())->toBe('diajukan')
        ->and(Dokumen::where('pendaftaran_id', $p->id)->where('status', 'menunggu_verifikasi')->count())->toBe(3);
});

it('AC-10.2: Valid ditolak bila ada kriteria tidak dicentang; keputusan kosong ditolak', function () {
    $p = diajukan();
    $ktp = Dokumen::where('pendaftaran_id', $p->id)->where('jenis', 'ktp')->firstOrFail();
    $ijazah = Dokumen::where('pendaftaran_id', $p->id)->where('jenis', 'ijazah_skl_rapor')->firstOrFail();
    $isian = isianVerifikasi($p, ['ktp' => ['valid', []]]);
    unset($isian['dokumen'][$ijazah->id]['keputusan']);

    $this->actingAs($this->silau)->post(route('admin.verifikasi-berkas.simpan', $p), $isian)
        ->assertSessionHasErrors(["dokumen.{$ktp->id}.keputusan", "dokumen.{$ijazah->id}.keputusan"]);
    expect(Keputusan::count())->toBe(0);
});

it('AC-10.4/A-64/FR-16: satu Ditolak (kriteria + catatan) → Perlu Revisi; alasan tampil di HW-16 peserta', function () {
    $p = diajukan();
    $kriteria = KriteriaChecklist::aktifUntuk('skhu_nilai')->get();

    $this->actingAs($this->silau)->post(route('admin.verifikasi-berkas.simpan', $p),
        isianVerifikasi($p, ['skhu_nilai' => ['ditolak', [$kriteria[1]->id], 'nilai semester 5 buram']]))->assertSessionHasNoErrors();

    $alasan = Keputusan::where('keputusan', 'ditolak')->value('alasan');
    expect($p->fresh()->status->getValue())->toBe('perlu_revisi')
        ->and($alasan)->toBe('Tidak terpenuhi: '.$kriteria[0]->teks.'. Catatan: nilai semester 5 buram');
    Notification::assertSentTo($p->user, Pemberitahuan::class, fn ($n) => $n->judul === 'Berkas perlu diperbaiki');

    $this->actingAs($p->user)->get(route('pendaftaran.berkas', $p))->assertOk()->assertSee('nilai semester 5 buram');
});

it('Revisi masuk: setelah unggah ulang, antrean menandai "Revisi masuk"; dokumen Valid terkunci; selesai → Masuk Kuota', function () {
    $p = diajukan();
    $this->actingAs($this->silau)->post(route('admin.verifikasi-berkas.simpan', $p), isianVerifikasi($p, ['skhu_nilai' => ['ditolak', [], null]]));
    $this->actingAs($p->user)->post(route('pendaftaran.berkas.unggah', [$p, 'skhu_nilai']), ['berkas' => berkasPdf('skhu_v2.pdf')]);

    $this->actingAs($this->silau)->get(route('admin.verifikasi-berkas.index', ['jenis' => 'revisi']))->assertSee($p->nomor_pendaftaran)->assertSee('Revisi masuk');

    $isian = isianVerifikasi($p);
    expect($isian['dokumen'])->toHaveCount(1); // hanya SKHU yang menunggu; KTP & Ijazah tetap Valid

    $this->actingAs($this->silau)->post(route('admin.verifikasi-berkas.simpan', $p), $isian)->assertSessionHasNoErrors();
    expect($p->fresh()->status->getValue())->toBe('masuk_kuota');
});

it('Uji wajib BR-61/FR-61: dua admin memutuskan pendaftaran yang sama → yang kedua 409, tidak ada perubahan, tercatat di audit', function () {
    $p = diajukan();
    $lain = buatPengguna('admin_silau');
    $isianLama = isianVerifikasi($p); // kedua admin membuka halaman pada versi yang sama

    $this->actingAs($lain)->post(route('admin.verifikasi-berkas.simpan', $p), isianVerifikasi($p, ['ktp' => ['ditolak', [], 'buram']]))->assertSessionHasNoErrors();

    $this->actingAs($this->silau)->from(route('admin.verifikasi-berkas.show', $p))
        ->post(route('admin.verifikasi-berkas.simpan', $p), $isianLama)
        ->assertRedirect(route('admin.verifikasi-berkas.show', $p))->assertSessionHas('kode_galat', SudahDiverifikasi::KODE);
    $this->actingAs($this->silau)->postJson(route('admin.verifikasi-berkas.simpan', $p), $isianLama)
        ->assertStatus(409)->assertJsonPath('code', SudahDiverifikasi::KODE);

    expect($p->fresh()->status->getValue())->toBe('perlu_revisi')
        ->and(Keputusan::where('diverifikasi_oleh', $this->silau->id)->count())->toBe(0)
        ->and(AuditLog::where('aksi', 'konflik_verifikasi')->where('user_id', $this->silau->id)->exists())->toBeTrue();
});

it('BR-61: versi dokumen usang ditolak 409 walau pendaftaran masih Diajukan (Action yang dipakai API mobile)', function () {
    $p = diajukan();
    $d = Dokumen::where('pendaftaran_id', $p->id)->where('jenis', 'ktp')->firstOrFail();
    $semua = KriteriaChecklist::aktifUntuk('ktp')->pluck('id')->all();

    expect(fn () => app(VerifikasiDokumen::class)($d, '2000-01-01 00:00:00.000000', 'valid', $semua, null, $this->silau))
        ->toThrow(SudahDiverifikasi::class);
    expect($d->fresh()->status->getValue())->toBe('menunggu_verifikasi');
});

it('AC-07.3: Masuk Kuota dan Perlu Revisi tampil di halaman peserta; tidak ada antrean admin untuk status lain', function () {
    $p = diajukan();
    $this->actingAs($this->silau)->post(route('admin.verifikasi-berkas.simpan', $p), isianVerifikasi($p));

    $this->actingAs($p->user)->get(route('status'))->assertSee('Berkas valid dan Anda Masuk Kuota')->assertSee('Verifikasi berkas selesai');
    $this->actingAs($this->silau)->get(route('admin.verifikasi-berkas.index'))->assertDontSee($p->nomor_pendaftaran);
    $this->actingAs($this->silau)->get(route('admin.verifikasi-berkas.show', $p))->assertOk()->assertSee('hanya untuk dilihat')->assertDontSee('Selesaikan verifikasi</button>', false);
});
