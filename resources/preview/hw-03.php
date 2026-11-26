<?php

// HW-03 · Detail peminatan (P08, K-42 tanpa kuota). Kontrak = Publik\PublikController::peminatan: `peminatan` (KatalogQuery::detail), `periode`, `tautanDaftar`.
$tarif = [['komponen' => 'Formulir pendaftaran', 'nominal' => 'Rp 350.000', 'sejak' => '1 Oktober 2026']];
foreach (range(1, 5) as $n) {
    $tarif[] = ['komponen' => "Semester {$n}", 'nominal' => $n === 5 ? null : 'Rp 6.500.000', 'sejak' => $n === 5 ? null : '1 Oktober 2026'];
}
$dasar = [
    'navAktif' => 'Program',
    'peminatan' => ['kode' => 'TRPL', 'nama' => 'Rekayasa Perangkat Lunak', 'track' => 'Teknik Informatika', 'durasi' => 5, 'tarif' => $tarif,
        'berkas' => ['KTP (wajib)', 'Ijazah / SKL / Rapor (wajib)', 'SKHU / Daftar Nilai (wajib)'], 'dibuka' => true],
    'periode' => ['buka' => true, 'nama' => 'Gelombang 2 · 2026', 'teks' => 'Pendaftaran dibuka 1 Oktober 2026 – 31 Januari 2027'],
    'tautanDaftar' => '#',
];

return [
    'view' => 'publik.peminatan',
    'peran' => null,
    'pengguna' => null,
    'default' => $dasar,
    'tidak_dibuka' => ['peminatan' => ['dibuka' => false] + $dasar['peminatan']] + $dasar,
    'ditutup' => ['periode' => ['buka' => false, 'nama' => null, 'teks' => 'Pendaftaran ditutup. Jadwal periode berikutnya belum diumumkan.']] + $dasar,
];
