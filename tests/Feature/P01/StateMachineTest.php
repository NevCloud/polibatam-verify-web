<?php

use App\Models\ArsipPeriode;
use App\Models\Dokumen;
use App\Models\Pendaftaran;
use App\Models\PesertaDidik;
use App\Models\Refund;
use App\Models\RekeningInstitusi;
use App\Models\Tagihan;
use App\Support\Ui\StatusBadge;

/*
 * NFR-28: setiap transisi sah dan tidak sah diuji. Tabel di bawah disalin langsung dari SRS §4.2–§4.7
 * (+ A-73 Dokumen, keputusan koreksi Lunas 25-11-2026), terpisah dari implementasi di app/States.
 */
$spesifikasi = [
    'Pendaftaran' => [Pendaftaran::class,
        // K-42: Daftar Tunggu & Tidak Tertampung dihapus (tanpa kuota maksimum).
        ['draf', 'diajukan', 'perlu_revisi', 'masuk_kuota', 'npd_terbit', 'dibatalkan'],
        ['draf>diajukan', 'draf>dibatalkan', 'diajukan>perlu_revisi', 'perlu_revisi>diajukan', 'diajukan>masuk_kuota',
            'masuk_kuota>npd_terbit', 'masuk_kuota>dibatalkan', 'diajukan>dibatalkan', 'perlu_revisi>dibatalkan', 'npd_terbit>dibatalkan']],
    'Dokumen' => [Dokumen::class,
        ['menunggu_verifikasi', 'valid', 'ditolak'],
        ['menunggu_verifikasi>valid', 'menunggu_verifikasi>ditolak', 'ditolak>menunggu_verifikasi']],
    'Tagihan' => [Tagihan::class,
        ['belum_bayar', 'menunggu_verifikasi', 'lunas', 'ditolak', 'dibatalkan'],
        ['belum_bayar>menunggu_verifikasi', 'ditolak>menunggu_verifikasi', 'menunggu_verifikasi>lunas', 'menunggu_verifikasi>ditolak',
            'belum_bayar>dibatalkan', 'ditolak>dibatalkan', 'menunggu_verifikasi>dibatalkan', 'lunas>menunggu_verifikasi']],
    'PesertaDidik' => [PesertaDidik::class,
        ['belum_aktif', 'aktif', 'tertahan', 'selesai_masa_program', 'dibatalkan'],
        ['belum_aktif>aktif', 'aktif>belum_aktif', 'aktif>selesai_masa_program', 'belum_aktif>tertahan', 'tertahan>aktif',
            'belum_aktif>dibatalkan', 'aktif>dibatalkan', 'tertahan>dibatalkan']],
    'Refund' => [Refund::class,
        ['menunggu_data_rekening', 'diproses', 'selesai', 'ditolak'],
        ['menunggu_data_rekening>diproses', 'diproses>selesai', 'menunggu_data_rekening>ditolak', 'diproses>ditolak']],
    'ArsipPeriode' => [ArsipPeriode::class,
        ['diproses', 'tersedia', 'gagal'],
        ['diproses>tersedia', 'diproses>gagal', 'gagal>diproses']],
    'RekeningInstitusi' => [RekeningInstitusi::class,
        ['nonaktif', 'aktif'],
        ['nonaktif>aktif', 'aktif>nonaktif']],
];

$kasus = [];
foreach ($spesifikasi as $entitas => [$model, $status, $sah]) {
    foreach ($status as $dari) {
        foreach ($status as $ke) {
            if ($dari !== $ke) {
                $boleh = in_array("{$dari}>{$ke}", $sah, true);
                $kasus["{$entitas}: {$dari} → {$ke} ".($boleh ? '(sah)' : '(ditolak)')] = [$model, $dari, $ke, $boleh];
            }
        }
    }
}

it('NFR-28: transisi status mengikuti tabel SRS §4', function (string $model, string $dari, string $ke, bool $boleh) {
    $m = (new $model)->setRawAttributes(['status' => $dari]);

    expect($m->status->getValue())->toBe($dari)
        ->and($m->status->canTransitionTo($ke))->toBe($boleh);
})->with($kasus);

it('NFR-28: status akhir tidak punya transisi keluar', function (string $model, string $status) {
    expect((new $model)->setRawAttributes(['status' => $status])->status->transitionableStates())->toBe([]);
})->with([
    'Pendaftaran dibatalkan' => [Pendaftaran::class, 'dibatalkan'],
    'Dokumen valid (terkunci, BR-14)' => [Dokumen::class, 'valid'],
    'Arsip tersedia (final, BR-69)' => [ArsipPeriode::class, 'tersedia'],
    'Refund selesai' => [Refund::class, 'selesai'],
    'Peserta selesai_masa_program' => [PesertaDidik::class, 'selesai_masa_program'],
]);

it('label status memakai nama resmi design-memory §6.2', function () {
    $p = (new Pendaftaran)->setRawAttributes(['status' => 'perlu_revisi']);
    $pd = (new PesertaDidik)->setRawAttributes(['status' => 'belum_aktif', 'semester_berjalan' => 2]);

    expect($p->labelStatus())->toBe('Perlu Revisi')
        ->and($pd->labelStatus())->toBe('Belum Aktif Semester 2')
        ->and((new Tagihan)->setRawAttributes(['status' => 'belum_bayar'])->status->label())->toBe('Belum Bayar')
        ->and(StatusBadge::nada($p->labelStatus()))->toBe('error');
});

it('status baru memakai nilai bawaan sesuai skema', function () {
    expect((new Pendaftaran)->status->getValue())->toBe('draf')
        ->and((new Tagihan)->status->getValue())->toBe('belum_bayar')
        ->and((new RekeningInstitusi)->status->getValue())->toBe('nonaktif');
});
