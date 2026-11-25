# Spesifikasi Kebutuhan Perangkat Lunak — Non-Functional Requirements
## Polibatam Verify
 
Disusun berdasarkan *Pain Points*, *User Personas*, *User Stories*, *FR & BR* (FR-01 s.d. FR-66, BR-01 s.d. BR-69), *Dokumen Keamanan Basis Data*, dan *Keputusan Klarifikasi Scope* (K-01 s.d. K-38).
 
**Konvensi:**
- Setiap NFR memiliki **target terukur** dan **metode verifikasi**.
- **Prioritas** memakai MoSCoW: *Must* / *Should* / *Could*.
- Angka target bertanda `[ASUMSI]` adalah usulan awal karena volume pengguna, infrastruktur, dan kebijakan institusi belum ditetapkan. Angka ini perlu dikonfirmasi.
- Zona waktu acuan: **WIB (Asia/Jakarta)**.
- **Status lingkungan (K-26):** proyek masih dikembangkan secara lokal dan lokasi *hosting* produksi belum ditetapkan. Target ketersediaan, *backup*, dan kapasitas (NFR-20 s.d. NFR-24) berlaku saat sistem dijalankan di lingkungan produksi.
---
 
## 1. Keamanan (Security)
 
| ID | Kebutuhan & Target | Verifikasi | Prioritas | Ref |
|---|---|---|---|---|
| NFR-01 | Seluruh komunikasi klien–server harus memakai **HTTPS dengan TLS ≥ 1.2** dan header HSTS. Akses HTTP dialihkan otomatis ke HTTPS. | Pemindaian SSL (mis. SSL Labs) minimal nilai A | Must | PP-17 |
| NFR-02 | Password disimpan dengan **hash adaptif (Argon2id atau bcrypt cost ≥ 12)**, tidak pernah dalam bentuk teks asli. Kebijakan password: minimal 8 karakter, mengandung huruf dan angka. | Inspeksi kode dan basis data | Must | AC-02.3, BR-03 |
| NFR-03 | Sesi berakhir setelah **30 menit tidak aktif untuk *role* admin** dan **60 menit untuk peserta** [ASUMSI]. *Cookie* sesi bertanda `HttpOnly`, `Secure`, dan `SameSite`. Logout dan penonaktifan akun langsung membatalkan sesi. | Uji fungsional sesi | Must | FR-06 |
| NFR-04 | Otorisasi harus diperiksa **di sisi server pada setiap *endpoint*** dengan prinsip *deny-by-default*. Akses ke data milik pengguna lain (IDOR) harus ditolak. | Uji otorisasi per *endpoint* sesuai Matriks RBAC | Must | FR-07, AC-15.3, BR-04 |
| NFR-05 | Aplikasi harus terlindung dari kerentanan **OWASP Top 10**: injeksi (*parameterized query*), XSS (*output encoding*), CSRF (token), dan *security misconfiguration*. | *Penetration test* sebelum *go-live* tanpa temuan *High*/*Critical* yang terbuka | Must | PP-17 |
| NFR-06 | Nomor rekening refund dan **NIK** dienkripsi *at rest* dengan **AES-256**; NIK dilengkapi *hash* HMAC untuk pencarian (K-31) dengan kunci **`NIK_HMAC_KEY` yang terpisah dari kunci enkripsi `APP_KEY`** (K-36). Kedua kunci disimpan terpisah dari basis data dan dari file *backup* basis data (*secret manager* atau variabel lingkungan terproteksi). | Inspeksi basis data dan konfigurasi | Must | K-13, K-31, BR-50, BR-62 |
| NFR-07 | Unggahan file harus: (a) divalidasi berdasarkan **tipe MIME aktual (*magic bytes*)**, bukan hanya ekstensi; (b) disimpan di penyimpanan **non-publik**; (c) diakses melalui **URL bertanda tangan yang berlaku ≤ 15 menit**; (d) diberi nama acak di server. | Uji unggah file berekstensi palsu, uji akses URL kedaluwarsa | Must | BR-12, FR-14, FR-29 |
| NFR-08 | *Rate limiting* pada *endpoint* sensitif: login, registrasi, reset password, dan unggah file, **maksimal 10 permintaan per menit per IP** [ASUMSI]. Pembatasan ini juga mengurangi risiko pengecekan email terdaftar melalui reset password (K-32). | Uji beban terarah | Must | BR-03 |
| NFR-09 | Akun admin (SILAU, Keuangan, Track, Super Admin) **wajib** memakai **autentikasi dua faktor berbasis TOTP** (aplikasi *authenticator*) di web dan mobile, karena akun admin tidak terhubung ke SSO Polibatam (K-26, K-33). | Uji fungsional: login admin tanpa TOTP ditolak | Must | PP-17, BR-59, FR-59 |
| NFR-47 | Token API mobile berupa token Sanctum **per perangkat** yang berlaku **12 jam**, disimpan di *secure storage* perangkat, dan langsung tidak berlaku saat logout, akun dinonaktifkan, atau *role* berubah (K-30). | Uji fungsional token kedaluwarsa dan dicabut | Must | BR-60, FR-60 |
| NFR-48 | Akses basis data memakai **akun MySQL terpisah dengan *least privilege***: akun DBA, migrasi, aplikasi, laporan, dan *backup*. Akun aplikasi bukan `root`, tidak memiliki hak DDL, tidak memiliki `DELETE` pada tabel domain, dan hanya terhubung dari *host* server aplikasi. Aplikasi mobile tidak pernah terhubung langsung ke basis data (K-36). | `SHOW GRANTS` per akun dan uji operasi *allowed*/*forbidden* | Must | Dokumen Keamanan Basis Data §3–§5 |
| NFR-10 | Kredensial, kunci API, dan kunci enkripsi **tidak boleh tersimpan di kode sumber** maupun repositori. | Pemindaian *secret* pada repositori | Must | — |
 
