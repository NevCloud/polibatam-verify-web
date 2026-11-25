<?php

namespace App\Support\Ui;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use InvalidArgumentException;

/**
 * Konteks pengguna untuk layout portal (PRD §4.2 butir 3): nama, inisial, chip peran, menu sidebar.
 * Menu, ikon, eyebrow, dan kartu bantuan per peran mengikuti Figma "Layar/Sidebar" (Role=…).
 * Mode preview: dariFixture(). Mode nyata: dariPengguna(Auth::user()) dengan role Spatie (P02).
 */
final class PortalContext
{
    /** @var array<string, array{eyebrow: string, menu: list<array{0: string, 1: string}>, bantuan: array{judul: string, teks: string, tautan: string}}> */
    private const PERAN = [
        'Calon Peserta' => [
            'eyebrow' => 'PORTAL PESERTA',
            'menu' => [['Dashboard', 'home'], ['Formulir', 'file'], ['Status', 'filecheck'], ['Tagihan', 'card'], ['Refund', 'refund'], ['Notifikasi', 'bell'], ['Profil', 'user']],
            'bantuan' => ['judul' => 'Butuh bantuan?', 'teks' => 'Tim kami siap membantu proses pendaftaran Anda.', 'tautan' => 'Hubungi dukungan →'],
        ],
        'Admin SILAU' => [
            'eyebrow' => 'PORTAL ADMIN',
            'menu' => [['Dashboard', 'home'], ['Data Pendaftaran', 'file'], ['Verifikasi Berkas', 'filecheck'], ['Arsip Periode', 'archive'], ['Dasbor Kuota', 'gauge'], ['Notifikasi', 'bell'], ['Profil', 'user']],
            'bantuan' => ['judul' => 'Panduan admin', 'teks' => 'Keputusan verifikasi diambil per dokumen dan tercatat di audit log.', 'tautan' => 'Buka panduan →'],
        ],
        'Admin Keuangan' => [
            'eyebrow' => 'PORTAL KEUANGAN',
            'menu' => [['Dashboard', 'home'], ['Verifikasi Pembayaran', 'card'], ['Refund', 'refund'], ['Rekap & Ekspor', 'report'], ['Rekening Institusi', 'bank'], ['Data Pendaftaran', 'file'], ['Notifikasi', 'bell'], ['Profil', 'user']],
            'bantuan' => ['judul' => 'Panduan keuangan', 'teks' => 'Cocokkan bukti dengan mutasi rekening sebelum menandai Lunas.', 'tautan' => 'Buka panduan →'],
        ],
        'Admin Track' => [
            'eyebrow' => 'PORTAL ADMIN TRACK',
            'menu' => [['Dashboard', 'home'], ['Data Pendaftaran', 'file'], ['Track', 'layers'], ['Peminatan', 'tag'], ['Tarif Peminatan', 'coins'], ['Kuota per Periode', 'chart'], ['Dasbor Kuota', 'gauge'], ['Profil', 'user']],
            'bantuan' => ['judul' => 'Panduan admin track', 'teks' => 'Perubahan tarif dan kuota tercatat sebagai versi baru.', 'tautan' => 'Buka panduan →'],
        ],
        'Super Admin' => [
            'eyebrow' => 'PORTAL SUPER ADMIN',
            // Figma + Data Pendaftaran, Arsip Periode, Notifikasi, Profil (keputusan pemilik 25-11-2026, T-14; BR-67, FR-66).
            'menu' => [['Dashboard', 'home'], ['Data Pendaftaran', 'file'], ['Arsip Periode', 'archive'], ['Kelola User', 'users'], ['Periode', 'calendar'], ['Rollover', 'refresh'], ['Evaluasi Kuota', 'alert'], ['Tagihan', 'card'], ['Audit Log', 'history'], ['CMS Landing Page', 'layout'], ['Kriteria Checklist', 'listcheck'], ['Pengaturan Sistem', 'settings'], ['Notifikasi', 'bell'], ['Profil', 'user']],
            'bantuan' => ['judul' => 'Panduan super admin', 'teks' => 'Pantau peringatan sistem, kelola akun, dan periksa audit log.', 'tautan' => 'Buka panduan →'],
        ],
    ];

