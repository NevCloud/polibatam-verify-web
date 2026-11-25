<?php

use App\Exceptions\SudahDiverifikasi;
use App\Exceptions\TransisiTidakSah;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $lempar = fn (Throwable $e) => fn () => throw $e;
    Route::post('/api/v1/_uji/konflik', $lempar(new SudahDiverifikasi));
    Route::post('/api/v1/_uji/transisi', $lempar(TransisiTidakSah::dari('Tagihan', 'lunas', 'ditolak')));
    Route::post('/api/v1/_uji/validasi', $lempar(ValidationException::withMessages(['alasan' => 'Alasan penolakan wajib diisi.'])));
    Route::get('/api/v1/_uji/terlarang', fn () => abort(403));
    Route::get('/api/v1/_uji/hilang', $lempar(new ModelNotFoundException));
    Route::get('/api/v1/_uji/meledak', $lempar(new RuntimeException('SQLSTATE rahasia pv_app')));
    Route::post('/_uji/web-konflik', $lempar(new SudahDiverifikasi))->middleware('web');
});

it('FR-61: API konflik verifikasi → 409 SUDAH_DIVERIFIKASI (format Arsitektur §6.2) + audit konflik_verifikasi', function () {
    $this->postJson('/api/v1/_uji/konflik')
        ->assertStatus(409)
        ->assertExactJson(['success' => false, 'code' => 'SUDAH_DIVERIFIKASI', 'message' => (new SudahDiverifikasi)->getMessage()]);

    expect(AuditLog::query()->where('aksi', 'konflik_verifikasi')->value('hasil'))->toBe('ditolak');
});

it('AE-04: transisi tidak sah → 409 TRANSISI_TIDAK_SAH', function () {
    $this->postJson('/api/v1/_uji/transisi')->assertStatus(409)->assertJsonPath('code', 'TRANSISI_TIDAK_SAH');
});

it('422 VALIDATION_ERROR membawa errors per field', function () {
    $this->postJson('/api/v1/_uji/validasi')
        ->assertStatus(422)
        ->assertJsonPath('code', 'VALIDATION_ERROR')
        ->assertJsonPath('message', 'Data yang dikirim tidak valid.')
        ->assertJsonPath('errors.alasan.0', 'Alasan penolakan wajib diisi.');
});

it('NFR-31: 403 → FORBIDDEN berbahasa Indonesia + audit akses_ditolak', function () {
    $this->getJson('/api/v1/_uji/terlarang')->assertStatus(403)
        ->assertJson(['success' => false, 'code' => 'FORBIDDEN', 'message' => 'Anda tidak berhak melakukan tindakan ini.']);

    $log = AuditLog::query()->where('aksi', 'akses_ditolak')->sole();
    expect($log->hasil)->toBe('ditolak')->and($log->kanal)->toBe('mobile')->and($log->nilai_sesudah['path'])->toBe('/api/v1/_uji/terlarang');
});

it('404 NOT_FOUND dan 500 tanpa detail teknis (NFR-35)', function () {
    $this->getJson('/api/v1/_uji/hilang')->assertStatus(404)->assertJsonPath('code', 'NOT_FOUND');
    $this->getJson('/api/v1/_uji/meledak')->assertStatus(500)->assertJsonPath('code', 'SERVER_ERROR')
        ->assertDontSee('SQLSTATE')->assertDontSee('pv_app');
});

it('FR-61 web: konflik pada form → kembali ke halaman dengan pesan galat', function () {
    $this->from('/halaman-verifikasi')->post('/_uji/web-konflik')
        ->assertRedirect('/halaman-verifikasi')
        ->assertSessionHas('kode_galat', 'SUDAH_DIVERIFIKASI');
});
