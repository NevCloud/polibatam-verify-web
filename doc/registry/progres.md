# Registry Progres — Polibatam Verify

Dikelola Claude Code (PRD §4.5). Diperbarui setiap tugas selesai.

## Status Paket

Status: `Rencana` → `Dikerjakan` → `Menunggu Uji` → `Perbaikan` → `Disetujui`.

| Paket | Nama | Status | Catatan |
|---|---|---|---|
| P00 | Fondasi proyek & UI | Disetujui | Skema, seed, GRANT, Telescope lokal, halaman sistem & komponen FE-0 dari Figma. Sisa: 5 komponen tanpa sumber Figma (dibuat bersama halamannya) |
| P01 | Layanan inti | Disetujui | AuditLogger + hash chain, NikHasher, CekVersiData (409), 7 state machine, Notifier, penanganan galat, penyamaran log; tabel `rekening_institusis` (T-16) |
| P02 | Akun & akses | Disetujui | Registrasi, verifikasi email, login + kunci akun, 2FA TOTP wajib admin (tantangan + pendaftaran), reset password, aktivasi admin 3 langkah, profil & sesi, kelola akun admin (BR-05), sesi tidak aktif 30/60 menit, rate limit 10/menit. Dasbor HW-13/HW-21 masih stub (P03/P12) |
| P03 | Pendaftaran | Disetujui | Pilih peminatan (Draf + nomor PV-{TAHUN}-{6 digit}, FOR UPDATE), formulir 3 tab (NIK terenkripsi + hash), unggah berkas (magic bytes, ≤ 2 MB, disk privat, URL bertanda tangan 15 menit, progres), ajukan (BR-07/08/09, event FormulirDiajukan), revisi dokumen Ditolak, dasbor + status & riwayat, kartu pendaftar PDF (A-63), notifikasi in-app. Tagihan formulir terhubung di P05 |
| P04 | Verifikasi berkas & kuota | Disetujui | Antrean (urut pengajuan, filter, cari NIK via hash, "Revisi masuk"), detail + document viewer, keputusan per dokumen dengan checklist (A-64) dalam satu form, Selesaikan → Perlu Revisi / Masuk Kuota (K-42, tanpa kuota maksimum), 409 optimistic locking + dialog, `pendaftaran:batalkan-kedaluwarsa` & `periode:tutup` (idempoten, terjadwal), skema 01c (hapus kuota maks) |
| P05 | Tagihan & pembayaran | Disetujui | Tagihan formulir otomatis saat diajukan (kode referensi TG-{TAHUN}-{6 digit}, kode unik, snapshot tarif & rekening), unggah bukti + data pengiriman (hash unik, selisih = peringatan), verifikasi Lunas/Tolak (gerbang Masuk Kuota, alasan baku, 409), NPD atomik + tagihan Semester 1, koreksi Lunas (SA), tanpa jatuh tempo (HW-42 + perintah harian 07.00 WIB), rekening institusi, rekap + ekspor Excel/PDF (sinkron), skema 01d |
| P06 | REST API v1 | Menunggu Uji | 16 endpoint `/api/v1` (Arsitektur §6.4): login dua tahap (password → `challenge_token` → TOTP → token Sanctum per perangkat 12 jam), logout, `/me`, dasbor, notifikasi, tautan berkas bertanda tangan 15 menit, verifikasi berkas (antrean, detail, keputusan per dokumen, selesai) & pembayaran (antrean + `siap=1` Fast Verify, detail, lunas, tolak). Hanya Admin SILAU/Keuangan, role silang 403, 409 `SUDAH_DIVERIFIKASI`, 429 login, kanal audit `mobile`. Scribe → `postman/` (HTML, collection, OpenAPI) + koleksi Newman `postman/uji-p06.postman_collection.json` |
| P07 | Data master, tarif, kuota | Rencana |  |
| P08 | Publik & CMS | Rencana |  |
| P09 | Refund & pembatalan peminatan | Rencana |  |
| P10 | Periode & rollover | Rencana |  |
| P11 | Data pendaftaran, ekspor & arsip | Rencana |  |
| P12 | Notifikasi, audit, pengaturan & dasbor | Rencana |  |
| PM1–PM3 | Aplikasi mobile | Rencana |  |
| PN | Gerbang NFR lintas modul | Rencana |  |

