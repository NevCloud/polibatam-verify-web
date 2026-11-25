# Design System & UI Skill Memory — Polibatam Verify (Full Unified)
 
## 0. Instruksi Eksekusi & Hemat Token (Wajib untuk Claude)
1. **Single Source of Truth:** Gunakan dokumen ini sebagai satu-satunya acuan utama desain. JANGAN memindai ulang file Figma yang sudah didokumentasikan di sini.
2. **Deteksi Platform Otomatis:** Periksa apakah permintaan pengguna ditujukan untuk **Mobile (`390px`)** atau **Desktop (`1440px`)**, lalu terapkan aturan pada Bagian 5 secara otomatis.
3. **Progressive Learning:** Jika pengguna memberikan *frame* atau komponen baru dan meminta "perbarui memori", tambahkan ringkasannya secara padat ke bagian yang relevan tanpa mengubah token inti yang sudah ada.
4. **Konsistensi Bahasa:** Gunakan Bahasa Indonesia formal-informatif untuk UI dan label aksi.
5. **Layar Referensi = Acuan Layout, Bukan Konten:** Tiga layar desktop pada Bagian 7 (Beranda, Dashboard Peserta, Data Pendaftaran Admin) adalah patokan **layout, komponen, dan gaya visual**. Isi alur, tahapan, status, peran, dan data diambil dari spesifikasi proyek (`claude/srs-fr-br.md`, `claude/user-stories.md`, pedoman), **bukan** dari teks yang tampil di layar referensi.
6. **Data Preview:** Selalu gunakan dataset dummy baku pada Bagian 9. Jangan menyalin data contoh dari layar referensi.
7. **Status & Peran:** Gunakan hanya nama status dan peran resmi pada Bagian 6. Jika SRS berubah, SRS yang menang.
---
 
## 1. Karakter Visual & Ikonografi
- **Vibe Utama:** *Clean Institutional SaaS*, terstruktur, modern, dan terpercaya (*high-trust academic portal*).
- **Perlakuan Permukaan:** Dominasi kanvas terang (`#F7F9FC`) dengan kartu putih (`#FFFFFF`) ber-border tipis (`1px solid #E2E8F0`). Hindari *drop shadow* berat; gunakan bayangan tipis hanya pada elemen melayang (*dropdown/modal*).
- **Ikonografi:** *Outline / Stroke icons* (gaya Lucide / Heroicons), *stroke-width* `1.5px–1.75px` dengan ujung membulat. Ikon kategori dibungkus kotak `32px × 32px` (`border-radius: 8px`, latar `#EFF6FF` / `#F1F5F9`, ikon `#1E376C`).
---
 
## 2. Color System (Polibatam Semantic Tokens)
### Brand & Interaksi
- *Aturan:* Navy untuk aksi utama; Cyan hanya sebagai aksen, **dilarang** dipakai untuk teks kecil di atas putih.
- `action/primary`: `#1E376C` (Tombol utama, menu aktif, ikon utama)
- `accent/brand`: `#54C8E8` (Aksen logo, elemen dekoratif)
- `surface/interactive`: `#EFF6FF` (Kartu interaktif / hover lembut)
- `navigation/active`: `#EDF3FC` (Latar navigasi aktif ringan)
### Netral & Permukaan
- `text/primary`: `#172B4D` (Judul & teks utama)
- `text/secondary`: `#64748B` (Subjudul, deskripsi, label pendukung)
- `background/canvas`: `#F7F9FC` (Latar belakang utama aplikasi/halaman)
- `surface/default`: `#FFFFFF` (Permukaan kartu, tabel, sidebar, modal)
- `border/default`: `#E2E8F0` (Garis pemisah kartu & baris tabel)
- `border/control`: `#94A3B8` (Batas input form & kontrol)
- `border/strong`: `#B8C9DD` (Batas komponen yang butuh penekanan)
- `border/accent`: `#D4E2F5` (Batas kartu aksen)
- `surface/document`: `#E4F3FA` (Latar miniatur dokumen/pratinjau berkas)
### Status & Feedback (Wajib Sertakan Label Teks)
- **Success:** Teks `status/success/text` `#15803D` | Latar `status/success/surface` `#EAFBEF`
- **Warning (menunggu):** Teks `status/warning/text` `#92400E` | Latar `status/warning/surface` `#FFF5E5`
- **Error (ditolak / perlu tindakan / berakhir gagal):** Teks `status/error/text` `#DC2626` | Latar `status/error/surface` `#FEF2F2` | Batas `status/error/border` `#FECACA`
- **Netral / belum dimulai** `[OBSERVASI — nilai hex perlu dikonfirmasi di Figma]`: teks `text/secondary` `#64748B` | latar `#F1F5F9`.
- **Informasi / penanda** `[OBSERVASI]`: teks `action/primary` atau biru tautan | latar `surface/interactive` `#EFF6FF`. Dipakai untuk chip `Gelombang 2 · 2026`, jumlah data, `Panduan proses`, chip peran admin.
- Pemetaan status resmi ke warna: lihat Bagian 6.2.
---
 
