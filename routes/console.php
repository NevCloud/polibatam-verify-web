<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

// Jadwal (CLAUDE.md "Scheduler Penting"); jam dalam WIB (NFR-30).
Schedule::command('audit:verifikasi-hash')->dailyAt('02:00')->timezone('Asia/Jakarta'); // NFR-31

// P04 (NFR-25). shortcut: kegagalan hanya ke log; alert tim teknis setelah 3 gagal berturut-turut dibuat di PN (monitoring).
Schedule::command('pendaftaran:batalkan-kedaluwarsa')->hourly()->withoutOverlapping()
    ->onFailure(fn () => Log::error('pendaftaran:batalkan-kedaluwarsa gagal')); // BR-21
Schedule::command('periode:tutup')->everyFifteenMinutes()->withoutOverlapping()
    ->onFailure(fn () => Log::error('periode:tutup gagal')); // BR-65, FR-25

// P05
Schedule::command('tagihan:cek-tanpa-jatuh-tempo')->dailyAt('07:00')->timezone('Asia/Jakarta')
    ->onFailure(fn () => Log::error('tagihan:cek-tanpa-jatuh-tempo gagal')); // FR-57
