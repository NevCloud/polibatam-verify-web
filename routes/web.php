<?php

use App\Http\Controllers\Web\Akun\ProfilController;
use App\Http\Controllers\Web\Auth\AktivasiController;
use App\Http\Controllers\Web\Auth\TantanganDuaFaktorController;
use App\Http\Controllers\Web\Keuangan\RekapController;
use App\Http\Controllers\Web\Keuangan\RekeningController;
use App\Http\Controllers\Web\Keuangan\VerifikasiPembayaranController;
use App\Http\Controllers\Web\Peserta\BerkasController;
use App\Http\Controllers\Web\Peserta\DasborController;
use App\Http\Controllers\Web\Peserta\NotifikasiController;
use App\Http\Controllers\Web\Peserta\PendaftaranController;
use App\Http\Controllers\Web\Peserta\TagihanController as PesertaTagihanController;
use App\Http\Controllers\Web\Publik\PublikController;
use App\Http\Controllers\Web\Silau\VerifikasiBerkasController;
use App\Http\Controllers\Web\SuperAdmin\AkunController;
use App\Http\Controllers\Web\SuperAdmin\CmsController;
use App\Http\Controllers\Web\SuperAdmin\TagihanController as SuperAdminTagihanController;
use App\Http\Controllers\Web\Track\KuotaController;
use App\Http\Controllers\Web\Track\PeminatanController;
use App\Http\Controllers\Web\Track\TarifController;
use App\Http\Controllers\Web\Track\TrackController;
use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// Rute registrasi, login, logout, verifikasi email, dan reset password disediakan Fortify (config/fortify.php).

