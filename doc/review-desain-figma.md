# Review Desain Figma vs Dokumen — Polibatam Verify
 
File: `Microcredential - Website (Copy)` (tim Education), halaman **Page 3** (`1562:1144`). Tanggal review: 21 November 2026.
Acuan: `srs-fr-br.md`, `user-stories.md`, `daftar-halaman-web.md` (K-39), `keputusan-klarifikasi-scope.md` (K-01 s.d. K-40), `Skills/design-memory.md`.
 
**Status (21 November 2026):** Tahap 1 (teks), Tahap 2 (rework frame inti), dan Tahap 3 (pustaka komponen + generate seluruh halaman HW-01 s.d. HW-56) **selesai**. Seluruh temuan S-01 s.d. S-33 tertangani di halaman baru **"🖥️ Halaman Web (HW)"** (lihat §4). Frame lama di Page 3 dipertahankan sebagai referensi.
 
Seluruh **50 frame** dibaca teksnya; 2 frame dicek visual setelah revisi.
 
---
 
## 1. Revisi yang Sudah Diterapkan di Figma (Tahap 1 — teks & istilah)
 
**379 lapisan teks** diubah di 36 frame, ditambah 1 baris disembunyikan dan 2 frame diganti nama.
 
| Kategori | Sebelum | Sesudah | Acuan |
|---|---|---|---|
| Nama program | Micro Credential | Microcredential | design-memory §8 |
| Istilah | Jurusan / JURUSAN / jurusan | Track | Pedoman Bag. 6, FR-45 |
| Istilah | Draft | Draf | Glosarium |
| *Role* | Admin akademik, Admin_Akademik | Admin SILAU (verifikasi), Admin Track (track, peminatan, kuota, harga), Super Admin (FAQ & landing page) | BR-04, K-14 |
| *Role* | Admin_Keuangan, User_Pendaftar, Administrator, SuperAdmin | Admin Keuangan, Calon Peserta, Super Admin | RBAC |
| Menu admin | Verifikasi Akademik / Verifikasi Keuangan | Verifikasi Berkas / Verifikasi Pembayaran | FR-18, FR-31 |
| Status pendaftaran (admin) | Perlu Verifikasi / Diterima / Ditolak / submitted | Diajukan / Masuk Kuota / Perlu Revisi / diajukan | Glosarium, BR-17 |
| Aksi massal Data Pendaftaran | Tandai untuk verifikasi + Terapkan Aksi | Ekspor terpilih + Ekspor | BR-66 |
| Status dokumen | Tersimpan / Perlu Perbaikan / Belum Diunggah | Menunggu Verifikasi / Ditolak / Opsional | Glosarium |
| Jenis dokumen | KTP, Ijazah/SKL, Pasfoto, Transkrip/Rapor | KTP, Ijazah / SKL / Rapor, SKHU / Daftar Nilai, Bukti Pendaftaran Polibatam (opsional) | BR-11 |
| Ukuran file | Maks. 5 MB | Maks. 2 MB | BR-12 |
| Peminatan | Teknologi RPL, Multimedia (dan Jaringan), Teknik Elektronika, Teknik Mekatronika | Rekayasa Perangkat Lunak, Rekayasa Multimedia, Umum | Pedoman Bag. 6, K-37 |
| Dasbor peserta | Prioritas 1 / Prioritas 2 (dua peminatan) | Satu peminatan; baris kedua disembunyikan | BR-09 |
| Kuota | "kebijakan akhir perlu dikonfirmasi" | Kuota penuh → Daftar Tunggu (FIFO) | K-15, K-22 |
| NPD | Terbit setelah pembayaran semester 1; format belum ditetapkan | Terbit saat tagihan **formulir** Lunas; format sementara `MC-{TAHUN}-{KODE_PEMINATAN}-{NO_URUT_4_DIGIT}` | BR-41, K-23 |
| Approval pembayaran | Sahkan (Valid) / Tolak (Invalid), catatan opsional | Tandai Lunas / Tolak Pembayaran, alasan wajib jika ditolak | FR-32, BR-38 |
| Kelola User | Suspend/Block, Delete User, Password Sementara | Kirim Ulang Undangan, Nonaktifkan Akun, aktivasi via email + 2FA | FR-06, BR-05, K-33 |
| Audit log | Level risiko High/Medium/Critical/Low | Hasil Berhasil/Gagal | K-36, NFR-31 |
| Lupa password | State gagal kirim | State "Email tidak terdaftar" | K-32 |
| Pembayaran | "BNI Virtual Account" | BNI (transfer manual + kode unik) | BR-29 |
| Super Admin | PostgreSQL 15 | MySQL 9.7 LTS | K-28 |
| Frame | EROR 405 / EROR 406 | Error 403 — Akses Ditolak / Error 404 — Halaman Tidak Ditemukan | HW-49, HW-50 |
 
