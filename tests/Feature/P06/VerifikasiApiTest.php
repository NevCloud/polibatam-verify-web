<?php

use App\Exceptions\SudahDiverifikasi;
use App\Exceptions\TransisiTidakSah;
use App\Models\AuditLog;
use App\Models\Dokumen;
use App\Models\KriteriaChecklist;
use App\Models\Pendaftaran;
use App\Models\PesertaDidik;
use App\Models\Tagihan;
use App\Models\VerifikasiDokumen;
use App\Notifications\Pemberitahuan;
use App\Services\CekVersiData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    Storage::fake('private');
    Notification::fake();
    $this->pp = bukaPeriode();
    rekeningAktif('formulir');
    rekeningAktif('semester', 'Bank Rakyat Indonesia (BRI)', '009801000123304');
    tarifSemester($this->pp->peminatan_id, 1, 6500000);
});

function semuaKriteria(Dokumen $d): array
{
    return KriteriaChecklist::aktifUntuk($d->jenis)->pluck('id')->all();
}

it('FR-18/BR-64: antrean berkas API hanya Diajukan (tanpa Draf), NIK tersamar; detail Draf 404', function () {
    $token = masukApi(buatPengguna('admin_silau'));
    $diajukan = pendaftaranBertagihan($this->pp, false);
    $draf = drafLengkap(buatPengguna('peserta'), $this->pp);

    $r = $this->getJson('/api/v1/verifikasi-berkas?per_page=5', bearer($token))->assertOk()
        ->assertJsonPath('meta.per_page', 5)->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.nomor_pendaftaran', $diajukan->nomor_pendaftaran);
    expect($r->json('data.0.nik'))->not->toContain('2171020807070003');

    $this->getJson("/api/v1/verifikasi-berkas/{$draf->id}", bearer($token))->assertNotFound()->assertJsonPath('code', 'NOT_FOUND');
    $this->getJson("/api/v1/verifikasi-berkas/{$diajukan->id}", bearer($token))->assertOk()
        ->assertJsonPath('data.bisa_diputuskan', true)->assertJsonCount(3, 'data.dokumen')
        ->assertJsonStructure(['data' => ['kuota' => ['minimum', 'npd_terbit'], 'dokumen' => [['id', 'versi', 'kriteria', 'berkas' => ['id']]]]]);
});

it('AC-30.3/BR-61: keputusan per dokumen + Selesaikan lewat API → Masuk Kuota, kanal mobile; 422 tanpa alasan; 409 versi usang', function () {
    $token = masukApi(buatPengguna('admin_silau'));
    $p = pendaftaranBertagihan($this->pp, false);
    $dokumen = Dokumen::where('pendaftaran_id', $p->id)->get();
    $url = fn (Dokumen $d) => "/api/v1/verifikasi-berkas/{$p->id}/dokumen/{$d->id}";

    $this->putJson($url($dokumen[0]), ['keputusan' => 'ditolak', 'versi' => CekVersiData::versi($dokumen[0]), 'kriteria' => semuaKriteria($dokumen[0])], bearer($token))
        ->assertUnprocessable()->assertJsonValidationErrors('catatan');
    $this->putJson($url($dokumen[0]), ['keputusan' => 'valid', 'versi' => '2000-01-01 00:00:00.000000', 'kriteria' => semuaKriteria($dokumen[0])], bearer($token))
        ->assertStatus(409)->assertJsonPath('code', SudahDiverifikasi::KODE);
    $this->postJson("/api/v1/verifikasi-berkas/{$p->id}/selesai", [], bearer($token))->assertUnprocessable()->assertJsonValidationErrors('selesai');

    foreach ($dokumen as $d) {
        $this->putJson($url($d), ['keputusan' => 'valid', 'versi' => CekVersiData::versi($d->fresh()), 'kriteria' => semuaKriteria($d)], bearer($token))
            ->assertOk()->assertJsonPath('data.status', 'valid');
    }
    $this->postJson("/api/v1/verifikasi-berkas/{$p->id}/selesai", [], bearer($token))->assertOk()->assertJsonPath('data.status', 'masuk_kuota');
    $this->postJson("/api/v1/verifikasi-berkas/{$p->id}/selesai", [], bearer($token))->assertStatus(409)->assertJsonPath('code', SudahDiverifikasi::KODE);

    expect(VerifikasiDokumen::whereIn('dokumen_versi_id', $dokumen->pluck('versi_aktif_id'))->pluck('kanal')->unique()->all())->toBe(['mobile'])
        ->and(AuditLog::where('aksi', 'selesaikan_verifikasi')->latest('id')->value('kanal'))->toBe('mobile');
});