    /**
     * @param  list<array{label: string, ikon: string, href: string, aktif: bool}>  $menu
     * @param  array{judul: string, teks: string, tautan: string}  $bantuan
     */
    private function __construct(
        public readonly string $nama,
        public readonly string $inisial,
        public readonly string $peran,
        public readonly string $nadaPeran,
        public readonly string $eyebrow,
        public readonly string $subjudul,
        public readonly array $menu,
        public readonly array $bantuan,
    ) {}

    /**
     * @param  array{nama: string, peran: string, periode?: string}  $pengguna
     */
    public static function dariFixture(array $pengguna, ?string $menuAktif = null): self
    {
        $peran = $pengguna['peran'];
        // Peserta Didik memakai sidebar peserta yang sama; yang berbeda hanya chip (A-72: satu role `peserta`).
        $konfigurasi = self::PERAN[$peran === 'Peserta Didik' ? 'Calon Peserta' : $peran]
            ?? throw new InvalidArgumentException("Peran tidak resmi: [{$peran}]");

        $menu = array_map(fn (array $m) => [
            'label' => $m[0],
            'ikon' => $m[1],
            'href' => self::href($m[0], $peran),
            'aktif' => $m[0] === ($menuAktif ?? 'Dashboard'),
        ], $konfigurasi['menu']);

        return new self(
            nama: $pengguna['nama'],
            inisial: self::inisial($pengguna['nama']),
            peran: $peran,
            nadaPeran: $peran === 'Peserta Didik' ? StatusBadge::SUCCESS : StatusBadge::INFO,
            eyebrow: $konfigurasi['eyebrow'],
            subjudul: $pengguna['periode'] ?? 'Pendaftaran', // STUB-BE(P10): nama periode aktif
            menu: $menu,
            bantuan: $konfigurasi['bantuan'],
        );
    }

    /** Mode nyata: konteks dari akun yang login (nama + role Spatie; Peserta Didik bila NPD sudah terbit). */
    public static function dariPengguna(User $user, ?string $menuAktif = null): self
    {
        return self::dariFixture(['nama' => $user->name, 'peran' => $user->labelRole()], $menuAktif);
    }

    /** Rute menu yang sudah dibangun; menu paket lain tetap '#' sampai paketnya selesai. */
    private static function href(string $label, string $peran): string
    {
        $admin = ! in_array($peran, ['Calon Peserta', 'Peserta Didik'], true);
        $rute = match ($label) {
            'Dashboard' => $admin ? 'admin.dasbor' : 'dasbor',
            'Profil' => 'akun.profil',
            'Kelola User' => 'admin.akun.index',
            'Verifikasi Berkas' => 'admin.verifikasi-berkas.index',
            'Formulir' => 'pendaftaran.saya',
            'Status' => 'status',
            'Notifikasi' => $admin ? null : 'notifikasi', // STUB-FE(P12): notifikasi admin
            default => null, // STUB-FE(P03–P12): rute menu lain dibuat bersama halamannya
        };

        // Tes unit tanpa aplikasi (router belum terdaftar) memakai '#'.
        return $rute && app()->bound('router') && Route::has($rute) ? route($rute) : '#';
    }

    /** 2 huruf: awal kata pertama + awal kata terakhir ("Nabila Putri Ramadhani" → "NR"). */
    public static function inisial(string $nama): string
    {
        $kata = preg_split('/\s+/u', trim($nama)) ?: [];
        $awal = mb_substr($kata[0] ?? '', 0, 1);
        $akhir = count($kata) > 1 ? mb_substr(end($kata), 0, 1) : mb_substr($kata[0] ?? '', 1, 1);

        return mb_strtoupper($awal.$akhir);
    }
}