## 3. Typography System (`Plus Jakarta Sans` — Line-Height 150%)
- **Weights:** Regular (`400`), Medium (`500`), SemiBold (`600`), Bold (`700`).
- **Display / Angka Utama:** `28px` / LH `42px` / Bold (KPI & angka ringkasan, misal: `128`)
- **Heading:** `24px` / LH `36px` / Bold (Judul halaman utama)
- **Greeting:** `22px` / LH `33px` / Bold (Sapaan dashboard)
- **Title:** `20px` / LH `30px` / SemiBold (Judul dialog / bottom sheet)
- **Subheading:** `18px` / LH `27px` / SemiBold (Identitas pendaftar / judul sub-bagian)
- **Body:** `16px` / LH `24px` / Regular (Teks umum desktop)
- **Body Small:** `14px` / LH `21px` / Regular (Input & konten utama mobile)
- **Label:** `14px` / LH `21px` / Medium (Label formulir)
- **Button:** `14px` / LH `21px` / SemiBold (Label tombol aksi)
- **Label Compact:** `13px` / LH `19.5px` / SemiBold (Informasi antrean / tabel)
- **Caption:** `12px` / LH `18px` / Regular (Metadata pendukung)
- **Meta:** `11px` / LH `16.5px` / Medium (Keterangan kartu ringkasan)
- *Pengecualian:* Ukuran `7–10px` hanya dipakai di dalam miniatur dokumen.
- **Hero Landing** `[OBSERVASI]`: judul hero landing tampil jauh lebih besar dari `Heading` (±48px Bold, 2 baris, diakhiri titik). Judul section landing ±26–36px Bold, juga sering diakhiri titik. Hanya untuk landing; jangan dipakai di portal.
- **Eyebrow / Overline:** label kecil UPPERCASE SemiBold berwarna biru (`TRACK & PEMINATAN`, `ALUR PENDAFTARAN`, `PORTAL PESERTA`) di atas judul section/sidebar. Seluruh teks eyebrow wajib UPPERCASE penuh (contoh: `SISTEM VERIFIKASI MICROCREDENTIAL`).
---
 
## 4. Spacing, Radius & Dimensi Kontrol (Basis 4px)
- **Skala Spacing:**
  - `space/4` (`4px`): Jarak ikon dan label kecil
  - `space/8` (`8px`): Jarak elemen terkait
  - `space/12` (`12px`): Padding kartu mobile / gutter ringkasan
  - `space/16` (`16px`): Padding konten komponen
  - `space/20` (`20px`): Margin kiri & kanan mobile / padding kartu desktop
  - `space/24` (`24px`): Ekstensi antarbagian
  - `space/32` (`32px`): Ekstensi kelompok besar / padding kanvas desktop
  - `space/40` (`40px`): Ekstensi section
  - `space/48` (`48px`): Ekstensi area dokumentasi
  - `space/64` (`64px`): Ekstensi pemisah utama
  - *Pengecualian:* Nilai `10px` dipertahankan sesuai desain sumber.
- **Border Radius:**
  - Badge: `6px` | Tombol & Input: `8px` | Kartu & Dialog: `12px` | Bottom Sheet: `16px`
  - *Pill* `[OBSERVASI]`: chip informasi (Gelombang, jumlah data, chip peran) tampil membulat penuh.
