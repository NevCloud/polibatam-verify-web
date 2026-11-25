<?php

use App\Actions\Pendaftaran\AjukanFormulir;
use App\Actions\Pendaftaran\PilihPeminatan;
use App\Events\FormulirDiajukan;
use App\Models\AuditLog;
use App\Models\Pendaftaran;
use App\Notifications\Pemberitahuan;
use App\Services\NikHasher;
use App\Support\Ui\Format;
use App\Support\Ui\TampilanPendaftaran;
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
    $this->peserta = buatPengguna('peserta');
});

it('AC-04.1/BR-68: simpan pilihan pertama membuat Draf dengan nomor PV-{TAHUN}-{6 digit} berurutan', function () {
    $tahun = Format::wib(now())->year;

    $this->actingAs($this->peserta)->post(route('pendaftaran.store'), ['peminatan_periode_id' => $this->pp->id])
        ->assertRedirect(route('pendaftaran.formulir', "PV-{$tahun}-000001"));

    $lain = buatPengguna('peserta');
    $this->actingAs($lain)->post(route('pendaftaran.store'), ['peminatan_periode_id' => $this->pp->id]);

    $p = Pendaftaran::where('user_id', $this->peserta->id)->firstOrFail();
    expect($p->status->getValue())->toBe('draf')
        ->and($p->nama_lengkap)->toBe($this->peserta->name)
        ->and(Pendaftaran::where('user_id', $lain->id)->value('nomor_pendaftaran'))->toBe("PV-{$tahun}-000002")
        ->and((int) DB::table('pengaturans')->where('kunci', "urut_nomor_pendaftaran_{$tahun}")->value('nilai'))->toBe(2)
        ->and(AuditLog::where('aksi', 'buat_draf_pendaftaran')->where('entitas_id', $p->id)->exists())->toBeTrue();
});

it('BR-68: penghitung tahun baru dibuat otomatis bila belum ada di pengaturans', function () {
    DB::table('pengaturans')->where('kunci', 'like', 'urut_nomor_pendaftaran_%')->delete();

    $this->actingAs($this->peserta)->post(route('pendaftaran.store'), ['peminatan_periode_id' => $this->pp->id]);

    expect(Pendaftaran::value('nomor_pendaftaran'))->toEndWith('-000001');
});

it('BR-09: selama Draf pilihan boleh diganti dan nomor tetap', function () {
    $lain = bukaPeriode('TRMM', [], ['periode_pendaftaran_id' => $this->pp->periode_pendaftaran_id]);
    $this->actingAs($this->peserta)->post(route('pendaftaran.store'), ['peminatan_periode_id' => $this->pp->id]);
    $nomor = Pendaftaran::value('nomor_pendaftaran');

    $this->actingAs($this->peserta)->post(route('pendaftaran.store'), ['peminatan_periode_id' => $lain->id]);

    expect(Pendaftaran::count())->toBe(1)
        ->and(Pendaftaran::value('nomor_pendaftaran'))->toBe($nomor)
        ->and(Pendaftaran::value('peminatan_periode_id'))->toBe($lain->id);
});

it('BR-07: peminatan tanpa tarif, nonaktif, atau periode tidak dibuka tidak dapat dipilih', function (Closure $siapkan) {
    $pp = $siapkan($this->pp);

    $this->actingAs($this->peserta)->post(route('pendaftaran.store'), ['peminatan_periode_id' => $pp->id])
        ->assertSessionHasErrors('peminatan_periode_id');
    expect(Pendaftaran::count())->toBe(0);
})->with([
    'periode ditutup' => [fn ($pp) => tap($pp, fn ($pp) => $pp->periode->update(['status' => 'ditutup']))],
    'periode belum mulai' => [fn ($pp) => tap($pp, fn ($pp) => $pp->periode->update(['tanggal_mulai' => now()->addDay(), 'tanggal_selesai' => now()->addMonth()]))],
    'peminatan nonaktif' => [fn ($pp) => tap($pp, fn ($pp) => $pp->peminatan->update(['is_active' => false]))],
    'tanpa tarif formulir' => [fn ($pp) => tap($pp, fn ($pp) => DB::table('tarifs')->where('peminatan_id', $pp->peminatan_id)->update(['tanggal_efektif' => now()->addYear()->toDateString()]))],
    'kuota dibatalkan' => [fn ($pp) => tap($pp, fn ($pp) => $pp->update(['status' => 'dibatalkan']))],
]);

