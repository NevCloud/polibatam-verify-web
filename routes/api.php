<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UmumController;
use App\Http\Controllers\Api\V1\VerifikasiBerkasController;
use App\Http\Controllers\Api\V1\VerifikasiPembayaranController;
use App\Http\Middleware\BatasiPermintaanSensitif;
use Illuminate\Support\Facades\Route;

/*
| REST API v1 untuk aplikasi mobile (P06, FR-60, Arsitektur §6). Prefiks `api/v1`, nama rute `api.*`.
| Hanya Admin SILAU & Admin Keuangan (BR-60); tidak ada endpoint peserta maupun keputusan massal (BR-66).
*/

Route::name('api.')->group(function () {
    // NFR-08: 10 permintaan/menit/IP (kunci sama dengan rute sensitif web).
    Route::middleware(BatasiPermintaanSensitif::class.':paksa')->group(function () {
        Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login');
        Route::post('auth/two-factor', [AuthController::class, 'duaFaktor'])->name('auth.two-factor');
    });

    // Tautan berkas bertanda tangan ≤ 15 menit (NFR-07, NFR-12): dibuka langsung oleh penampil gambar/PDF di aplikasi.
    Route::get('berkas/{jenis}/{id}/unduh', [UmumController::class, 'unduh'])
        ->whereIn('jenis', ['dokumen', 'bukti'])->whereNumber('id')->middleware('signed')->name('berkas.unduh');

    Route::middleware(['auth:sanctum', 'role:admin_silau|admin_keuangan'])->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('me', [AuthController::class, 'me'])->name('me');
        Route::get('dasbor/ringkasan', [UmumController::class, 'ringkasan'])->name('dasbor.ringkasan');
        Route::get('notifikasi', [UmumController::class, 'notifikasi'])->name('notifikasi');
        Route::patch('notifikasi/{id}/dibaca', [UmumController::class, 'dibaca'])->name('notifikasi.dibaca');
        Route::get('berkas/{id}/tautan', [UmumController::class, 'tautan'])->whereNumber('id')->name('berkas.tautan');

        Route::middleware('role:admin_silau')->prefix('verifikasi-berkas')->name('verifikasi-berkas.')->group(function () {
            Route::get('/', [VerifikasiBerkasController::class, 'index'])->name('index');
            Route::get('{pendaftaran:id}', [VerifikasiBerkasController::class, 'show'])->whereNumber('pendaftaran')->name('show');
            Route::put('{pendaftaran:id}/dokumen/{dokumen}', [VerifikasiBerkasController::class, 'putuskan'])->whereNumber(['pendaftaran', 'dokumen'])->name('dokumen');
            Route::post('{pendaftaran:id}/selesai', [VerifikasiBerkasController::class, 'selesai'])->whereNumber('pendaftaran')->name('selesai');
        });

        Route::middleware('role:admin_keuangan')->prefix('verifikasi-pembayaran')->name('verifikasi-pembayaran.')->group(function () {
            Route::get('/', [VerifikasiPembayaranController::class, 'index'])->name('index');
            Route::get('{tagihan}', [VerifikasiPembayaranController::class, 'show'])->where('tagihan', '[A-Z0-9-]+')->name('show');
            Route::post('{tagihan}/lunas', [VerifikasiPembayaranController::class, 'lunas'])->where('tagihan', '[A-Z0-9-]+')->name('lunas');
            Route::post('{tagihan}/tolak', [VerifikasiPembayaranController::class, 'tolak'])->where('tagihan', '[A-Z0-9-]+')->name('tolak');
        });
    });
});