it('NFR-07/NFR-12: tautan berkas bertanda tangan 15 menit; kedaluwarsa/diubah → 403; Keuangan memakai id bukti', function () {
    $silau = masukApi(buatPengguna('admin_silau'));
    $p = pendaftaranBertagihan($this->pp, false);
    $versiId = Dokumen::where('pendaftaran_id', $p->id)->value('versi_aktif_id');

    $url = $this->getJson("/api/v1/berkas/{$versiId}/tautan", bearer($silau))->assertOk()->json('data.url');
    $this->get($url)->assertOk()->assertHeader('Cache-Control', 'no-store, private');
    $this->get($url.'x')->assertForbidden();
    $this->travel(16)->minutes();
    $this->get($url)->assertForbidden();
    $this->travelBack();

    $draf = drafLengkap(buatPengguna('peserta'), $this->pp);
    $this->getJson('/api/v1/berkas/'.Dokumen::where('pendaftaran_id', $draf->id)->value('versi_aktif_id').'/tautan', bearer($silau))->assertNotFound();

    $t = tagihanSiapApi($this->pp);
    $keuangan = masukApi(buatPengguna('admin_keuangan'));
    $this->getJson('/api/v1/berkas/'.$t->buktiTerakhir->id.'/tautan', bearer($keuangan))->assertOk()->assertJsonPath('data.url', fn ($u) => str_contains($u, '/berkas/bukti/'));
});

/** Tagihan formulir dengan bukti terunggah; pendaftaran Masuk Kuota (lolos gerbang) kecuali $masukKuota = false. */
function tagihanSiapApi($pp, bool $masukKuota = true): Tagihan
{
    $p = pendaftaranBertagihan($pp, $masukKuota);
    $t = Tagihan::where('pendaftaran_id', $p->id)->firstOrFail();
    test()->actingAs($p->user)->post(route('tagihan.unggah', $t), ['berkas' => berkasPdf()] + dataPengiriman($t->nominal_total))->assertSessionHasNoErrors();

    return $t->fresh();
}

it('FR-31/BR-74: antrean pembayaran API; siap=1 hanya yang lolos gerbang ganda; detail berisi bukti & gerbang', function () {
    $token = masukApi(buatPengguna('admin_keuangan'));
    $siap = tagihanSiapApi($this->pp);
    $tertahan = tagihanSiapApi($this->pp, false);

    $this->getJson('/api/v1/verifikasi-pembayaran', bearer($token))->assertOk()->assertJsonPath('meta.total', 2);
    $this->getJson('/api/v1/verifikasi-pembayaran?siap=1', bearer($token))->assertOk()
        ->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.kode_referensi', $siap->kode_referensi)->assertJsonPath('data.0.nominal_total', $siap->nominal_total);
    $this->getJson("/api/v1/verifikasi-pembayaran/{$tertahan->kode_referensi}", bearer($token))->assertOk()
        ->assertJsonPath('data.gerbang.lolos', false)->assertJsonPath('data.bukti.0.selisih_nominal', false)
        ->assertJsonPath('data.bukti.0.nama_pengirim', 'Atma Fauzilla');
    $this->getJson('/api/v1/dasbor/ringkasan', bearer($token))->assertOk()
        ->assertJsonPath('data.antrean_pembayaran', 2)->assertJsonPath('data.siap_diputuskan', 1);
});