- **Tinggi Standar Komponen:**
  - Tombol & Kolom Pencarian: `48px`
  - Bottom Navigation (Mobile): `76px`
  - Field Berlabel: `77px`
  - Kartu Identitas: `76px`
---
 
## 5. Responsive & Platform Rules (Mobile vs Desktop)
 
### A. Mobile (`390px` Viewport)
- **Grid & Margin:** 4 kolom, margin kiri/kanan `20px`, gutter `12px` (area konten bersih `350px`).
- **Layout 2 Kartu Sejajar:** Lebar masing-masing kartu `169px` dengan gutter `12px`.
- **Navigasi:** Top App Bar ringkas + Fixed Bottom Navigation (`height: 76px`).
- **Overlay & Modal:** Gunakan **Bottom Sheet** (`border-radius: 16px` di sudut atas) dengan *drag handle* di tengah atas.
- Aplikasi mobile hanya untuk Admin SILAU dan Admin Keuangan (dasbor, antrean & keputusan verifikasi, notifikasi in-app) — BR-60.
### B. Desktop (`1440px` Viewport)
- **Grid & Margin:**
  - *Portal Peserta & Admin:* Top Header penuh (`height: 72px`) + Fixed Left Sidebar (`width: 240px–260px`) + Fluid Main Content (padding `24px–32px`, gutter `16px–24px`).
  - *Landing Page:* Top Navbar penuh (`height: 72px`) + Kontainer terpusat (`max-width: 1200px`), jarak antar-section `64px–80px`.
- **Tinggi Header (KEPUTUSAN FINAL): `72px`** untuk landing dan portal. Alasan: pas dengan tombol `48px` + padding vertikal `12px` (grid 4px), menghemat ruang vertikal untuk tabel admin. Isi header (avatar `40px`, nama + chip peran bertumpuk) harus muat dalam 72px.
- **Navigasi Sidebar:** Item aktif menggunakan fill penuh `#1E376C` dengan teks putih (`border-radius: 8px`), dilengkapi kotak bantuan (*Help / Panduan Card*) di bagian bawah sidebar.
- **Overlay & Modal:** Gunakan **Centered Dialog Modal** (`border-radius: 12px`, `max-width: 480px–560px`).
---
 
## 6. Aturan Microcopy, Status & Peran
 
### 6.1 Peran Resmi (chip di header)
| Peran | Chip | Warna chip |
|---|---|---|
| Calon Peserta (belum NPD) | `Calon Peserta` | Informasi |
| Peserta Didik (NPD terbit) | `Peserta Didik` | Success |
| Admin SILAU | `Admin SILAU` | Informasi |
| Admin Keuangan | `Admin Keuangan` | Informasi |
| Admin Track | `Admin Track` | Informasi |
| Super Admin | `Super Admin` | Informasi |
 
❌ Jangan pakai `Admin akademik` atau `Akun aktif`.
 
### 6.2 Status Resmi → Warna Badge
| Entitas | Status | Warna |
|---|---|---|
| Pendaftaran | `Draf` | Netral |
| | `Diajukan` | Warning |
| | `Perlu Revisi` | Error |
| | `Masuk Kuota` | Success |
| | `Daftar Tunggu` | Warning (tampilkan nomor urut, mis. `Daftar Tunggu · #3`) |
| | `NPD Terbit` | Success |
| | `Tidak Tertampung` | Error |
| | `Dibatalkan` | Error |
| Dokumen | `Menunggu Verifikasi` / `Valid` / `Ditolak` | Warning / Success / Error |
| Tagihan | `Belum Bayar` / `Menunggu Verifikasi` / `Ditolak` / `Lunas` / `Dibatalkan` | Netral / Warning / Error / Success / Error |
| Peserta Didik | `Belum Aktif Semester N` / `Aktif Semester N` / `Tertahan (Tunggakan)` / `Selesai Masa Program` / `Dibatalkan` | Netral / Success / Error / Success / Error |
| Refund | `Menunggu Data Rekening` / `Diproses` / `Selesai` / `Ditolak` | Netral / Warning / Success / Error |
 