it('AC-04.6/BR-75 (K-42): HW-14 tidak memuat informasi kuota apa pun; peminatan tetap dapat dipilih', function () {
    $this->pp->update(['kuota_min' => 25]);

    $this->actingAs($this->peserta)->get(route('pendaftaran.baru'))->assertOk()
        ->assertDontSee('Kuota peminatan')->assertDontSee('Daftar Tunggu')->assertDontSee('penuh')
        ->assertDontSee('kuota_')->assertDontSee('slot'); // termasuk data JSON untuk Alpine

    $this->actingAs($this->peserta)->post(route('pendaftaran.store'), ['peminatan_periode_id' => $this->pp->id])->assertSessionHasNoErrors();
    expect(Pendaftaran::count())->toBe(1);
});

it('BR-02: peserta yang emailnya belum terverifikasi diarahkan ke HW-06', function () {
    $belum = buatPengguna('peserta', ['email_verified_at' => null]);

    $this->actingAs($belum)->get(route('pendaftaran.baru'))->assertRedirect(route('verification.notice'));
    $this->actingAs($belum)->get(route('dasbor'))->assertRedirect(route('verification.notice'));
});

it('AC-04.7/BR-62: NIK disimpan terenkripsi + nik_hash HMAC; isian lain tersimpan walau belum lengkap', function () {
    $p = app(PilihPeminatan::class)($this->peserta, $this->pp->id);

    $this->actingAs($this->peserta)->post(route('pendaftaran.simpan', $p), ['nik' => '2171 0208 0707 0003', 'tempat_lahir' => 'Batam'])
        ->assertRedirect()->assertSessionHasNoErrors();

    $mentah = DB::table('pendaftarans')->find($p->id);
    expect($mentah->nik_terenkripsi)->not->toContain('2171020807070003')
        ->and($p->fresh()->nik_terenkripsi)->toBe('2171020807070003')
        ->and($mentah->nik_hash)->toBe(app(NikHasher::class)->hash('2171020807070003'))
        ->and($mentah->tempat_lahir)->toBe('Batam')
        ->and($mentah->status)->toBe('draf');
});

it('NFR-35: format NIK, nomor HP, dan tahun lulus divalidasi dengan pesan spesifik', function () {
    $p = app(PilihPeminatan::class)($this->peserta, $this->pp->id);

    $this->actingAs($this->peserta)->post(route('pendaftaran.simpan', $p), ['nik' => '12345', 'no_hp' => '12345', 'tahun_lulus' => '1800'])
        ->assertSessionHasErrors([
            'nik' => 'NIK harus 16 digit angka sesuai KTP.',
            'no_hp' => 'Nomor HP harus nomor seluler Indonesia, mis. 0812xxxxxxx atau +62812xxxxxxx.',
            'tahun_lulus',
        ]);
});

it('AC-04.3: ajukan ditolak bila kolom/dokumen wajib belum lengkap dan bagian terkait ditandai', function () {
    $p = app(PilihPeminatan::class)($this->peserta, $this->pp->id);

    $this->actingAs($this->peserta)->post(route('pendaftaran.ajukan', $p), ['nik' => '2171020807070003'])
        ->assertSessionHasErrors(['tempat_lahir', 'tahun_lulus', 'berkas.ktp', 'berkas.skhu_nilai'])
        ->assertSessionDoesntHaveErrors(['nik', 'berkas.bukti_pendaftaran_polibatam']);

    expect($p->fresh()->status->getValue())->toBe('draf');
});

it('AC-04.2/AC-05.3: formulir lengkap (tanpa dokumen opsional) diajukan → Diajukan + event + notifikasi + audit', function () {
    Event::fake([FormulirDiajukan::class]);
    $p = drafLengkap($this->peserta, $this->pp);

    $this->actingAs($this->peserta)->post(route('pendaftaran.ajukan', $p), dataDiriLengkap())
        ->assertRedirect(route('status'))->assertSessionHasNoErrors();

    $p->refresh();
    expect($p->status->getValue())->toBe('diajukan')
        ->and($p->waktu_pengajuan)->not->toBeNull()
        ->and((int) $p->durasi_semester_snapshot)->toBe($this->pp->peminatan->durasi_semester)
        ->and(AuditLog::where('aksi', 'ajukan_formulir')->where('entitas_id', $p->id)->exists())->toBeTrue();
    Event::assertDispatched(FormulirDiajukan::class, fn ($e) => $e->pendaftaran->is($p));
    Notification::assertSentTo($this->peserta, Pemberitahuan::class, fn ($n) => $n->judul === 'Formulir berhasil diajukan');
});