## Halaman Web

FE: `Belum` → `Selesai`. Data: `Stub` → `Terhubung`.

| ID | Rute | View | Paket | FE | Data | Uji | Catatan |
|---|---|---|---|---|---|---|---|
| HW-01 | `/` | `publik/beranda` | P08 | Belum | — | — | Beranda |
| HW-02 | `/katalog` | `publik/katalog` | P08 | Belum | — | — | Katalog peminatan |
| HW-03 | `/katalog/{kode}` | `publik/peminatan` | P08 | Belum | — | — | Detail peminatan |
| HW-04 | `/kebijakan-privasi` | `publik/kebijakan-privasi` | P08 | Belum | — | — | Kebijakan privasi |
| HW-05 | `/register` | `auth/register` | P02 | Selesai | Terhubung | Disetujui | Registrasi (+ persetujuan kebijakan privasi dicatat bersama waktu) |
| HW-06 | `/email/verify` | `auth/verify-email` | P02 | Selesai | Terhubung | Disetujui | Verifikasi email |
| HW-07 | `/login` | `auth/login` | P02 | Selesai | Terhubung | Disetujui | Login (satu untuk semua role) |
| HW-08 | `/two-factor-challenge` | `auth/two-factor` | P02 | Selesai | Terhubung | Disetujui | Verifikasi 2FA |
| HW-09 | `/forgot-password` | `auth/forgot-password` | P02 | Selesai | Terhubung | Disetujui | Lupa password |
| HW-10 | `/reset-password/{token}` | `auth/reset-password` | P02 | Selesai | Terhubung | Disetujui | Reset password |
| HW-11 | `/aktivasi/{token}` | `auth/aktivasi` | P02 | Selesai | Terhubung | Disetujui | Aktivasi akun admin (password → QR TOTP → recovery code) |
| HW-12 | `/akun` | `akun/profil` | P02 | Selesai | Terhubung | Disetujui | Profil & keamanan akun |
| HW-13 | `/dasbor`, `/status` | `peserta/dasbor`, `peserta/status` | P03 | Selesai | Terhubung | Disetujui | Dasbor peserta (tracker 5 tahap, kartu status, ringkasan, kartu pendaftar) + halaman Status & riwayat (A-83). Kartu tagihan terhubung (P05) |
| HW-14 | `/pendaftaran/baru` | `peserta/pendaftaran/pilih-peminatan` | P03 | Selesai | Terhubung | Disetujui | Pilih peminatan (membuat Draf + nomor pendaftaran; ubah pilihan selama Draf) |
| HW-15 | `/pendaftaran/{nomor}` | `peserta/pendaftaran/formulir` | P03 | Selesai | Terhubung | Disetujui | Formulir pendaftaran (satu halaman, 3 tab: Data Diri · Berkas · Tinjau & Ajukan) |
| HW-16 | `/pendaftaran/{nomor}/berkas` | `peserta/pendaftaran/berkas` | P03 | Selesai | Terhubung | Disetujui | Detail berkas & revisi |
| HW-17 | `/tagihan` | `peserta/tagihan/index` | P05 | Selesai | Terhubung | Disetujui | Daftar tagihan peserta |
| HW-18 | `/tagihan/{kode_referensi}` | `peserta/tagihan/show` | P05 | Selesai | Terhubung | Disetujui | Detail tagihan & unggah bukti (+ data pengiriman wajib) |
| HW-19 | `/refund`, `/refund/{id}` | `peserta/refund/{index,show}` | P09 | Belum | — | — | Refund peserta (daftar + formulir rekening) |
| HW-20 | `/notifikasi` | `peserta/notifikasi` | P03 | Selesai | Terhubung | Disetujui | Notifikasi peserta (notifikasi in-app milik sendiri, tandai dibaca). Jenis notifikasi lain ditambah P05/P12 |
| HW-21 | `/admin` | `admin/bersama/dasbor` | P12 | Belum | — | — | Dasbor admin per role |
| HW-22 | `/admin/data-pendaftaran` | `admin/bersama/data-pendaftaran/index` | P11 | Belum | — | — | Data Pendaftaran (semua admin; ekspor hanya SILAU & Super Admin) |
| HW-23 | `/admin/data-pendaftaran/{nomor}` | `admin/bersama/data-pendaftaran/show` | P11 | Belum | — | — | Detail pendaftaran baca-saja (Draf → 404) |
| HW-24 | `/admin/notifikasi` | `admin/bersama/notifikasi` | P12 | Belum | — | — | Notifikasi admin |
| HW-25 | `/admin/verifikasi-berkas` | `admin/silau/verifikasi-berkas/index` | P04 | Selesai | Terhubung | Disetujui | Antrean verifikasi berkas |
| HW-26 | `/admin/verifikasi-berkas/{nomor}` | `admin/silau/verifikasi-berkas/show` | P04 | Selesai | Terhubung | Disetujui | Detail verifikasi berkas (document viewer, checklist, Valid/Ditolak + alasan, kuota minimum & NPD Terbit, Selesaikan, dialog 409) |
| HW-27 | `/admin/arsip-periode` | `admin/bersama/arsip-periode/index` | P11 | Belum | — | — | Arsip Periode (SILAU & Super Admin) |
| HW-28 | `/admin/verifikasi-pembayaran` | `admin/keuangan/verifikasi-pembayaran/index` | P05 | Selesai | Terhubung | Disetujui | Antrean verifikasi pembayaran |
| HW-29 | `/admin/verifikasi-pembayaran/{kode_referensi}` | `admin/keuangan/verifikasi-pembayaran/show` | P05 | Selesai | Terhubung | Disetujui | Detail verifikasi pembayaran (gerbang ganda, 409) |
| HW-30 | `/admin/refund` | `admin/keuangan/refund/index` | P09 | Belum | — | — | Daftar refund (Admin Keuangan) |
| HW-31 | `/admin/refund/{id}` | `admin/keuangan/refund/show` | P09 | Belum | — | — | Proses refund (pencocokan mutasi, rekening lengkap, bukti transfer) |
| HW-32 | `/admin/rekap` | `admin/keuangan/rekap/index` | P05 | Selesai | Terhubung | Disetujui | Rekap & ekspor keuangan |
| HW-33 | `/admin/track` | `admin/track/track/index` | P07 | Belum | — | — | Track |
| HW-34 | `/admin/peminatan` | `admin/track/peminatan/index` | P07 | Belum | — | — | Peminatan |
| HW-35 | `/admin/peminatan/{kode}/tarif` | `admin/track/peminatan/tarif` | P07 | Belum | — | — | Tarif peminatan (berversi) |
| HW-36 | `/admin/kuota` | `admin/track/kuota/index` | P07 | Belum | — | — | Kuota per periode |
| HW-37 | `/admin/kuota/dasbor` | `admin/track/kuota/dasbor` | P07 | Belum | — | — | Dasbor kuota |
| HW-38 | `/admin/periode-pendaftaran` | `admin/superadmin/periode-pendaftaran/index` | P10 | Belum | — | — | Periode pendaftaran |
| HW-39 | `/admin/periode-akademik` | `admin/superadmin/periode-akademik/index` | P10 | Belum | — | — | Periode akademik |
| HW-40 | `/admin/rollover` | `admin/superadmin/rollover/index` | P10 | Belum | — | — | Rollover semester (pratinjau → konfirmasi → status) |
| HW-41 | `/admin/evaluasi-kuota` | `admin/superadmin/evaluasi-kuota/index` | P09 | Belum | — | — | Evaluasi kuota & pembatalan peminatan |
| HW-42 | `/admin/tagihan/tanpa-jatuh-tempo` | `admin/superadmin/tagihan/tanpa-jatuh-tempo` | P05 | Selesai | Terhubung | Disetujui | Tagihan tanpa jatuh tempo |
| HW-43 | `/admin/tagihan`, `/admin/tagihan/{kode_referensi}` | `admin/superadmin/tagihan/{index,show}` | P05 | Selesai | Terhubung | Disetujui | Cari & koreksi tagihan |
| HW-44 | `/admin/akun` | `admin/superadmin/akun/index` | P02 | Selesai | Terhubung | Disetujui | Akun & role admin (Super Admin) |
| HW-45 | `/admin/audit-log` | `admin/superadmin/audit-log/index` | P12 | Belum | — | — | Audit log (hanya baca) |
| HW-46 | `/admin/cms` | `admin/superadmin/cms/index` | P08 | Belum | — | — | CMS landing page (Super Admin) |
| HW-47 | `/admin/pengaturan` | `admin/superadmin/pengaturan/index` | P12 | Belum | — | — | Pengaturan sistem (parameter NFR-40) |
| HW-48 | `/admin/kriteria-checklist` | `admin/superadmin/kriteria-checklist/index` | P12 | Belum | — | — | Kriteria checklist dokumen |
| HW-49 | — (halaman galat) | `errors/403` | P00 | Selesai | Terhubung | Menunggu | 403 Tidak berhak; preview `/_preview/hw-49` |
| HW-50 | — (halaman galat) | `errors/404` | P00 | Selesai | Terhubung | Menunggu | 404 Tidak ditemukan; preview `/_preview/hw-50` |
| HW-51 | — (halaman galat) | `errors/410` | P00 | Selesai | Terhubung | Menunggu | 410 Tautan kedaluwarsa; preview `/_preview/hw-51` |
| HW-52 | — (halaman galat) | `errors/419` | P00 | Selesai | Terhubung | Menunggu | 419 Sesi berakhir; preview `/_preview/hw-52` |
| HW-53 | — (halaman galat) | `errors/429` | P00 | Selesai | Terhubung | Menunggu | 429 Terlalu banyak permintaan; preview `/_preview/hw-53` |
| HW-54 | — (halaman galat) | `errors/500` | P00 | Selesai | Terhubung | Menunggu | 500 Kesalahan server (tanpa detail teknis, NFR-35); preview `/_preview/hw-54` |
| HW-55 | — (halaman galat) | `errors/503` | P00 | Selesai | Terhubung | Menunggu | Pemeliharaan (503); preview `/_preview/hw-55` |
| HW-56 | `/admin/rekening` | `admin/keuangan/rekening/index` | P05 | Selesai | Terhubung | Disetujui | Rekening institusi |