- Label bantu non-status (boleh, warna Netral): `Belum terunggah`, `Belum terbit` (tagihan sebelum formulir diajukan), `Menunggu verifikasi berkas` (FR-33).
- ❌ Jangan pakai: `Draft`, `Perlu Verifikasi`, `Diterima`, `Perlu perbaikan`, `Belum lengkap`, atau `Ditolak` untuk status pendaftaran.
### 6.3 Microcopy
- **Label Tombol:** Gunakan kata kerja aktif spesifik (`Lengkapi formulir →`, `Ajukan Formulir`, `Terapkan Filter`, `Simpan Penolakan`, `Lihat detail ↗`). Hindari kata generik (`Submit`, `OK`).
- **Empty State:** Ikon outline `#94A3B8` (`32px`), judul singkat `16px` SemiBold, deskripsi 1 baris `14px` `#64748B`, dan maksimal 1 tombol aksi primer.
- **Tautan Aksi Teks:** biru, SemiBold, diakhiri `→` untuk navigasi internal (`Lihat peminatan →`, `Kelola berkas →`, `Periksa biodata →`) dan `↗` untuk membuka detail (`Lihat detail ↗`).
- **Terminologi Baku:**
  - Program: **`Microcredential`** (satu kata, M kapital). ❌ `Micro Credential`, `Micro-credential`.
  - Pengguna: `Calon Peserta` sebelum NPD, `Peserta Didik` setelah NPD; di admin boleh `pendaftar` untuk data pendaftaran.
  - Pengelompokan: `Track` dan `Peminatan`. NPD = Nomor Peserta Didik.
  - Nomor pendaftaran: `PV-{TAHUN}-{6 digit}` (BR-68), tampil di ringkasan peserta dan tabel admin.
### 6.4 Tahapan Pendaftaran Baku (FR-15)
Dipakai **identik** di stepper landing dan dashboard peserta:
1. **Formulir Diajukan** — lengkapi biodata, pilih track & peminatan, unggah berkas wajib, ajukan.
2. **Verifikasi Berkas** — Admin SILAU memeriksa dokumen (bisa kembali ke Perlu Revisi).
3. **Masuk Kuota / Daftar Tunggu** — penempatan kuota otomatis.
4. **Verifikasi Pembayaran Formulir** — bayar tagihan formulir, unggah bukti, diverifikasi Admin Keuangan.
5. **NPD Terbit** — akun menjadi Peserta Didik, tagihan Semester 1 terbit.
Saat status `Draf`, stepper menyorot tahap 1 sebagai tahap berjalan (belum selesai). Status `Tidak Tertampung` / `Dibatalkan` ditampilkan sebagai banner Error di atas stepper beserta penjelasan.
 
---
 
## 7. Layar Referensi Baku (Desktop) — Acuan Layout
 
> Yang diambil dari layar referensi adalah **struktur dan gaya komponen**. Label tahapan, status, peran, dan isi data mengikuti Bagian 6 dan 9.
 
### 7.1 Shell Bersama (Header & Footer)
- **Header putih `72px`** dengan border bawah `#E2E8F0`: logo `polibatam Verify` kiri · nav tengah (`Beranda`, `Program`, `FAQ`, `Kontak`) · aksi kanan.
  - *Publik:* tombol `Masuk` (outline navy) + `Daftar` (fill navy). Nav aktif = teks biru + garis bawah biru.
  - *Login:* tombol lonceng notifikasi (kotak abu dengan titik biru) · avatar lingkaran navy berinisial (2 huruf dari nama) · nama (SemiBold) + chip peran (Bagian 6.1) · tombol `Logout` outline.