Catatan: 1 teks di frame Audit log memakai font "Liberation Mono" yang tidak tersedia, sehingga tidak bisa diubah.
 
---
 
## 2. Temuan Struktural (Tahap 2)
 
Prioritas: **P1** = bertentangan dengan aturan bisnis, **P2** = halaman/komponen kurang, **P3** = kerapian. Kolom **Penyelesaian** merujuk halaman baru di §4.
 
### 2.1 Alur Peserta
 
| # | Frame | Temuan | Revisi | Acuan | P | Penyelesaian |
|---|---|---|---|---|---|---|
| S-01 | Formulir Data Diri, Pilih Program, Upload Berkas (+2 preview) | Tab: Data Diri · Pilihan Program · Upload Berkas · Pembayaran | Pisahkan **HW-14 Pilih Peminatan**. HW-15 jadi satu halaman 3 tab: **Data Diri · Berkas · Tinjau & Ajukan** | K-39, FR-11–14 | P1 | HW-14, HW-15 |
| S-02 | Formulir Data Diri | Alamat terpecah; tanpa Asal Sekolah & Tahun Lulus | *Field* sesuai K-31 | K-31 | P1 | HW-15 |
| S-03 | Upload Berkas | "Kirim untuk Verifikasi" dari tab berkas | Pengajuan hanya dari tab Tinjau & Ajukan | FR-12 | P1 | HW-15 (frame Tinjau di Page 3) |
| S-04 | Pembayaran, Html→Body | Hanya tagihan Semester 1 | Tagihan formulir terbit saat diajukan | BR-32, BR-39, FR-26 | P1 | HW-17, HW-18 |
| S-05 | Pembayaran | Tanggal Transfer & Nama Pemilik Rekening | **Dipertahankan** (K-40) → BR-72, FR-29 | K-40 | P3 | HW-18, HW-29 |
| S-06 | Dashboard peserta (2 frame) | Tracker 6 tahap | Tracker 5 tahap FR-15 | FR-15 | P1 | HW-13 |
| S-07 | Status pendaftaran | Riwayat lama | Riwayat status resmi | Glosarium, FR-15 | P1 | HW-13, HW-23 |
| S-08 | Status pendaftaran | Unduh "Kartu pendaftar" | **Dipertahankan** (K-40) → BR-73, FR-68; "Bukti kelulusan berkas" tidak dipakai | K-40 | P3 | HW-13 |
| S-09 | Peserta | Belum ada HW-16–HW-20 | Desain halaman | daftar-halaman-web | P2 | HW-16 – HW-20 |
 
### 2.2 Admin SILAU & Data Pendaftaran
 
| # | Frame | Temuan | Revisi | Acuan | P | Penyelesaian |
|---|---|---|---|---|---|---|
| S-10 | Approval pendaftaran | Tahap 2 Terima / Tolak / Kembalikan | Hapus; keputusan per dokumen + Selesaikan verifikasi | FR-20, FR-21, BR-17 | P1 | HW-26; frame lama diberi label "❌ TIDAK DIPAKAI" |
| S-11 | Verifikasi & detail berkas | "Tahap 1 dari 2" | *Checklist*, Valid/Ditolak per dokumen, sisa kuota, dialog 409 | FR-19–21, FR-61 | P1 | HW-26 |
| S-12 | Data pendaftaran | Ekspor untuk semua admin | Hanya SIL & SA | BR-67 | P2 | HW-22 (versi SILAU) |
| S-13 | Dashboard admin | Gaya "akademik" | Dasbor per *role* | HW-21 | P2 | HW-21 |
| S-14 | Sidebar admin | Tanpa Arsip Periode | Menu SILAU sesuai daftar halaman | HW-21–27 | P2 | Komponen `PV/Sidebar` Role=SILAU |
 