## 2. Privasi & Perlindungan Data Pribadi
 
| ID | Kebutuhan & Target | Verifikasi | Prioritas | Ref |
|---|---|---|---|---|
| NFR-11 | Pemrosesan data pribadi harus mengikuti **UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi**. Calon peserta harus menyetujui kebijakan privasi saat registrasi, dan persetujuan itu dicatat beserta waktunya. | Tinjauan kepatuhan, uji alur registrasi | Must | FR-01 |
| NFR-12 | Dokumen pendaftaran (KTP, ijazah, dll.) hanya dapat diakses pemiliknya, Admin SILAU, dan Super Admin. Bukti pembayaran hanya dapat diakses pemiliknya, Admin Keuangan, dan Super Admin. Data identitas sensitif (mis. NIK) ditampilkan tersamar di daftar/antrean. | Uji otorisasi | Must | NFR-04 |
| NFR-13 | Masa retensi: data pendaftaran, dokumen, dan transaksi keuangan disimpan **minimal 5 tahun** [ASUMSI]. Data pendaftar berstatus akhir Tidak Tertampung/Dibatalkan yang sudah melewati masa retensi dianonimkan atau dihapus. **Pengecualian:** arsip periode (BR-69) disimpan permanen dan tidak ikut anonimisasi/penghapusan, karena hanya memuat NIK tersamar dan tidak memuat dokumen maupun nomor rekening (K-38). | Tinjauan kebijakan dan uji proses retensi | Should | — |
| NFR-50 | File hasil ekspor data pendaftaran (FR-64) dan tautan unduhannya berlaku **24 jam**. Proses terjadwal menghapus file yang melewati batas tersebut setiap jam, dan direktori ekspor tidak pernah memuat file berumur > 25 jam. Penghapusan tidak menyentuh direktori arsip periode (K-38). | Uji fungsional dengan waktu dimajukan, inspeksi direktori | Must | BR-67, FR-64 |
 
## 3. Performa
 
Diukur pada beban normal sesuai NFR-20, di lingkungan setara produksi.
 
