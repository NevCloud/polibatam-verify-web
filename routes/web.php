<?php

use App\Http\Controllers\Web\Akun\ProfilController;
use App\Http\Controllers\Web\Auth\AktivasiController;
use App\Http\Controllers\Web\Auth\TantanganDuaFaktorController;
use App\Http\Controllers\Web\Peserta\BerkasController;
use App\Http\Controllers\Web\Peserta\DasborController;
use App\Http\Controllers\Web\Peserta\NotifikasiController;
use App\Http\Controllers\Web\Peserta\PendaftaranController;
use App\Http\Controllers\Web\Silau\VerifikasiBerkasController;
use App\Http\Controllers\Web\SuperAdmin\AkunController;
use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// Rute registrasi, login, logout, verifikasi email, dan reset password disediakan Fortify (config/fortify.php).

Route::get('/', function () {
    return view('welcome'); // STUB-FE(P08): HW-01 beranda
});

// HW-08 dan HW-11: admin yang sudah lolos password tetapi belum punya sesi (BR-59).
Route::middleware('guest')->group(function () {
    Route::get('/two-factor-challenge', [TantanganDuaFaktorController::class, 'create'])->name('two-factor.login');
    Route::post('/two-factor-challenge', [TantanganDuaFaktorController::class, 'store'])->name('two-factor.login.store');

    Route::get('/aktivasi/autentikator', [AktivasiController::class, 'autentikator'])->name('aktivasi.autentikator');
    Route::post('/aktivasi/autentikator', [AktivasiController::class, 'konfirmasi'])->name('aktivasi.autentikator.store');
    Route::get('/aktivasi/{token}', [AktivasiController::class, 'create'])->where('token', '[A-Fa-f0-9]{64}')->name('aktivasi.create');
    Route::post('/aktivasi', [AktivasiController::class, 'store'])->name('aktivasi.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/aktivasi/kode-pemulihan', [AktivasiController::class, 'kodePemulihan'])->name('aktivasi.kode-pemulihan');
    Route::post('/aktivasi/selesai', [AktivasiController::class, 'selesai'])->name('aktivasi.selesai');

    // HW-12: profil & keamanan akun (semua role)
    Route::get('/akun', [ProfilController::class, 'show'])->name('akun.profil');
    Route::put('/akun/profil', [ProfilController::class, 'perbaruiProfil'])->name('akun.profil.update');
    Route::put('/akun/password', [ProfilController::class, 'gantiPassword'])->name('akun.password.update');
    Route::post('/akun/kode-pemulihan', [ProfilController::class, 'kodePemulihan'])->name('akun.kode-pemulihan');
    Route::post('/akun/sesi/akhiri', [ProfilController::class, 'akhiriSesiLain'])->name('akun.sesi.akhiri');

    // Portal peserta (FR-07 deny-by-default: role dicek di setiap grup). BR-02: email wajib terverifikasi.
    Route::middleware(['role:peserta', 'verified'])->group(function () {
        Route::get('/dasbor', [DasborController::class, 'index'])->name('dasbor'); // HW-13
        Route::get('/status', [DasborController::class, 'status'])->name('status'); // HW-13 status & riwayat

        Route::get('/pendaftaran', [PendaftaranController::class, 'saya'])->name('pendaftaran.saya');
        Route::get('/pendaftaran/baru', [PendaftaranController::class, 'pilih'])->name('pendaftaran.baru'); // HW-14
        Route::post('/pendaftaran', [PendaftaranController::class, 'simpanPilihan'])->name('pendaftaran.store');

        Route::prefix('/pendaftaran/{pendaftaran}')->where(['pendaftaran' => 'PV-\d{4}-\d{6}'])->name('pendaftaran.')->group(function () {
            Route::get('/', [PendaftaranController::class, 'formulir'])->name('formulir'); // HW-15
            // POST (bukan PUT): tombol Ajukan memakai form yang sama lewat `formaction` (HW-15 Tinjau & Ajukan).
            Route::post('/', [PendaftaranController::class, 'simpan'])->name('simpan');
            Route::post('/ajukan', [PendaftaranController::class, 'ajukan'])->name('ajukan');
            Route::get('/kartu', [PendaftaranController::class, 'kartu'])->name('kartu');

            $jenis = implode('|', array_keys(Dokumen::JENIS));
            Route::get('/berkas', [BerkasController::class, 'index'])->name('berkas'); // HW-16
            Route::post('/berkas/{jenis}', [BerkasController::class, 'unggah'])->where('jenis', $jenis)->name('berkas.unggah');
            Route::delete('/berkas/{jenis}', [BerkasController::class, 'hapus'])->where('jenis', $jenis)->name('berkas.hapus');
            Route::get('/berkas/versi/{versi}', [BerkasController::class, 'lihat'])->middleware('signed')->whereNumber('versi')->name('berkas.lihat');
        });

        Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi'); // HW-20
        Route::post('/notifikasi/baca', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.baca-semua');
        Route::get('/notifikasi/{id}', [NotifikasiController::class, 'buka'])->whereUuid('id')->name('notifikasi.buka');
    });

    // Portal admin
    Route::middleware('role:'.implode('|', User::ROLE_ADMIN))->prefix('admin')->name('admin.')->group(function () {
        Route::view('/', 'stub.dasbor', ['paket' => 'P12', 'halaman' => 'HW-21'])->name('dasbor'); // STUB-FE(P12)

        // HW-25/26: verifikasi berkas (Admin SILAU & Super Admin; FR-18–21, FR-61)
        Route::middleware('role:admin_silau|super_admin')->prefix('verifikasi-berkas')->name('verifikasi-berkas.')->group(function () {
            Route::get('/', [VerifikasiBerkasController::class, 'index'])->name('index');
            Route::get('/{pendaftaran}', [VerifikasiBerkasController::class, 'show'])->where('pendaftaran', 'PV-\d{4}-\d{6}')->name('show');
            Route::post('/{pendaftaran}', [VerifikasiBerkasController::class, 'simpan'])->where('pendaftaran', 'PV-\d{4}-\d{6}')->name('simpan');
            Route::get('/{pendaftaran}/berkas/{versi}', [VerifikasiBerkasController::class, 'lihat'])->middleware('signed')
                ->where('pendaftaran', 'PV-\d{4}-\d{6}')->whereNumber('versi')->name('berkas');
        });

        // HW-44: akun & role admin (Super Admin saja)
        Route::middleware('role:super_admin')->group(function () {
            Route::get('/akun', [AkunController::class, 'index'])->name('akun.index');
            Route::post('/akun', [AkunController::class, 'store'])->name('akun.store');
            Route::put('/akun/{user}/role', [AkunController::class, 'ubahRole'])->name('akun.role');
            Route::put('/akun/{user}/status', [AkunController::class, 'ubahStatus'])->name('akun.status');
            Route::post('/akun/{user}/undangan', [AkunController::class, 'kirimUlangUndangan'])->name('akun.undangan');
        });
    });
});