- **Footer portal:** satu baris tipis — `© 2026 Polibatam Verify. Hak cipta dilindungi.` kiri, `Politeknik Negeri Batam` kanan, Caption abu.
- **Footer landing:** 3 kolom — (logo + deskripsi + alamat berikon pin) · `Jelajahi` (tautan nav) · `Quick Support` (teks + tombol outline `Hubungi tim dukungan` + tautan sosial `Instagram ↗ YouTube ↗ LinkedIn ↗`), lalu garis pemisah + baris hak cipta.
### 7.2 Sidebar Portal
- Urutan: eyebrow portal (`PORTAL PESERTA` / `PORTAL ADMIN`) + subjudul periode (`Pendaftaran 2026`) → daftar menu berikon outline → **Help card** (latar `#EFF6FF`, radius 12px, ikon chat, judul SemiBold, deskripsi 2–3 baris, tautan `→`) → teks kecil `Politeknik Negeri Batam`.
- Menu mengikuti RBAC (SRS Bagian 3). Contoh:
  - Calon Peserta / Peserta Didik: `Dashboard`, `Formulir`, `Berkas`, `Tagihan`, `Profil` (+ `Refund` bila ada).
  - Admin SILAU: `Dashboard`, `Antrean Verifikasi Berkas`, `Data Pendaftaran`, `Dasbor Kuota`.
  - Admin Keuangan: `Dashboard`, `Antrean Verifikasi Pembayaran`, `Data Pendaftaran`, `Refund`, `Rekap Keuangan`.
  - Admin Track: `Dashboard`, `Data Pendaftaran`, `Track & Peminatan`, `Tarif`, `Kuota`, `Dasbor Kuota`.
  - Super Admin: semua menu di atas + `Periode`, `Rollover`, `Akun & Role`, `Audit Log`, `Konten Landing`.