### 2.3 Admin Track
 
| # | Frame | Temuan | Revisi | Acuan | P | Penyelesaian |
|---|---|---|---|---|---|---|
| S-15 | Kelola Track | Tanpa kode track | Tambah kode unik | BR-52 | P1 | HW-33 |
| S-16 | Peminatan & Kuota | Kuota tunggal, tanpa durasi, data non-pedoman | Pisah Peminatan & Kuota per periode | FR-46, FR-48 | P1 | HW-34, HW-36 |
| S-17 | Kelola Harga Semester | Matriks per angkatan, tanpa tarif formulir | Tarif berversi | FR-47 | P1 | HW-35 |
| S-18 | Admin Track | Belum ada Dasbor Kuota | Desain halaman | FR-49 | P2 | HW-37 |
 
### 2.4 Admin Keuangan
 
| # | Frame | Temuan | Revisi | Acuan | P | Penyelesaian |
|---|---|---|---|---|---|---|
| S-19 | Daftar pembayaran | Daftar campur, refund digabung | Antrean dengan filter jenis tagihan | FR-31, FR-44 | P1 | HW-28 |
| S-20 | Verifikasi & approval pembayaran | Dua tahap | Satu halaman detail + gerbang ganda + 409 | FR-32–35 | P1 | HW-29 |
| S-21 | Rekening Institusi | Belum ada di FR | **Diperlukan** (K-40) → BR-70/71, FR-67 | K-40 | P2 | HW-56 |
| S-22 | Keuangan | Belum ada Refund & Rekap | Desain halaman | FR-38, FR-44 | P2 | HW-30, HW-31, HW-32 |
 
### 2.5 Super Admin
 
| # | Frame | Temuan | Revisi | Acuan | P | Penyelesaian |
|---|---|---|---|---|---|---|
| S-23 | Dashboard SuperAdmin | Kesehatan server | Peringatan *rollover*, tagihan tanpa jatuh tempo, arsip gagal | HW-21 | P2 | Pola HW-21 (sidebar SuperAdmin); varian dasbor SA belum dibuat terpisah |
| S-24 | Kelola User | Memuat akun peserta | Hanya akun admin | FR-06 | P1 | HW-44 |
| S-25 | Audit log | Ekspor CSV/JSON; filter IP | Filter sesuai FR-56 | FR-56 | P3 | HW-45 |
| S-26 | Sidebar Super Admin | Menu kurang | Menu lengkap | HW-38–48 | P2 | Komponen `PV/Sidebar` Role=SuperAdmin |
| S-27 | FAQ / Landing Page | *Role* lama | CMS Super Admin | K-14 | P2 | HW-46 |
 
### 2.6 Publik, Autentikasi & Sistem
 
| # | Frame | Temuan | Revisi | Acuan | P | Penyelesaian |
|---|---|---|---|---|---|---|
| S-28 | Login | Tanpa 2FA | Tambah HW-08 | FR-59, K-33 | P1 | HW-07, HW-08 |
| S-29 | Registrasi | Sesuai | — | FR-01 | — | HW-05 |
| S-30 | Beranda | Alur belum sama dengan FR-15 | Sesuaikan langkah | FR-15 | P3 | HW-01 |
| S-31 | Navigasi | Header 96 px | 72 px | design-memory §8 | P3 | Komponen header 72 px |
| S-32 | Data contoh | Nama dummy berbeda | Samakan dataset §9 | design-memory §9 | P3 | Halaman HW memakai dataset §9 + nama tambahan |
| S-33 | Halaman sistem | Baru 403 & 404 | Tambah 410, 419, 429, 500, 503 | HW-51–55 | P3 | HW-49 – HW-55 |
 