// HW-01–04: halaman publik tanpa login (FR-08, FR-09)
Route::get('/', [PublikController::class, 'beranda'])->name('beranda');
Route::get('/katalog', [PublikController::class, 'katalog'])->name('katalog');
Route::get('/katalog/{kode}', [PublikController::class, 'peminatan'])->where('kode', '[A-Za-z0-9-]+')->name('katalog.show');
Route::view('/kebijakan-privasi', 'publik.kebijakan-privasi')->name('kebijakan-privasi');

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

        // HW-17/18: tagihan & unggah bukti (FR-29, FR-37)
        Route::get('/tagihan', [PesertaTagihanController::class, 'index'])->name('tagihan.index');
        Route::get('/tagihan/{tagihan}', [PesertaTagihanController::class, 'show'])->where('tagihan', '[A-Z0-9-]+')->name('tagihan.show');
        Route::post('/tagihan/{tagihan}/bukti', [PesertaTagihanController::class, 'unggah'])->where('tagihan', '[A-Z0-9-]+')->name('tagihan.unggah');
        Route::get('/tagihan/{tagihan}/bukti/{bukti}', [PesertaTagihanController::class, 'bukti'])->middleware('signed')
            ->where('tagihan', '[A-Z0-9-]+')->whereNumber('bukti')->name('tagihan.bukti');
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

        // HW-28/29, HW-32, HW-56: keuangan (Admin Keuangan & Super Admin; FR-31–38, FR-67)
        Route::middleware('role:admin_keuangan|super_admin')->group(function () {
            Route::get('/verifikasi-pembayaran', [VerifikasiPembayaranController::class, 'index'])->name('verifikasi-pembayaran.index');
            Route::get('/verifikasi-pembayaran/{tagihan}', [VerifikasiPembayaranController::class, 'show'])->where('tagihan', '[A-Z0-9-]+')->name('verifikasi-pembayaran.show');
            Route::post('/verifikasi-pembayaran/{tagihan}', [VerifikasiPembayaranController::class, 'putuskan'])->where('tagihan', '[A-Z0-9-]+')->name('verifikasi-pembayaran.putuskan');
            Route::get('/verifikasi-pembayaran/{tagihan}/bukti/{bukti}', [VerifikasiPembayaranController::class, 'bukti'])->middleware('signed')
                ->where('tagihan', '[A-Z0-9-]+')->whereNumber('bukti')->name('verifikasi-pembayaran.bukti');

            Route::get('/rekap', [RekapController::class, 'index'])->name('rekap');
            Route::get('/rekap/excel', [RekapController::class, 'excel'])->name('rekap.excel');
            Route::get('/rekap/pdf', [RekapController::class, 'pdf'])->name('rekap.pdf');

            Route::get('/rekening', [RekeningController::class, 'index'])->name('rekening.index');
            Route::post('/rekening', [RekeningController::class, 'store'])->name('rekening.store');
            Route::put('/rekening/{rekening}', [RekeningController::class, 'update'])->whereNumber('rekening')->name('rekening.update');
            Route::put('/rekening/{rekening}/status', [RekeningController::class, 'status'])->whereNumber('rekening')->name('rekening.status');
        });

        // HW-42/43: tagihan tanpa jatuh tempo & koreksi (Super Admin; FR-36, FR-57)
        Route::middleware('role:super_admin')->prefix('tagihan')->name('tagihan.')->group(function () {
            Route::get('/', [SuperAdminTagihanController::class, 'index'])->name('index');
            Route::get('/tanpa-jatuh-tempo', [SuperAdminTagihanController::class, 'tanpaJatuhTempo'])->name('tanpa-jatuh-tempo');
            Route::post('/tanpa-jatuh-tempo', [SuperAdminTagihanController::class, 'tetapkanJatuhTempo'])->name('tanpa-jatuh-tempo.simpan');
            Route::get('/{tagihan}', [SuperAdminTagihanController::class, 'show'])->where('tagihan', '[A-Z0-9-]+')->name('show');
            Route::post('/{tagihan}/koreksi', [SuperAdminTagihanController::class, 'koreksi'])->where('tagihan', '[A-Z0-9-]+')->name('koreksi');
        });

        // HW-33–37: data master, tarif berversi, kuota minimum (P07; RBAC SRS: Track & peminatan, Tarif, Kuota, Dasbor kuota)
        Route::middleware('role:admin_track|super_admin')->group(function () {
            Route::get('/track', [TrackController::class, 'index'])->name('track.index');
            Route::post('/track', [TrackController::class, 'store'])->name('track.store');
            Route::put('/track/{track}', [TrackController::class, 'update'])->whereNumber('track')->name('track.update');
            Route::put('/track/{track}/status', [TrackController::class, 'status'])->whereNumber('track')->name('track.status');
            Route::delete('/track/{track}', [TrackController::class, 'destroy'])->whereNumber('track')->name('track.destroy');

            Route::get('/peminatan', [PeminatanController::class, 'index'])->name('peminatan.index');
            Route::post('/peminatan', [PeminatanController::class, 'store'])->name('peminatan.store');
            Route::put('/peminatan/{peminatan}', [PeminatanController::class, 'update'])->whereNumber('peminatan')->name('peminatan.update');
            Route::put('/peminatan/{peminatan}/status', [PeminatanController::class, 'status'])->whereNumber('peminatan')->name('peminatan.status');
            Route::delete('/peminatan/{peminatan}', [PeminatanController::class, 'destroy'])->whereNumber('peminatan')->name('peminatan.destroy');

            Route::post('/peminatan/{peminatan:kode}/tarif', [TarifController::class, 'store'])->where('peminatan', '[A-Z0-9-]+')->name('tarif.store');
            Route::post('/kuota', [KuotaController::class, 'simpan'])->name('kuota.simpan');
            Route::delete('/kuota/{peminatanPeriode}', [KuotaController::class, 'lepas'])->whereNumber('peminatanPeriode')->name('kuota.lepas');
        });
        Route::middleware('role:admin_track|admin_keuangan|super_admin')->group(function () {
            Route::get('/tarif', [TarifController::class, 'index'])->name('tarif.index');
            Route::get('/peminatan/{peminatan:kode}/tarif', [TarifController::class, 'show'])->where('peminatan', '[A-Z0-9-]+')->name('tarif.show');
        });
        Route::middleware('role:admin_track|admin_silau|super_admin')->group(function () {
            Route::get('/kuota', [KuotaController::class, 'index'])->name('kuota.index');
            Route::get('/kuota/dasbor', [KuotaController::class, 'dasbor'])->name('kuota.dasbor');
        });

        // HW-46: CMS landing page (Super Admin saja; FR-10, BR-55)
        Route::middleware('role:super_admin')->prefix('cms')->name('cms.')->group(function () {
            Route::get('/', [CmsController::class, 'index'])->name('index');
            Route::post('/', [CmsController::class, 'simpan'])->name('simpan');
            Route::post('/terbitkan', [CmsController::class, 'terbitkan'])->name('terbitkan');
            Route::get('/pratinjau', [CmsController::class, 'pratinjau'])->name('pratinjau');
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