| ID | Kebutuhan & Target | Verifikasi | Prioritas | Ref |
|---|---|---|---|---|
| NFR-14 | Waktu respons server **p95 ≤ 2 detik** untuk operasi baca dan **p95 ≤ 3 detik** untuk operasi tulis. | Uji beban (mis. k6/JMeter) | Must | — |
| NFR-15 | Halaman katalog publik dimuat **p95 ≤ 1,5 detik**. Jika memakai *cache*, perubahan tarif atau kuota harus tampil di katalog **≤ 1 menit** setelah disimpan. | Uji beban dan uji konsistensi data | Must | FR-08, AC-01.2 |
| NFR-16 | Unggah file 2 MB selesai **≤ 10 detik** pada koneksi 2 Mbps. Sistem menampilkan indikator progres unggah. | Uji dengan *network throttling* | Should | FR-14, UP-01 |
| NFR-17 | *Document viewer* menampilkan dokumen **≤ 3 detik** setelah dipilih. | Uji fungsional berwaktu | Should | FR-19 |
| NFR-18 | Pratinjau *rollover* tampil **≤ 30 detik** dan eksekusi *rollover* untuk **5.000 peserta selesai ≤ 10 menit** (A-29, K-32). | Uji beban dengan data sintetis | Must | FR-51, FR-52 |
| NFR-19 | Ekspor laporan hingga **10.000 baris selesai ≤ 60 detik**. Ekspor yang lebih besar diproses di latar belakang lalu pengguna diberi tautan unduhan. Arsip periode untuk 5.000 pendaftaran tersedia **≤ 15 menit** setelah periode ditutup [ASUMSI]. | Uji fungsional berwaktu | Should | FR-38, FR-64, FR-65 |
 
## 4. Kapasitas & Skalabilitas
 
| ID | Kebutuhan & Target | Verifikasi | Prioritas | Ref |
|---|---|---|---|---|
| NFR-20 | Sistem mendukung **≥ 500 pengguna aktif bersamaan** pada puncak pendaftaran dan **≥ 5.000 pendaftaran per periode** (A-29, K-32) tanpa melanggar target NFR-14. | Uji beban dan *stress test* | Must | K-15 |
| NFR-21 | Penyimpanan file dapat diperluas tanpa *downtime*. Estimasi awal: ±40 GB per periode (5.000 pendaftar × 4 dokumen × 2 MB), ditambah bukti pembayaran. Arsip periode diperkirakan < 5 MB per periode, sehingga penyimpanan permanen tidak berdampak signifikan. | Tinjauan arsitektur | Should | BR-12, BR-69 |
| NFR-22 | Lapisan aplikasi web bersifat *stateless* sehingga dapat diskalakan horizontal. Proses latar belakang (notifikasi, proses terjadwal, ekspor, arsip periode, *rollover*) dijalankan melalui antrean (*job queue*) terpisah dari permintaan pengguna. | Tinjauan arsitektur | Should | FR-24, FR-53, FR-65 |
 
## 5. Ketersediaan & Keandalan
 
| ID | Kebutuhan & Target | Verifikasi | Prioritas | Ref |
|---|---|---|---|---|
| NFR-23 | Ketersediaan layanan **≥ 99,5% per bulan**, dan **≥ 99,9% selama periode pendaftaran berlangsung** [ASUMSI]. Pemeliharaan terjadwal dilakukan di luar pukul 08.00–22.00 WIB dan diumumkan minimal H-2. | Pemantauan *uptime* | Must | — |
| NFR-24 | *Backup* basis data otomatis harian dengan *point-in-time recovery*. **RPO ≤ 1 jam, RTO ≤ 4 jam** [ASUMSI]. *Backup* file harian. *Backup* basis data dienkripsi, dan uji *restore* ke basis data uji dilakukan minimal sekali per semester (prosedur: Dokumen Keamanan Basis Data §13). Direktori arsip periode wajib ikut *backup* dan memiliki minimal satu salinan di lokasi terpisah dari server aplikasi; direktori ekspor sementara dikecualikan dari *backup*. | Laporan uji *restore* | Must | BR-69 |
| NFR-25 | Proses terjadwal (pembatalan 7 hari, penutupan periode, pembuatan arsip periode, pembersihan file ekspor, pengingat tagihan, peringatan *rollover*) harus **idempoten**, tercatat setiap eksekusinya, dicoba ulang otomatis bila gagal, dan mengirim *alert* ke tim teknis setelah 3 kegagalan berturut-turut. | Uji kegagalan (*fault injection*) | Must | FR-24, FR-25, FR-54, FR-58, FR-64, FR-65 |
| NFR-26 | Email notifikasi terkirim **≤ 5 menit** setelah kejadian, dengan percobaan ulang hingga 3 kali. Kegagalan pengiriman dicatat. Notifikasi *in-app* tetap tersedia meski email gagal. | Uji fungsional berwaktu dan log | Must | FR-53, BR-56 |
 
