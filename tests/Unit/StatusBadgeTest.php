<?php

use App\Support\Ui\StatusBadge;

it('NFR-34: status resmi design-memory §6.2 dipetakan ke nada yang benar', function (string $status, string $nada) {
    expect(StatusBadge::nada($status))->toBe($nada);
})->with([
    ['Draf', 'neutral'],
    ['Diajukan', 'warning'],
    ['Perlu Revisi', 'error'],
    ['Masuk Kuota', 'success'],
    ['NPD Terbit', 'success'],
    ['Dibatalkan', 'error'],
    ['Menunggu Verifikasi', 'warning'],
    ['Valid', 'success'],
    ['Ditolak', 'error'],
    ['Belum Bayar', 'neutral'],
    ['Lunas', 'success'],
    ['Belum Aktif Semester 2', 'neutral'],
    ['Aktif Semester 1', 'success'],
    ['Tertahan (Tunggakan)', 'error'],
    ['Selesai Masa Program', 'success'],
    ['Menunggu Data Rekening', 'neutral'],
    ['Diproses', 'warning'],
    ['Selesai', 'success'],
    ['Belum terbit', 'neutral'],
]);

it('AE-15: istilah terlarang ditolak, bukan diberi warna bawaan', function (string $status) {
    StatusBadge::nada($status);
})->with(['Draft', 'Perlu Verifikasi', 'Diterima', 'Perlu perbaikan', 'Daftar Tunggu', 'Daftar Tunggu · #3', 'Tidak Tertampung']) // K-42
    ->throws(InvalidArgumentException::class);