### 7.3 Pola Header Halaman Portal
1. Breadcrumb Caption abu (`Portal Admin / Data Pendaftaran`).
2. Judul halaman (Bold, navy) + chip konteks di kanan (`Gelombang 2 · 2026`).
3. Deskripsi 1 baris `text/secondary`.
4. (Admin) baris meta kiri + tombol sekunder kanan (`Export Excel`, `Export PDF` — hanya untuk Admin SILAU dan Super Admin, BR-67).
### 7.4 Beranda (Landing)
- **Hero 2 kolom:** kiri = eyebrow berikon centang (`SISTEM VERIFIKASI MICROCREDENTIAL`) · judul besar 2 baris · deskripsi · CTA `Daftar Sekarang` (fill) + `Baca Pedoman Lengkap` (outline). Kanan = foto kampus radius 12px dengan overlay gradien navy di bawah + label berikon + caption putih.
- **Track & Peminatan:** 4 kartu sejajar — badge singkatan (`TI`, `MB`, `TE`, `TM`, latar biru muda) + nama track · deskripsi abu · daftar peminatan dipisah `·` (isi dari Bagian 9.1) · tautan `Lihat peminatan →`. Di produksi data ini diambil dari data master (BR-55).
- **Alur Pendaftaran:** kiri = eyebrow, judul, deskripsi, kotak info `Sebelum Anda mulai` (latar `#EFF6FF`) + tombol `Mulai Pendaftaran`. Kanan = kartu stepper vertikal bernomor 01–05 sesuai **Bagian 6.4**, dihubungkan garis vertikal, chip `Panduan proses`, catatan kaki Caption.
- **FAQ:** kiri = daftar kategori (item aktif fill navy + chevron, sisanya kartu putih berikon) + Help card `Belum menemukan jawaban?`. Kanan = accordion kartu putih radius 12px, pertanyaan SemiBold + chevron biru; item terbuka menampilkan jawaban + daftar bernomor biru.
### 7.5 Dashboard Peserta
- **Kartu Progres** (latar `#EFF6FF`, border aksen): judul + teks tahap di kanan (`Tahap 1 dari 5 · Lengkapi formulir`) + stepper horizontal 5 tahap (Bagian 6.4): selesai = lingkaran navy berceklis, aktif = lingkaran navy bernomor + label Bold, belum = lingkaran putih bergaris + label abu.
- **Banner info** (latar `#EFF6FF`, ikon `i`): judul SemiBold + instruksi spesifik apa yang kurang. Saat Draf, sertakan pengingat bahwa Draf otomatis Dibatalkan bila belum diajukan saat periode ditutup (BR-65) beserta tanggal tutup periode.
- **3 kartu status modul:** `Formulir` · `Berkas` · `Tagihan Formulir` — ikon kiri atas, badge status kanan atas, judul, meta progres (`7 dari 8 kolom terisi`, `2 dari 3 berkas wajib terunggah`), progress bar biru tipis, tautan aksi `→`. Tagihan Formulir berlabel `Belum terbit` sampai formulir diajukan (BR-32).
- **Grid 2 kolom bawah (±2:1):** kiri = `Ringkasan pendaftaran` (No. pendaftaran + badge status, baris Track, Peminatan, Periode, Tanggal dibuat) dan `Biodata` (2 kolom, NIK dimasking). Kanan = `Ringkasan pembayaran` (badge, penjelasan "Tagihan formulir terbit setelah formulir diajukan.", tombol outline lebar penuh) dan `Aktivitas terbaru` (timeline titik biru + judul + waktu Caption).
- Satu peminatan saja (BR-09) — ❌ tidak ada "Prioritas 1/2".
### 7.6 Data Pendaftaran (Admin) — FR-63, FR-64
- **Kartu Filter:** dropdown `Periode`, `Track`, `Peminatan`, `Status Pendaftaran`, `Rentang Tanggal Pengajuan` (berikon kalender) + kolom cari `Cari nama / No. pendaftaran / NIK` + `Reset Filter` (outline) + `Terapkan Filter` (fill).
- **Baris meta:** `486 pendaftaran diajukan · 37 Draf tidak ditampilkan` — jumlah Draf hanya angka (BR-64).
- **Kartu Tabel:** judul + chip jumlah data. **Bulk action bar** (latar `#EFF6FF`) muncul saat ada baris terpilih: `n baris dipilih` · tombol `Ekspor terpilih` · tautan `Batalkan pilihan`. ❌ Tidak ada dropdown aksi verifikasi massal (BR-66). Checkbox, bulk action bar, dan tombol ekspor hanya muncul untuk Admin SILAU dan Super Admin (BR-67); Admin Keuangan dan Admin Track melihat tabel tanpa kolom checkbox.
- **Tabel:** header latar abu muda, kolom bisa diurutkan (ikon ⇅, aktif ↓); kolom: checkbox · No. Pendaftaran · Nama (Bold) + track (Caption) · Peminatan · Tanggal + jam pengajuan (Caption) · badge Status (Bagian 6.2) · `Lihat detail ↗` (detail baca-saja). Baris terpilih berlatar `#EFF6FF`, checkbox navy.
- **Pagination:** `Baris per halaman` + dropdown + `1–8 dari N` kiri; tombol halaman kotak (aktif fill navy) dengan `…` dan panah kanan.
- **Catatan kaki** Caption: `Draf tidak ditampilkan. Verifikasi berkas dan pembayaran dilakukan satu per satu melalui antrean verifikasi.`
---
 
## 8. Keputusan Desain (Final, 21 Nov 2026)
| Topik | Keputusan |
| :--- | :--- |
| Tinggi header | `72px` (landing & portal). |
| Tahapan / alur | 5 tahap FR-15 (Bagian 6.4), sama di landing dan dashboard. |
| Status & peran | Hanya nama resmi SRS (Bagian 6.1–6.2). |
| Data contoh | Dataset dummy Bagian 9; nomor pendaftaran unik, ejaan nama konsisten. |
| Avatar | Inisial = 2 huruf dari nama pengguna. |
| Track & peminatan | Mengikuti pedoman Bagian 6 (lihat 9.1); peminatan Teknik Elektro = `Umum`. |
| Penulisan program | `Microcredential`. |
| Nomor pendaftaran | `PV-{TAHUN}-{6 digit}`, terbit saat Draf pertama disimpan (BR-68). |
| Draf | Tidak tampil di halaman admin; hanya jumlahnya (K-37, BR-64). |
| Aksi massal | Hanya `Ekspor terpilih`; verifikasi satu per satu (K-37, BR-66). |
| Hak ekspor | Hanya Admin SILAU dan Super Admin (BR-67). |
 
---
 
