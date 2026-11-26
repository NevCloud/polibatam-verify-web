<?php

use App\Actions\Cms\KelolaKontenCms;
use App\Actions\Tagihan\SimpanVersiTarif;
use App\Models\AuditLog;
use App\Models\KontenCms;
use App\Models\Peminatan;
use App\Models\PeriodePendaftaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
});

it('FR-08/AC-01.1/01.4: katalog publik tanpa login menampilkan peminatan aktif, durasi & tarif berlaku; tanpa informasi kuota', function () {
    bukaPeriode(kuota: ['kuota_min' => 25]);

    $this->get(route('katalog'))->assertOk()
        ->assertSee('Rekayasa Perangkat Lunak')->assertSee('Rp 300.000')->assertSee('5 semester')
        ->assertDontSee('kuota', false)->assertDontSee('Kuota')->assertDontSee('Daftar Tunggu');
    $this->get(route('katalog.show', 'TRPL'))->assertOk()->assertSee('Formulir pendaftaran')->assertSee('Daftar peminatan ini')->assertDontSee('Kuota');
    $this->get(route('katalog', ['track' => 'MB']))->assertOk()->assertDontSee('Rekayasa Perangkat Lunak');
});

it('AC-01.2/NFR-15: tarif baru dengan tanggal efektif hari ini langsung tampil (tanpa cache)', function () {
    bukaPeriode();
    $this->get(route('katalog.show', 'TRPL'))->assertSee('Rp 300.000');

    app(SimpanVersiTarif::class)(Peminatan::where('kode', 'TRPL')->firstOrFail(), now()->toDateString(), ['formulir' => 375000], buatPengguna('admin_track'));
    $this->get(route('katalog.show', 'TRPL'))->assertSee('Rp 375.000')->assertDontSee('Rp 300.000');
});

it('Uji wajib AC-01.3: peminatan nonaktif (atau track nonaktif) tidak tampil; detailnya 404', function () {
    bukaPeriode();
    Peminatan::where('kode', 'TRPL')->update(['is_active' => false]);

    $this->get(route('katalog'))->assertOk()->assertDontSee('Rekayasa Perangkat Lunak');
    $this->get(route('katalog.show', 'TRPL'))->assertNotFound();

    DB::table('tracks')->where('kode', 'MB')->update(['is_active' => false]);
    $this->get(route('katalog'))->assertDontSee('Akuntansi');
});

it('FR-09/AC-01.5: tanpa periode dibuka tombol daftar nonaktif dan jadwal periode berikutnya tampil', function () {
    PeriodePendaftaran::create(['nama' => 'Gelombang Depan', 'tanggal_mulai' => now()->addMonth(), 'tanggal_selesai' => now()->addMonths(2)]);

    $this->get('/')->assertOk()->assertSee('Pendaftaran ditutup')->assertSee('Periode berikutnya (Gelombang Depan)')->assertDontSee('Daftar Sekarang');
    $this->get(route('katalog'))->assertSee('Pendaftaran sedang ditutup');
    $this->get(route('katalog.show', 'TRPL'))->assertSee('Pendaftaran ditutup')->assertDontSee('Daftar peminatan ini');
});

it('HW-01/HW-04: beranda menampilkan track dari data master & isi bawaan; kebijakan privasi dapat dibuka', function () {
    bukaPeriode();
    $this->get('/')->assertOk()->assertSee('Teknik Informatika')->assertSee('Daftar Sekarang')->assertSee('Pertanyaan yang sering ditanyakan.')->assertSee(route('register'), false);
    $this->get(route('kebijakan-privasi'))->assertOk()->assertSee('Pelindungan Data Pribadi');
});

it('FR-10/AC-29.1/29.2: Super Admin simpan draf (belum tampil) → pratinjau → terbitkan (tampil); tercatat di audit', function () {
    $sa = buatPengguna('super_admin');
    $this->actingAs($sa)->get(route('admin.cms.index'))->assertOk();

    $this->post(route('admin.cms.simpan'), ['bagian' => 'pengumuman', 'judul' => 'Info Penting', 'isi' => "Libur nasional 25 Desember.\n<script>alert(1)</script>"])
        ->assertSessionHasNoErrors();
    $this->get('/')->assertDontSee('Libur nasional 25 Desember.');
    $this->get(route('admin.cms.pratinjau'))->assertOk()->assertSee('Mode pratinjau CMS')->assertSee('Libur nasional 25 Desember.')
        ->assertDontSee('<script>alert(1)</script>', false); // teks polos ter-escape

    $this->post(route('admin.cms.terbitkan'), ['bagian' => 'pengumuman'])->assertSessionHasNoErrors();
    $this->get('/')->assertSee('Info Penting')->assertSee('Libur nasional 25 Desember.');
    expect(KontenCms::where('bagian', 'pengumuman')->pluck('status')->sort()->values()->all())->toBe(['draf', 'terbit'])
        ->and(AuditLog::whereIn('aksi', ['simpan_konten_cms', 'terbitkan_konten_cms'])->count())->toBe(2);
});

it('FR-10: terbitkan tanpa draf ditolak; bagian tak dikenal ditolak', function () {
    $sa = buatPengguna('super_admin');
    expect(fn () => app(KelolaKontenCms::class)->terbitkan('faq', $sa))->toThrow(ValidationException::class);
    $this->actingAs($sa)->post(route('admin.cms.simpan'), ['bagian' => 'tarif', 'judul' => 'X', 'isi' => 'Y'])->assertSessionHasErrors('bagian');
});

it('AC-29.3/NFR-04: CMS hanya Super Admin', function (string $role) {
    $this->actingAs(buatPengguna($role))->get(route('admin.cms.index'))->assertForbidden();
    $this->actingAs(buatPengguna($role))->post(route('admin.cms.terbitkan'), ['bagian' => 'faq'])->assertForbidden();
})->with(['admin_track', 'admin_silau', 'admin_keuangan', 'peserta']);

it('T-19/NFR-33: header publik punya menu ponsel; tamu melihat Masuk & Daftar', function () {
    $this->get(route('katalog'))->assertSee('aria-controls="menu-publik"', false)->assertSee(route('login'), false);
});