---
 
## 3. Keputusan (dijawab 21 November 2026 → K-40)
 
1. **Rekening Institusi (S-21):** **diperlukan** → BR-70, BR-71, FR-67, halaman HW-56.
2. **Tanggal transfer & nama pengirim (S-05):** **dipertahankan** (ditambah nominal ditransfer & bank pengirim) → BR-72, FR-29, FR-31.
3. **Unduh kartu pendaftar (S-08):** **dipertahankan** → BR-73, FR-68.
---
 
## 4. Tahap 3 — Pustaka Komponen & Generate Halaman
 
### 4.1 Halaman "🧩 Komponen PV"
 
- **Variabel warna** (koleksi `PV Tokens`, 22 variabel): `action/primary` #1E376C, `text/primary` #172B4D, `text/secondary` #64748B, `background/canvas` #F7F9FC, `border/default` #E2E8F0, `surface/interactive` #EFF6FF, serta `status/{success,warning,error,neutral}/{text,surface}`.
- **Text style** (15): `PV/Display`, `PV/Heading`, `PV/Title`, `PV/Subheading`, `PV/Body`, `PV/Body Small`, `PV/Label`, `PV/Button`, `PV/Label Compact`, `PV/Caption`, `PV/Meta`, `PV/Eyebrow`, `PV/Hero`, `PV/Section`, `PV/Greeting` — semuanya Plus Jakarta Sans.
- **Komponen** (12 set):
| Komponen | Varian / properti |
|---|---|
| `PV/Button` | Variant = Primary · Secondary · Danger · Link; teks `Label` |
| `PV/Badge` | Tone = Success · Warning · Error · Neutral · Info; teks `Label` |
| `PV/Tab` | State = Active · Default |
| `PV/Step` | State = Done · Current · Upcoming; `Nomor`, `Label` |
| `PV/Sidebar Item` | State = Active · Default |
| `PV/Sidebar` | Role = Peserta · SILAU · Keuangan · Track · SuperAdmin (menu sesuai RBAC) |
| `PV/Alert` | Tone = Info · Warning · Error · Success; `Judul`, `Teks` |
| `PV/Field` | Type = Input · Select · Textarea; `Label`, `Nilai`, `Bantuan` |
| `PV/Header Portal` | `Nama`, `Inisial`, chip *role* |
| `PV/Header Publik` | Tombol Masuk / Daftar |
| `PV/Page Header` | `Breadcrumb`, `Judul`, `Deskripsi`, chip konteks |
| `PV/Stat Card`, `PV/Footer` | `Label`, `Nilai`, `Keterangan` |
 
### 4.2 Halaman "🖥️ Halaman Web (HW)"
 
**56 frame** (1440 px) dikelompokkan dalam **9 *section* per aktor**, satu baris per *section*: 1 Publik (HW-01–04) · 2 Autentikasi (HW-05–11) · 3 Calon Peserta / Peserta Didik (HW-13–20) · 4 Admin bersama (HW-12, HW-21–24, contoh tampilan Admin SILAU) · 5 Admin SILAU (HW-25–27) · 6 Admin Keuangan (HW-28–32, HW-56) · 7 Admin Track (HW-33–37) · 8 Super Admin (HW-38–48) · 9 Halaman sistem (HW-49–55). Seluruh frame memakai *instance* komponen di atas, warna terikat variabel, dan data contoh dari design-memory §9.
 
Catatan terbuka:
- *Document viewer*, area unggah, dan dialog konfirmasi masih berupa *placeholder* (belum menjadi komponen).
- Foto kampus di beranda (HW-01) masih *placeholder*.
- Dasbor admin baru dibuat untuk Admin SILAU (HW-21); varian Keuangan, Track, dan Super Admin memakai pola yang sama.
- Navigasi publik di `PV/Header Portal` (Beranda · Program · FAQ · Kontak) dapat dipertimbangkan untuk disembunyikan di portal admin.
 