## 6. Integritas Data
 
| ID | Kebutuhan & Target | Verifikasi | Prioritas | Ref |
|---|---|---|---|---|
| NFR-27 | Operasi multi-langkah harus **atomik (ACID)**: penerbitan NPD, pembatalan peminatan, dan *rollover*. Pada akses bersamaan tidak boleh terjadi *race condition*, misalnya NPD/kode unik/nomor pendaftaran ganda (penempatan kuota dihapus, K-42). | Uji konkurensi (≥ 50 permintaan simultan pada penerbitan NPD/kode unik) | Must | FR-34, FR-41, FR-52 |
| NFR-28 | Perubahan status hanya boleh mengikuti transisi yang terdefinisi pada tabel transisi status. Transisi di luar tabel ditolak di lapisan *domain/service*. | *Unit test* untuk setiap transisi valid dan tidak valid | Must | Data & State Notes |
| NFR-29 | Nominal uang disimpan sebagai **bilangan bulat Rupiah** (tanpa *floating point*). | Inspeksi skema basis data | Must | BR-29, BR-48 |
| NFR-30 | Waktu disimpan dalam **UTC** dan ditampilkan dalam **WIB**. Perhitungan tenggat (H-7, H-14, 7 hari) memakai kalender WIB. | *Unit test* perhitungan tanggal | Must | BR-21, BR-35, BR-44 |
| NFR-49 | Arsip periode bersifat ***write-once***: disimpan di *disk* privat terpisah (`arsip`) yang tidak dapat ditimpa atau dihapus oleh akun aplikasi (izin sistem berkas hanya tulis-baru dan baca), tabel `arsip_periodes` tidak memiliki hak `UPDATE` atas kolom file dan `DELETE` untuk akun aplikasi, dan *checksum* SHA-256 diverifikasi setiap kali arsip diunduh. Pemeriksaan integritas seluruh arsip dijalankan otomatis minimal sebulan sekali. | Uji timpa/hapus file dengan akun aplikasi, uji file dimanipulasi, `SHOW GRANTS` | Must | BR-69, FR-65, FR-66 |
 
## 7. Keterauditan & Pemantauan
 
| ID | Kebutuhan & Target | Verifikasi | Prioritas | Ref |
|---|---|---|---|---|
| NFR-31 | *Audit log* bersifat **append-only**: pengguna aplikasi, termasuk Super Admin, tidak memiliki hak ubah atau hapus di tingkat basis data. Setiap entri memuat *hash* entri sebelumnya (*hash chain*) agar perubahan dapat dideteksi. Setiap entri mencatat **pelaku, aksi, objek, waktu, dan hasil** (`berhasil`/`gagal`/`ditolak`), termasuk login admin gagal, TOTP gagal, akses ditolak (403), konflik verifikasi (409), dan transaksi yang gagal (K-36). Retensi **≥ 5 tahun** [ASUMSI]. Jam server disinkronkan dengan NTP. | Inspeksi hak akses basis data, uji verifikasi *hash chain* | Must | BR-58, FR-55 |
| NFR-32 | Log aplikasi dan *error* dikumpulkan terpusat dengan *alert* untuk *error rate* abnormal. Log **tidak boleh memuat** password, token, nomor rekening lengkap, atau isi dokumen. | Inspeksi log | Must | NFR-06 |
 