## 9. Dataset Dummy Baku untuk Preview
> Semua nama, NIK, email, dan nomor telepon fiktif. NIK selalu ditampilkan termasking. Nomor pendaftaran mengikuti format `PV-{TAHUN}-{6 digit}` (BR-68).
 
### 9.1 Track & Peminatan (sumber: pedoman Bagian 6)
| Kode | Track | Peminatan |
| :--- | :--- | :--- |
| `MB` | Manajemen & Bisnis | Administrasi Bisnis · Akuntansi |
| `TE` | Teknik Elektro | Umum (disepakati K-37; pedoman menulis "Umum / Sesuai Skema") |
| `TI` | Teknik Informatika | Rekayasa Perangkat Lunak · Rekayasa Multimedia |
| `TM` | Teknik Mesin | Teknik Mesin · Rekayasa Pengelasan · AMTO Basic License |
 
*Nama track memakai "Teknik Informatika" (sesuai pedoman Bagian 2.1 & FAQ), bukan "Informatika" pada tabel pedoman.*
 
### 9.2 Pengguna Login
| Peran | Nama | Inisial | Chip | Email |
| :--- | :--- | :--- | :--- | :--- |
| Calon Peserta | Nabila Putri Ramadhani | `NR` | `Calon Peserta` | `nabila.ramadhani@mail.com` |
| Admin SILAU | Hendra Saputra | `HS` | `Admin SILAU` | `hendra.saputra@polibatam.ac.id` |
| Admin Keuangan | Yuliana Siregar | `YS` | `Admin Keuangan` | `yuliana.siregar@polibatam.ac.id` |
| Admin Track | Bayu Prasetyo | `BP` | `Admin Track` | `bayu.prasetyo@polibatam.ac.id` |
| Super Admin | Ratna Wijayanti | `RW` | `Super Admin` | `ratna.wijayanti@polibatam.ac.id` |
 
**Detail peserta (Dashboard):** No. `PV-2026-000426` · status `Draf` · Tahap 1 dari 5 · Track Teknik Informatika · Peminatan Rekayasa Perangkat Lunak · Dibuat 5 November 2026 · Periode `Gelombang 2 · 2026` (tutup 30 November 2026) · NIK `2171 •••• •••• 0427` · `+62 812-7745-2190` · Kota Batam · Formulir `7 dari 8 kolom terisi` · Berkas `2 dari 3 berkas wajib terunggah` · Tagihan Formulir `Belum terbit`. *(Karena masih Draf, Nabila tidak muncul di tabel admin.)*
 
### 9.3 Daftar Pendaftaran (Tabel Admin — tanpa Draf)
| No. Pendaftaran | Nama | Track | Peminatan | Diajukan | Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| PV-2026-000425 | Raka Aditya Pratama | Teknik Mesin | Rekayasa Pengelasan | 5 Nov 2026, 14.25 WIB | Diajukan |
| PV-2026-000424 | Salsabila Azzahra | Manajemen & Bisnis | Akuntansi | 5 Nov 2026, 14.02 WIB | Diajukan |
| PV-2026-000423 | Dimas Arya Nugroho | Teknik Elektro | Umum | 5 Nov 2026, 13.54 WIB | Diajukan |
| PV-2026-000422 | Fadli Rahman Hakim | Teknik Mesin | AMTO Basic License | 5 Nov 2026, 13.10 WIB | Perlu Revisi |
| PV-2026-000421 | Intan Maharani | Teknik Informatika | Rekayasa Multimedia | 5 Nov 2026, 11.05 WIB | Masuk Kuota |
| PV-2026-000420 | Yoga Firmansyah | Teknik Mesin | Teknik Mesin | 5 Nov 2026, 10.48 WIB | Daftar Tunggu · #3 |
| PV-2026-000419 | Aulia Khairunnisa | Manajemen & Bisnis | Administrasi Bisnis | 5 Nov 2026, 10.35 WIB | Dibatalkan |
| PV-2026-000418 | Rizki Maulana | Teknik Informatika | Rekayasa Perangkat Lunak | 5 Nov 2026, 10.22 WIB | NPD Terbit |
 
Total: `486 pendaftaran diajukan` · `37 Draf tidak ditampilkan` · Pagination `1–8 dari 486` (61 halaman).