it('Uji wajib BR-07: formulir tidak dapat diajukan setelah periode ditutup', function () {
    $p = drafLengkap($this->peserta, $this->pp);
    $this->pp->periode->update(['tanggal_selesai' => now()->subMinute(), 'tanggal_mulai' => now()->subMonth()]);

    $this->actingAs($this->peserta)->post(route('pendaftaran.ajukan', $p), dataDiriLengkap())->assertSessionHasErrors('formulir');
    expect($p->fresh()->status->getValue())->toBe('draf');
});

it('AC-04.4/AC-04.5/BR-09: setelah Diajukan data & pilihan terkunci dan pendaftaran kedua di periode yang sama ditolak', function () {
    $p = drafLengkap($this->peserta, $this->pp);
    app(AjukanFormulir::class)($p);

    $this->actingAs($this->peserta)->post(route('pendaftaran.simpan', $p), ['tempat_lahir' => 'Jakarta'])->assertSessionHasErrors('formulir');
    $this->actingAs($this->peserta)->post(route('pendaftaran.store'), ['peminatan_periode_id' => $this->pp->id])->assertSessionHasErrors('peminatan_periode_id');
    $this->actingAs($this->peserta)->get(route('pendaftaran.baru'))->assertRedirect(route('pendaftaran.formulir', $p));

    expect($p->fresh()->tempat_lahir)->toBe('Batam')->and(Pendaftaran::count())->toBe(1);
});

it('BR-08: setelah pendaftaran berstatus akhir, akun boleh mendaftar lagi pada periode yang sama', function () {
    $p = drafLengkap($this->peserta, $this->pp);
    DB::table('pendaftarans')->where('id', $p->id)->update(['status' => 'dibatalkan']);

    $this->actingAs($this->peserta)->post(route('pendaftaran.store'), ['peminatan_periode_id' => $this->pp->id])->assertSessionHasNoErrors();
    expect(Pendaftaran::where('user_id', $this->peserta->id)->count())->toBe(2);
});

it('Uji wajib BR-64/NFR-12: Draf tidak dapat dibuka akun lain (403) maupun admin (403), dan tidak tampil di sisi admin', function () {
    $p = drafLengkap($this->peserta, $this->pp);
    $lain = buatPengguna('peserta');
    $admin = buatPengguna('super_admin');

    foreach ([$lain, $admin] as $akun) {
        $this->actingAs($akun)->get(route('pendaftaran.formulir', $p))->assertForbidden();
        $this->actingAs($akun)->get(route('pendaftaran.berkas', $p))->assertForbidden();
        $this->actingAs($akun)->post(route('pendaftaran.simpan', $p), ['tempat_lahir' => 'X'])->assertForbidden();
    }

    expect(Pendaftaran::untukAdmin()->count())->toBe(0)
        ->and(DB::table('v_data_pendaftaran')->count())->toBe(0);
});

it('FR-68/BR-73: kartu pendaftar hanya untuk pemilik setelah Diajukan; Draf dan akun lain 403', function () {
    $p = drafLengkap($this->peserta, $this->pp);
    $this->actingAs($this->peserta)->get(route('pendaftaran.kartu', $p))->assertForbidden();

    app(AjukanFormulir::class)($p);

    $this->actingAs(buatPengguna('peserta'))->get(route('pendaftaran.kartu', $p))->assertForbidden();
    $this->actingAs($this->peserta)->get(route('pendaftaran.kartu', $p))
        ->assertOk()->assertHeader('content-type', 'application/pdf')
        ->assertDownload("kartu-pendaftar-{$p->nomor_pendaftaran}.pdf");
});

it('A-63/BR-73: isi kartu = data terkini tanpa NIK', function () {
    $p = drafLengkap($this->peserta, $this->pp);
    app(AjukanFormulir::class)($p);
    TampilanPendaftaran::muat($p->refresh());

    $html = view('pdf.kartu-pendaftar', ['kartu' => TampilanPendaftaran::ringkasan($p), 'diajukan' => 'x', 'dicetak' => 'y'])->render();

    expect($html)->toContain($p->nomor_pendaftaran)->toContain('Atma Fauzilla')->toContain('Rekayasa Perangkat Lunak')->toContain('Diajukan')
        ->not->toContain('2171020807070003')->not->toContain('0003');
});