## Komponen FE-0 (`x-pv.*`)

Sumber: Figma "Microcredential - Website", bagian "10 · Komponen layar (web)" (25-11-2026). Galeri: `/_preview/komponen` (`?peran=Admin SILAU` dst.).

| Komponen | Figma | Status | Catatan |
|---|---|---|---|
| `button` (Primary/Secondary/Danger/Link) | PV/Button | Selesai | Danger gaya terang sesuai Figma |
| `badge`, `badge-status`, `chip` | PV/Badge, "Status" | Selesai | pemetaan status di `StatusBadge` |
| `alert` (Info/Warning/Error/Success) | PV/Alert | Selesai | |
| `field` (Input/Select/Textarea + galat) | PV/Field | Selesai | |
| `tab` (+ penanda kelengkapan) | PV/Tab | Selesai | varian Default [ASUMSI] |
| `step` (selesai/aktif/belum) | PV/Step | Selesai | state aktif/belum dari design-memory §7.5 |
| `stat-card` | PV/Stat Card | Selesai | |
| `page-header` | Layar/Judul halaman | Selesai | |
| `header-portal`, `header-publik`, `footer`, `logo` | Layar/Header portal, Layar/Header publik, Layar/Footer | Selesai | tinggi 72px (T-14) |
| `sidebar`, `sidebar-item`, `ikon` (24 ikon) | Layar/Sidebar (5 peran), Layar/Menu item, Ikon/* | Selesai | menu per peran dari Figma; Super Admin + Data Pendaftaran, Arsip Periode, Notifikasi, Profil (T-14) |
| `kartu-galat` | Layar/Kartu galat | Selesai | HW-49–55 |
| `empty-state` | — | Selesai | design-memory §6.3 |
| `table`, `dialog`, `upload-area`, `document-viewer`, `toast` | tidak ada sebagai komponen | Belum | dibuat bersama halaman pertama pemakainya (HW-22/25, HW-26, HW-15) |
| Layout `publik`, `auth`, `portal` | — | Selesai | `auth` = header publik + isi tengah + footer (Figma HW-49) |
