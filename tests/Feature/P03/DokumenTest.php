<?php

use App\Actions\Pendaftaran\AjukanFormulir;
use App\Actions\Pendaftaran\PilihPeminatan;
use App\Models\Dokumen;
use App\Models\DokumenVersi;
use App\Models\Pendaftaran;
use App\Support\Ui\TampilanPendaftaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Storage::fake('private');
    Notification::fake();
    $this->pp = bukaPeriode();
    $this->peserta = buatPengguna('peserta');
    $this->draf = app(PilihPeminatan::class)($this->peserta, $this->pp->id);
});

/** Ubah pendaftaran yang sudah diajukan menjadi Perlu Revisi: $ditolak = jenis yang ditolak, sisanya Valid. */
function jadikanPerluRevisi(Pendaftaran $p, array $ditolak): void
{
    $admin = buatPengguna('admin_silau');
    foreach ($p->dokumens()->get() as $d) {
        $tolak = in_array($d->jenis, $ditolak, true);
        DB::table('verifikasi_dokumens')->insert([
            'dokumen_versi_id' => $d->versi_aktif_id, 'keputusan' => $tolak ? 'ditolak' : 'valid', 'hasil_checklist' => '[]',
            'alasan' => $tolak ? 'Dokumen buram, nilai tidak terbaca.' : null, 'diverifikasi_oleh' => $admin->id, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('dokumens')->where('id', $d->id)->update(['status' => $tolak ? 'ditolak' : 'valid']);
    }
    DB::table('pendaftarans')->where('id', $p->id)->update(['status' => 'perlu_revisi']);
}

it('FR-14/NFR-07(b,d): PDF valid tersimpan di disk privat dengan nama acak + riwayat versi', function () {
    $this->actingAs($this->peserta)->post(route('pendaftaran.berkas.unggah', [$this->draf, 'ktp']), ['berkas' => berkasPdf('KTP_Atma.pdf')])
        ->assertRedirect()->assertSessionHasNoErrors();

    $d = Dokumen::where('pendaftaran_id', $this->draf->id)->where('jenis', 'ktp')->firstOrFail();
    $v = $d->versiAktif;
    expect($v->nama_asli)->toBe('KTP_Atma.pdf')
        ->and($v->mime)->toBe('application/pdf')
        ->and($v->path)->toStartWith("pendaftaran/{$this->draf->id}/")->not->toContain('KTP_Atma')
        ->and(strlen($v->sha256))->toBe(64)
        ->and($d->status->getValue())->toBe('menunggu_verifikasi');
    Storage::disk('private')->assertExists($v->path);
});

it('BR-12: JPG dan PNG asli diterima', function (UploadedFile $berkas) {
    $this->actingAs($this->peserta)->post(route('pendaftaran.berkas.unggah', [$this->draf, 'ktp']), ['berkas' => $berkas])->assertSessionHasNoErrors();
})->with([
    'jpg' => fn () => berkasJpg(),
    'png' => fn () => berkasUji('ktp.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==')),
]);

it('Uji wajib NFR-07(a)/AC-05.2: berkas berekstensi palsu ditolak berdasarkan isi (magic bytes)', function () {
    $palsu = berkasUji('ijazah.pdf', "MZ\x90\x00\x03\x00\x00\x00\x04\x00\x00\x00\xFF\xFF".str_repeat("\0", 128));

    $this->actingAs($this->peserta)->post(route('pendaftaran.berkas.unggah', [$this->draf, 'ijazah_skl_rapor']), ['berkas' => $palsu])
        ->assertSessionHasErrors(['berkas' => 'Format berkas harus PDF, JPG, atau PNG dengan ukuran maksimal 2 MB.']);

    expect(DokumenVersi::count())->toBe(0);
    expect(Storage::disk('private')->allFiles())->toBe([]);
});

it('Uji wajib BR-12: berkas > 2 MB ditolak; format lain (docx) ditolak', function (UploadedFile $berkas) {
    $this->actingAs($this->peserta)->post(route('pendaftaran.berkas.unggah', [$this->draf, 'ktp']), ['berkas' => $berkas])
        ->assertSessionHasErrors('berkas');
    expect(DokumenVersi::count())->toBe(0);
})->with([
    '3 MB' => fn () => UploadedFile::fake()->create('besar.pdf', 3000, 'application/pdf'),
    'docx' => fn () => berkasUji('ijazah.docx', "PK\x03\x04".str_repeat('x', 64)),
    'HTML bernama .jpg' => fn () => berkasUji('foto.jpg', '<html><script>alert(1)</script></html>'),
]);

it('NFR-16: unggah lewat XHR menerima JSON (200 / 422)', function () {
    $this->actingAs($this->peserta)->postJson(route('pendaftaran.berkas.unggah', [$this->draf, 'ktp']), ['berkas' => berkasPdf()])
        ->assertOk()->assertJson(['pesan' => 'KTP berhasil diunggah.']);
    $this->actingAs($this->peserta)->postJson(route('pendaftaran.berkas.unggah', [$this->draf, 'ktp']), ['berkas' => UploadedFile::fake()->create('a.pdf', 3000, 'application/pdf')])
        ->assertUnprocessable()->assertJsonValidationErrors('berkas');
});

it('AC-05.4/FR-17: ganti berkas saat Draf → versi aktif baru, versi lama tetap di riwayat; Hapus mengosongkan slot', function () {
    $this->actingAs($this->peserta)->post(route('pendaftaran.berkas.unggah', [$this->draf, 'ktp']), ['berkas' => berkasPdf('lama.pdf')]);
    $this->actingAs($this->peserta)->post(route('pendaftaran.berkas.unggah', [$this->draf, 'ktp']), ['berkas' => berkasJpg('baru.jpg')]);

    $d = Dokumen::where('jenis', 'ktp')->firstOrFail();
    expect($d->versis()->count())->toBe(2)->and($d->versiAktif->nama_asli)->toBe('baru.jpg');

    $this->actingAs($this->peserta)->delete(route('pendaftaran.berkas.hapus', [$this->draf, 'ktp']))->assertRedirect();
    expect($d->fresh()->versi_aktif_id)->toBeNull()->and($d->versis()->count())->toBe(2)
        ->and($this->draf->fresh()->load('dokumens')->kekurangan()['berkas'])->toHaveKey('ktp');
});

it('Setelah Diajukan berkas tidak dapat diunggah atau dihapus', function () {
    $p = drafLengkap(buatPengguna('peserta'), $this->pp);
    app(AjukanFormulir::class)($p);

    $this->actingAs($p->user)->post(route('pendaftaran.berkas.unggah', [$p, 'ktp']), ['berkas' => berkasPdf()])
        ->assertSessionHasErrors(['berkas' => 'Berkas tidak dapat diubah setelah formulir diajukan.']);
    $this->actingAs($p->user)->delete(route('pendaftaran.berkas.hapus', [$p, 'ktp']))->assertSessionHasErrors('formulir');
});

it('Uji wajib BR-14/AC-08.2/AC-08.3: saat Perlu Revisi hanya dokumen Ditolak yang dapat diunggah; lalu status kembali Diajukan', function () {
    $p = drafLengkap(buatPengguna('peserta'), $this->pp);
    app(AjukanFormulir::class)($p);
    $awal = $p->fresh()->waktu_pengajuan;
    jadikanPerluRevisi($p, ['skhu_nilai']);

    $this->actingAs($p->user)->post(route('pendaftaran.berkas.unggah', [$p, 'ktp']), ['berkas' => berkasPdf()])
        ->assertSessionHasErrors(['berkas' => 'Hanya dokumen berstatus Ditolak yang dapat diunggah ulang. Dokumen Valid terkunci.']);

    $this->actingAs($p->user)->post(route('pendaftaran.berkas.unggah', [$p, 'skhu_nilai']), ['berkas' => berkasPdf('skhu_v2.pdf')])
        ->assertSessionHasNoErrors();

    $p->refresh()->load('dokumens');
    expect($p->status->getValue())->toBe('diajukan')
        ->and($p->waktu_pengajuan->equalTo($awal))->toBeTrue() // FIFO BR-18 tidak berubah
        ->and($p->dokumens->firstWhere('jenis', 'skhu_nilai')->status->getValue())->toBe('menunggu_verifikasi')
        ->and($p->dokumens->firstWhere('jenis', 'ktp')->status->getValue())->toBe('valid');
});

it('FR-16: dua dokumen Ditolak → status tetap Perlu Revisi sampai keduanya diunggah ulang', function () {
    $p = drafLengkap(buatPengguna('peserta'), $this->pp);
    app(AjukanFormulir::class)($p);
    jadikanPerluRevisi($p, ['skhu_nilai', 'ktp']);

    $this->actingAs($p->user)->post(route('pendaftaran.berkas.unggah', [$p, 'ktp']), ['berkas' => berkasPdf()]);
    expect($p->fresh()->status->getValue())->toBe('perlu_revisi');

    $this->actingAs($p->user)->post(route('pendaftaran.berkas.unggah', [$p, 'skhu_nilai']), ['berkas' => berkasPdf()]);
    expect($p->fresh()->status->getValue())->toBe('diajukan');
});

it('AC-08.1/AC-08.4: HW-16 menampilkan alasan penolakan dan riwayat versi', function () {
    $p = drafLengkap(buatPengguna('peserta'), $this->pp);
    app(AjukanFormulir::class)($p);
    jadikanPerluRevisi($p, ['skhu_nilai']);
    $this->actingAs($p->user)->post(route('pendaftaran.berkas.unggah', [$p, 'skhu_nilai']), ['berkas' => berkasPdf('skhu_v2.pdf')]);
    jadikanPerluRevisi($p, ['skhu_nilai']);

    $this->actingAs($p->user)->get(route('pendaftaran.berkas', $p))->assertOk()
        ->assertSee('Dokumen buram, nilai tidak terbaca.')
        ->assertSee('skhu_v2.pdf')->assertSee('skhu_nilai.pdf')
        ->assertSee('Unggah ulang: SKHU / Daftar Nilai');
});

it('Uji wajib NFR-07(c)/NFR-12: berkas hanya lewat URL bertanda tangan ≤ 15 menit dan hanya pemilik', function () {
    $this->actingAs($this->peserta)->post(route('pendaftaran.berkas.unggah', [$this->draf, 'ktp']), ['berkas' => berkasPdf()]);
    $versi = DokumenVersi::firstOrFail();
    $url = URL::temporarySignedRoute('pendaftaran.berkas.lihat', now()->addMinutes(TampilanPendaftaran::MENIT_URL), ['pendaftaran' => $this->draf, 'versi' => $versi->id]);

    $this->actingAs($this->peserta)->get($url)->assertOk()->assertHeader('content-type', 'application/pdf');
    $this->actingAs($this->peserta)->get(route('pendaftaran.berkas.lihat', [$this->draf, $versi->id]))->assertForbidden(); // tanpa tanda tangan
    $this->actingAs(buatPengguna('peserta'))->get($url)->assertForbidden();

    $this->travel(16)->minutes();
    $this->actingAs($this->peserta)->get($url)->assertForbidden();
});

it('NFR-35: pratinjau berkas yang hilang dari disk dijawab 404, bukan 500', function () {
    $this->actingAs($this->peserta)->post(route('pendaftaran.berkas.unggah', [$this->draf, 'ktp']), ['berkas' => berkasPdf()]);
    $versi = DokumenVersi::firstOrFail();
    Storage::disk('private')->delete($versi->path);

    $url = URL::temporarySignedRoute('pendaftaran.berkas.lihat', now()->addMinutes(15), ['pendaftaran' => $this->draf, 'versi' => $versi->id]);
    $this->actingAs($this->peserta)->get($url)->assertNotFound();
});