## 8. Usability & Aksesibilitas
 
| ID | Kebutuhan & Target | Verifikasi | Prioritas | Ref |
|---|---|---|---|---|
| NFR-33 | Antarmuka **responsif** dan dapat dipakai penuh pada layar mulai lebar **360 px**, terutama alur pendaftaran, unggah, dan tagihan. | Uji di perangkat seluler | Must | UP-01 |
| NFR-34 | Bahasa antarmuka **Bahasa Indonesia**. Format mata uang `Rp 1.500.000` dan tanggal `21 November 2026`. | Tinjauan UI | Must | — |
| NFR-35 | Pesan validasi dan *error* bersifat spesifik per *field*, menjelaskan cara memperbaikinya, dan tidak menampilkan detail teknis (*stack trace*). | Tinjauan UI dan uji negatif | Must | AC-04.3, AC-05.2 |
| NFR-36 | Antarmuka memenuhi **WCAG 2.1 level AA** untuk halaman publik dan alur peserta. | Audit aksesibilitas (mis. axe/Lighthouse ≥ 90) | Should | — |
| NFR-37 | ≥ 80% calon peserta dalam uji *usability* dapat menyelesaikan registrasi hingga pengajuan formulir **≤ 15 menit tanpa bantuan** [ASUMSI]. | Uji *usability* dengan ≥ 5 responden | Should | PP-02, UP-01 |
 
## 9. Kompatibilitas
 
| ID | Kebutuhan & Target | Verifikasi | Prioritas | Ref |
|---|---|---|---|---|
| NFR-38 | Mendukung **2 versi terbaru** Chrome, Firefox, Edge, dan Safari, serta Chrome Android dan Safari iOS. | Uji lintas *browser* | Must | — |
| NFR-39 | File ekspor laporan dan arsip periode berformat **.xlsx** yang dapat dibuka di Microsoft Excel dan Google Sheets; ekspor laporan juga tersedia dalam **PDF**. | Uji buka file | Must | FR-38, FR-64, FR-65 |
 
## 10. Pemeliharaan & Konfigurabilitas
 
| ID | Kebutuhan & Target | Verifikasi | Prioritas | Ref |
|---|---|---|---|---|
| NFR-40 | Parameter bisnis berikut **dapat diubah melalui konfigurasi tanpa mengubah kode**: batas pelunasan formulir (7 hari), jatuh tempo (H-7), ambang peringatan *rollover* (H-14), jadwal pengingat (H-7, H-1), format dan ukuran file, format NPD, batas *lockout* login, serta masa berlaku file ekspor (24 jam). | Uji ubah konfigurasi | Must | BR-03, BR-12, BR-21, BR-35, BR-41, BR-44, BR-57, BR-67 |
| NFR-41 | Modul logika bisnis kritis (kuota, tagihan, NPD, *rollover*, refund) memiliki **cakupan *unit test* ≥ 80%** [ASUMSI]. | Laporan *code coverage* | Should | — |
| NFR-42 | Tersedia lingkungan terpisah **development, staging, dan production**. Data produksi tidak boleh dipakai di non-produksi tanpa anonimisasi. | Tinjauan infrastruktur | Must | NFR-11 |
 
## 11. Integrasi & Dependensi Eksternal
 