it('AC-30.3/BR-39/BR-61: Lunas lewat API → NPD terbit (kanal mobile); gerbang belum lolos 409; versi usang 409; Tolak tanpa alasan 422', function () {
    $token = masukApi(buatPengguna('admin_keuangan'));
    $tertahan = tagihanSiapApi($this->pp, false);
    $this->postJson("/api/v1/verifikasi-pembayaran/{$tertahan->kode_referensi}/lunas", ['versi' => CekVersiData::versi($tertahan)], bearer($token))
        ->assertStatus(409)->assertJsonPath('code', TransisiTidakSah::KODE);
    $this->postJson("/api/v1/verifikasi-pembayaran/{$tertahan->kode_referensi}/tolak", ['versi' => CekVersiData::versi($tertahan)], bearer($token))
        ->assertUnprocessable()->assertJsonValidationErrors('catatan');

    $t = tagihanSiapApi($this->pp);
    $this->postJson("/api/v1/verifikasi-pembayaran/{$t->kode_referensi}/lunas", ['versi' => '2000-01-01 00:00:00.000000'], bearer($token))
        ->assertStatus(409)->assertJsonPath('code', SudahDiverifikasi::KODE);
    $this->postJson("/api/v1/verifikasi-pembayaran/{$t->kode_referensi}/lunas", ['versi' => CekVersiData::versi($t)], bearer($token))
        ->assertOk()->assertJsonPath('data.status', 'lunas');

    expect(PesertaDidik::where('pendaftaran_id', $t->pendaftaran_id)->exists())->toBeTrue()
        ->and(Pendaftaran::find($t->pendaftaran_id)->status->getValue())->toBe('npd_terbit')
        ->and(AuditLog::where('aksi', 'verifikasi_pembayaran')->latest('id')->value('kanal'))->toBe('mobile');

    $this->postJson("/api/v1/verifikasi-pembayaran/{$tertahan->kode_referensi}/tolak",
        ['versi' => CekVersiData::versi($tertahan), 'alasan' => ['nominal_tidak_sesuai']], bearer($token))->assertOk()->assertJsonPath('data.status', 'ditolak');
    $this->getJson('/api/v1/verifikasi-pembayaran/TG-TIDAK-ADA', bearer($token))->assertNotFound();
});

it('FR-53/AC-30.6: notifikasi in-app milik admin sendiri; tandai dibaca', function () {
    $notif = fn (string $judul) => ['id' => (string) Str::uuid(), 'type' => Pemberitahuan::class, 'data' => ['jenis' => 'uji', 'judul' => $judul, 'isi' => 'Isi', 'url' => null]];
    $admin = buatPengguna('admin_keuangan');
    $token = masukApi($admin);
    $admin->notifications()->create($notif('Halo'));
    $lain = buatPengguna('admin_keuangan');
    $lain->notifications()->create($notif('Bukan milikmu'));

    $id = $this->getJson('/api/v1/notifikasi', bearer($token))->assertOk()->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.judul', 'Halo')->assertJsonPath('data.0.dibaca', false)->json('data.0.id');
    $this->patchJson("/api/v1/notifikasi/{$id}/dibaca", [], bearer($token))->assertOk()->assertJsonPath('data.dibaca', true);
    $this->patchJson('/api/v1/notifikasi/'.$lain->notifications()->value('id').'/dibaca', [], bearer($token))->assertNotFound();
    $this->getJson('/api/v1/dasbor/ringkasan', bearer($token))->assertJsonPath('data.notifikasi_belum_dibaca', 0);
});

it('BR-66: tidak ada endpoint keputusan massal', function () {
    $rute = collect(app('router')->getRoutes()->getRoutes())->map(fn ($r) => $r->uri())->filter(fn ($u) => str_starts_with($u, 'api/v1'));
    expect($rute->filter(fn ($u) => str_contains($u, 'massal') || str_contains($u, 'bulk'))->all())->toBe([])
        ->and($rute->contains('api/v1/verifikasi-berkas/{pendaftaran}/dokumen/{dokumen}'))->toBeTrue();
});