| ID | Kebutuhan & Target | Verifikasi | Prioritas | Ref |
|---|---|---|---|---|
| NFR-43 | Autentikasi dikelola **mandiri oleh Polibatam Verify** dan tidak bergantung pada SSO Polibatam. Seluruh akun (peserta dan admin) dibuat dan dikelola di sistem ini. | Tinjauan arsitektur | Must | K-26, FR-01, FR-06 |
| NFR-44 | Pengiriman email memakai **adapter yang dapat dikonfigurasi** (SMTP atau API penyedia email) tanpa mengubah kode. Di lingkungan *development*, email ditangkap oleh *mail catcher* lokal (mis. Mailpit/MailHog). | Uji ganti konfigurasi penyedia | Must | K-26, FR-02, FR-05, FR-53 |
| NFR-45 | **Layanan email produksi wajib tersedia sebelum *go-live***, karena verifikasi email (BR-02), reset password (FR-05), dan undangan akun admin (FR-06) tidak dapat berjalan tanpa email. | *Checklist* kesiapan *go-live* | Must | K-26 |
| NFR-46 | Penyedia email mengikuti tahapan berikut (K-27): (a) **Development lokal:** Mailpit; (b) **Demo/pilot terbatas:** Brevo atau Resend paket gratis; (c) **Produksi:** SMTP email institusi Polibatam (domain `polibatam.ac.id`) atau paket berbayar dengan kuota ≥ estimasi beban puncak (±40.000 email per periode). Domain pengirim produksi harus dikonfigurasi SPF, DKIM, dan DMARC. | Uji pengiriman di setiap tahap, pemeriksaan DNS | Must | K-27, NFR-44 |
 
---
 
## 12. Ringkasan
 
| Kategori | NFR | Must | Should |
|---|---|---|---|
| Keamanan | NFR-01 – 10, NFR-47, NFR-48 | 12 | 0 |
| Privasi & Perlindungan Data | NFR-11 – 13, NFR-50 | 3 | 1 |
| Performa | NFR-14 – 19 | 3 | 3 |
| Kapasitas & Skalabilitas | NFR-20 – 22 | 1 | 2 |
| Ketersediaan & Keandalan | NFR-23 – 26 | 4 | 0 |
| Integritas Data | NFR-27 – 30, NFR-49 | 5 | 0 |
| Keterauditan & Pemantauan | NFR-31 – 32 | 2 | 0 |
| Usability & Aksesibilitas | NFR-33 – 37 | 3 | 2 |
| Kompatibilitas | NFR-38 – 39 | 2 | 0 |
| Pemeliharaan & Konfigurabilitas | NFR-40 – 42 | 2 | 1 |
| Integrasi & Dependensi Eksternal | NFR-43 – 46 | 4 | 0 |
| **Total** | **50** | **41** | **9** |
 
## 13. Status Asumsi
 
| # | Asumsi | Dipakai di | Status |
|---|---|---|---|
| A-26 | Batas sesi tidak aktif: 30 menit (admin), 60 menit (peserta). | NFR-03 | Standar awal |
| A-27 | *Rate limit* 10 permintaan per menit per IP untuk *endpoint* sensitif. | NFR-08 | Standar awal |
| A-28 | Retensi data dan *audit log* minimal 5 tahun. | NFR-13, NFR-31 | Dipakai sementara; Polibatam belum memiliki kebijakan retensi (K-26). Arsip periode dikecualikan (permanen, K-38). |
| A-29 | Volume: ≥ 500 pengguna bersamaan, ≥ 5.000 pendaftaran per periode, ≥ 5.000 peserta per *rollover*. | NFR-18, NFR-20, NFR-21 | Dikonfirmasi (K-32) |
| A-30 | Ketersediaan 99,5% per bulan dan 99,9% selama periode pendaftaran. RPO ≤ 1 jam, RTO ≤ 4 jam. | NFR-23, NFR-24 | Ditinjau ulang setelah lokasi *hosting* ditetapkan (K-26) |
| A-31 | Target *usability*: ≥ 80% responden menyelesaikan pendaftaran ≤ 15 menit. | NFR-37 | Standar awal |
| A-32 | Cakupan *unit test* ≥ 80% untuk modul logika bisnis kritis. | NFR-41 | Standar awal |
| A-52 | *Audit log* mencatat hasil aksi, termasuk aksi gagal dan ditolak. | NFR-31 | Disetujui (K-36) |
| A-53 | Kunci HMAC NIK terpisah dari `APP_KEY`. | NFR-06 | Disetujui (K-36) |
| A-59 | File ekspor dan tautannya berlaku 24 jam. | NFR-50 | Disetujui (K-38) |
| A-60 | Arsip periode disimpan permanen. | NFR-13, NFR-24, NFR-49 | Disetujui (K-38) |
 