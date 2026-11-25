# Keputusan Klarifikasi Scope — Polibatam Verify
 
Dikonfirmasi oleh pengguna pada 20–21 November 2026 (K-01 s.d. K-41). Keputusan di bawah ini **mengesampingkan** isi Pedoman Microcredential 2026 jika keduanya bertentangan.
 
## K-01 — Urutan Registrasi & Status Akun
1. Pengguna membuat akun dengan **email dan password**.
2. Pengguna mengisi **formulir pendaftaran berbayar**.
3. Setelah registrasi formulir diterima (berkas dan pembayaran formulir valid), akun mendapat **NPD (Nomor Peserta Didik)** dan berubah menjadi **akun Peserta Didik**.
4. Peserta Didik membayar tagihan semester. Setelah itu statusnya menjadi **Peserta Didik Aktif Semester 1–N**.
Urutan verifikasi (lihat K-12 dan K-15):
`Formulir diajukan → Verifikasi berkas (Admin SILAU) → Masuk kuota / Daftar tunggu → Verifikasi pembayaran formulir (Admin Keuangan) → NPD terbit → Verifikasi pembayaran semester (Admin Keuangan) → Aktif Semester 1`
 
Alur status:
`Akun Terdaftar → Pendaftar (formulir diajukan) → Peserta Didik (NPD terbit) → Peserta Didik Belum Aktif / Aktif Semester 1 … N → Selesai Masa Program`
 
## K-02 — Skema Biaya Dinamis
- Biaya formulir pendaftaran dan biaya semester **dapat diubah melalui sistem**, mengikuti kesepakatan terbaru.
- Rentang nominal di Pedoman hanya acuan, bukan nilai tetap di sistem.
## K-03 — Seleksi
- Tidak ada tes dan tidak ada interview untuk semua peminatan.
## K-04 — RPL
- RPL dilakukan **di luar** Polibatam Verify.
## K-05 — Data Track/Peminatan
- Data track dan peminatan di Pedoman tidak dijadikan data tetap. Sistem menyediakan fitur tambah/ubah track dan peminatan.
## K-06 — Kuota & Refund
- Kuota minimum dan maksimum bersifat **dinamis** (dapat diatur sesuai kesepakatan terbaru).
- **Refund** berlaku jika peminatan dibatalkan (lihat K-16 dan K-19).
- Di luar scope Polibatam Verify: tagihan pengulangan modul, biaya sertifikasi/uji ulang, dan modul RPL.
## K-07 — Akhir Siklus Semester
- Siklus semester peserta berakhir sesuai **durasi semester peminatan** yang diikuti.
## K-08 — Istilah NPD
- NPD = **Nomor Peserta Didik** (bukan "Nomor Pokok Peserta Didik").
## K-09 — Kode Unik Pembayaran
- Nominal tagihan dapat ditambah kode unik untuk pencocokan mutasi. **Selisih akibat kode unik tidak dikembalikan.**
## K-10 — Kontrol Bukti Pembayaran
- *Hash check* file bukti transfer hanya kontrol awal. Kontrol yang mengikat adalah pencocokan mutasi rekening dengan kode referensi tagihan.
- Tagihan memiliki status **Ditolak**. Status ini membuka kembali opsi unggah ulang bukti pembayaran.
## K-11 — Durasi Peminatan & Status Akhir
- Parameter master peminatan adalah **durasi semester** (bukan durasi maksimal).
- Status akhir peserta setelah mencapai durasi semester: **Selesai Masa Program** (bukan "Lulus", karena kelulusan akademik ditentukan di luar sistem).
## K-12 — Kuota & Pendaftar Terverifikasi
- **Pendaftar terverifikasi** = peserta yang **NPD-nya sudah terbit**.
- Kuota minimum (dan maksimum awal) **ditetapkan sebelum periode pendaftaran dimulai**.
- Pendaftar masuk hitungan kuota setelah berkas diverifikasi Admin SILAU, sebelum verifikasi pembayaran formulir oleh Admin Keuangan.
## K-13 — Data Rekening Refund
- Nomor rekening pengembalian dana disimpan **terenkripsi**.
## K-14 — Landing Page
- Pengelolaan konten *landing page* **hanya oleh Super Admin**.
## K-15 — Kuota Maksimum Tidak Menolak Pendaftar
- Tujuan utama sistem adalah **mengumpulkan peserta didik sebanyak mungkin**.
- Jika kuota maksimum peminatan sudah penuh, sistem **tetap menerima pendaftaran**. Pendaftar dengan berkas valid masuk **Daftar Tunggu**.
- Kuota maksimum **dapat ditambah** selama periode pendaftaran berjalan. Saat slot bertambah atau dilepas, pendaftar Daftar Tunggu dinaikkan menjadi Masuk Kuota.
## K-16 — Dasar Pembatalan Peminatan
Peminatan tidak dijalankan (dibatalkan) jika:
1. jumlah pendaftar terverifikasi **tidak mencapai kuota minimum**, atau
2. ada **kesepakatan terbaru** untuk tidak menjalankannya.
## K-17 — Batas Waktu Pembayaran Formulir & Pelepasan Slot
- Pendaftaran berstatus Masuk Kuota yang tagihan formulirnya tidak dilunasi dalam **7 hari** otomatis **Dibatalkan** dan slot kuotanya dilepas.
- Asumsi lain pada daftar asumsi User Stories (A-01 s.d. A-14) **disetujui**, dengan A-05 dan A-13 direvisi sesuai K-15 dan K-17.
## K-18 — Tunggakan Saat Rollover
- Peserta yang tagihan semester berjalannya belum Lunas saat *rollover* **tidak dinaikkan** dan berstatus **Tertahan (Tunggakan)**.
- Peserta Tertahan **tidak dapat mengikuti semester selanjutnya** dan tidak diterbitkan tagihan semester berikutnya sampai tunggakannya Lunas.
## K-19 — Cakupan Refund
- **Semua biaya yang tidak terpakai dikembalikan**, termasuk biaya formulir dan tagihan semester yang sudah Lunas.
- Berlaku untuk kedua dasar pembatalan di K-16 (kuota minimum maupun kesepakatan terbaru).
- Selisih kode unik tetap tidak dikembalikan (K-09).
## K-20 — Tarif Formulir per Peminatan
- Tarif formulir pendaftaran **bervariasi tergantung peminatan** dan dikelola berversi seperti tarif semester.
## K-21 — Status Belum Aktif Semester N
- Istilah **Belum Aktif Semester N** disetujui untuk peserta yang sudah memiliki NPD atau sudah naik semester tetapi tagihan semester N belum Lunas.
## K-22 — Daftar Tunggu, Tidak Tertampung & Refund Belum Terverifikasi
- Urutan Daftar Tunggu ditentukan oleh **waktu pengajuan formulir** (siapa cepat dia dapat / FIFO).
- Pendaftar yang masih berstatus Daftar Tunggu saat periode pendaftaran ditutup berstatus **Tidak Tertampung** dan **mendapat refund**.
- Refund dari pembayaran formulir yang belum terverifikasi baru diproses setelah **Admin Keuangan mencocokkan bukti dengan mutasi rekening**.
- Dengan keputusan ini, **seluruh asumsi User Stories (A-01 s.d. A-18) telah dikonfirmasi**.
## K-23 — Kode Unik, Format NPD, Jatuh Tempo & Refund Tidak Ditemukan
- Kode unik pembayaran bernilai **001–999**, dibangkitkan **secara acak**, dan tidak boleh kembar di antara tagihan terbuka dengan nominal dasar yang sama.
- Polibatam **belum memiliki format NPD resmi**. Format sementara (*dummy*): `MC-{TAHUN}-{KODE_PEMINATAN}-{NO_URUT_4_DIGIT}`, contoh `MC-2026-AKT-0001`.
- Jatuh tempo tagihan semester adalah **H-7 sebelum semester dimulai**.
- Pembayaran formulir belum terverifikasi yang **tidak ditemukan di mutasi rekening tidak ikut di-*refund***, dan alasannya dicatat.
## K-24 — Jatuh Tempo Manual & Peringatan Rollover
- Tagihan semester yang terbit **pada atau setelah H-7** sebelum semester dimulai diberi jatuh tempo **secara manual**.
- Sistem memberi **peringatan kepada Super Admin** jika *rollover* belum dijalankan pada **H-14** sebelum semester dimulai.
## K-25 — Penetap Jatuh Tempo Manual
- Jatuh tempo manual **hanya ditetapkan oleh Super Admin**.
- Dengan keputusan ini, **seluruh asumsi FR/BR (A-19 s.d. A-25) telah dikonfirmasi**.
## K-26 — Infrastruktur, Retensi & Integrasi
- Proyek **masih dikembangkan secara lokal**. Lokasi *hosting* produksi (*on-premise* atau *cloud*) **belum ditetapkan**.
- Polibatam **belum memiliki kebijakan retensi data**. Retensi 5 tahun dipakai sementara. Pengecualian: arsip periode disimpan permanen (K-38).
- Akun Polibatam Verify **tidak terhubung dengan SSO Polibatam**. Autentikasi dikelola mandiri.
- **Layanan email/notifikasi institusi belum tersedia.** Layanan email produksi wajib disiapkan sebelum *go-live*.
## K-27 — Tahapan Penyedia Email
1. **Development lokal:** Mailpit (*mail catcher*, email tidak benar-benar terkirim).
2. **Demo / pilot terbatas:** Brevo (gratis 300 email/hari) atau Resend (gratis 100 email/hari, wajib domain sendiri).
3. **Produksi:** SMTP email institusi Polibatam (domain `polibatam.ac.id`) atau paket berbayar dengan kuota yang mencukupi beban puncak (±40.000 email per periode).
- Pergantian penyedia dilakukan lewat konfigurasi tanpa mengubah kode (NFR-44).
## K-28 — Tumpukan Teknologi
- **Backend:** PHP 8.4 + **Laravel 13**, bersifat **REST API** (`/api/v1`) untuk aplikasi mobile.
- **Web:** **Blade + Tailwind CSS**, memakai Service layer yang sama dengan API.
- **Mobile:** **Flutter (Dart)**.
- **Basis data:** **MySQL 9.7 LTS** (dipilih LTS, bukan rilis Innovation terbaru, demi masa dukungan panjang).
- **Server & lingkungan lokal:** **Nginx** + PHP-FPM, **Laragon**.
- **Pengujian API:** **Postman** (ditambah Newman untuk otomatisasi).
- Tambahan service/package yang disetujui tercatat di `claude/arsitektur-teknologi.md`.
## K-29 — Ruang Lingkup Aplikasi Mobile & Server Produksi
- Aplikasi mobile Flutter **khusus untuk Admin SILAU dan Admin Keuangan**, bertujuan **mempermudah verifikasi** (berkas dan pembayaran).
- Calon Peserta / Peserta Didik, Admin Track, dan Super Admin memakai **web (Blade)**.
- REST API v1 hanya membuka *endpoint* verifikasi untuk kedua *role* tersebut.
- Produksi / *deploy* menggunakan **server Linux**.
## K-30 — Detail Aplikasi Mobile Admin
- Token mobile berlaku **12 jam**, diterbitkan setelah 2FA (TOTP), dan dicabut saat logout, akun dinonaktifkan, atau *role* berubah.
- **Tanpa notifikasi *push***. Notifikasi hanya tampil **di dalam aplikasi**.
- Proses refund serta rekap dan ekspor laporan Admin Keuangan **cukup di web**.
- Kunci PIN/biometrik saat aplikasi dibuka kembali **tidak diperlukan**.
- Dengan keputusan ini, **seluruh asumsi arsitektur yang memengaruhi ruang lingkup (A-33, A-34, A-36, A-37, A-39, A-40) telah diputuskan**.
## K-31 — Formulir, NIK & Refund (ERD)
- *Field* formulir pendaftaran: **nama lengkap, NIK, tempat & tanggal lahir, jenis kelamin, no. HP, alamat, asal sekolah, tahun lulus**. Tidak ada *field* tambahan.
- **NIK disimpan terenkripsi.** Untuk pencarian dan pengecekan duplikat dipakai `nik_hash` (HMAC), sehingga NIK tidak perlu didekripsi.
- **Satu pendaftaran dapat memiliki lebih dari satu refund**, tergantung banyaknya biaya yang tidak terpakai atau perlu dikembalikan sesuai persyaratan. Satu tagihan hanya boleh dikembalikan satu kali.
## K-32 — Reset Password, Jatuh Tempo Manual & Volume
- Saat reset password, sistem **menampilkan pesan "Email tidak terdaftar"** jika email tidak ditemukan, agar pengguna yang salah ketik langsung tahu. Risiko pengecekan email terdaftar diterima dan dikurangi dengan *rate limit* (NFR-08).
- Jatuh tempo manual **boleh lebih awal dari tanggal penetapan**, menyesuaikan kesepakatan bersama. Tagihan seperti itu langsung dihitung sebagai lewat jatuh tempo.
- Target volume **dikonfirmasi**: ≥ 500 pengguna bersamaan, ≥ 5.000 pendaftaran per periode, ≥ 5.000 peserta per *rollover* (A-29).
- Dengan keputusan ini, **seluruh asumsi NFR yang menunggu konfirmasi (A-29) dan asumsi Use Case (A-44, A-45) telah diputuskan**.
## K-33 — 2FA Wajib untuk Admin & Sinkronisasi Dokumen
- **2FA (TOTP) wajib** untuk seluruh akun admin (Admin SILAU, Admin Keuangan, Admin Track, Super Admin), di web maupun mobile. Admin mendaftarkan *authenticator* saat aktivasi akun. Ketentuan ini menegaskan K-30 (token mobile diterbitkan setelah TOTP).
- Seluruh dokumen disinkronkan dengan K-29 s.d. K-33:
  - *User Stories*: US-30 (verifikasi lewat aplikasi mobile) dan AC baru pada US-03, US-04, US-19, US-20, US-27.
  - *FR & BR*: BR-59 s.d. BR-63 dan FR-59 s.d. FR-62 (2FA wajib, kanal mobile, *optimistic locking* 409, NIK terenkripsi, refund bertahap).
  - *NFR*: NFR-09 menjadi *Must*, NFR-06 mencakup NIK, NFR-47 untuk token mobile.
## K-34 — Pembatalan Peserta Didik, Refund Ditolak & Konfirmasi Desain
- Saat peminatan dibatalkan, **Peserta Didik yang sudah memiliki NPD ikut berstatus Dibatalkan** (A-48). Diterapkan pada BR-24, FR-41, dan tabel transisi status Peserta Didik.
- Jika **seluruh rincian refund dikecualikan** karena pembayaran tidak ditemukan di mutasi rekening (nominal Rp0), refund berstatus **Ditolak** dengan alasan, tanpa bukti transfer, dan penerima diberi notifikasi (A-49). Diterapkan pada BR-51, FR-44, ERD (`refunds.status`, `alasan_ditolak`), dan class diagram.
- Nama kelas dan metode di class diagram **disetujui** sebagai acuan implementasi dan boleh disesuaikan tanpa mengubah perilaku (A-46).
- Notifikasi dikirim **setelah transaksi berhasil** (*after commit*) (A-47).
## K-35 — Konvensi Sequence Diagram
- Nama rute web dan nama controller di sequence diagram yang tidak tercantum di Dokumen Arsitektur **bersifat ilustratif** dan boleh disesuaikan saat implementasi, selama urutan interaksinya tetap sama (A-50).
- *Rollover* semester dijalankan sebagai **job antrean** (`JalankanRollover`). Super Admin langsung menerima respons "sedang diproses", dan hasilnya dikirim lewat notifikasi (A-51).
## K-36 — Keamanan Basis Data
- *Audit log* ditambah kolom **`hasil`** (`berhasil` / `gagal` / `ditolak`) untuk mencatat aksi berhasil maupun gagal, termasuk login admin gagal, TOTP gagal, akses ditolak (403), konflik verifikasi (409), dan transaksi yang gagal (A-52).
- `nik_hash` memakai kunci HMAC terpisah **`NIK_HMAC_KEY`**, bukan `APP_KEY`, dan ketentuannya dicantumkan di Dokumen Keamanan Basis Data §9.3 (A-53).
- Penilaian risiko memakai **matriks 5×5** (Likelihood × Impact) (A-54).
- Pengujian keamanan basis data dan pengambilan *evidence* dilakukan **di lingkungan lokal (Laragon) oleh pengembang** sebelum *deploy* ke Linux. Kontrol khusus server (BL-07, BL-17, BL-21) diverifikasi ulang setelah *deploy*.
- Perubahan dari Dokumen Keamanan Basis Data diterapkan ke ERD, Dokumen Arsitektur, NFR (termasuk **NFR-48** akun DB *least privilege*), dan Class Diagram, termasuk `updated_at` `TIMESTAMP(6)` (A-55) dan VIEW laporan melalui akun `pv_report` (A-56).
## K-37 — Draf, Data Pendaftaran, Ekspor & Aksi Massal (21 November 2026)
Keputusan ini muncul dari peninjauan desain referensi halaman admin "Data Pendaftaran".
- **Draf privat:** pendaftaran berstatus Draf hanya dapat diakses pemiliknya dan tidak masuk antrean, daftar data, laporan, maupun ekspor admin. Admin hanya boleh melihat **jumlah** Draf sebagai angka (BR-64).
- **Draf saat periode ditutup:** Draf otomatis menjadi **Dibatalkan** dengan alasan tercatat, formulir terkunci, dan pemilik diberi notifikasi. Tidak ada tagihan atau refund karena tagihan formulir baru terbit saat formulir diajukan (BR-65, FR-25). Status Dibatalkan yang sudah ada dipakai agar tidak perlu menambah nilai status baru di ERD.
- **Daftar Data Pendaftaran:** ditambahkan halaman daftar seluruh pendaftaran yang sudah diajukan (semua status kecuali Draf), terpisah dari antrean verifikasi FR-18 (FR-63, US-31, UC-33).
- **Ekspor data pendaftaran:** Excel/PDF untuk hasil filter atau baris terpilih, NIK tersamar, tercatat di *audit log*, **hanya untuk Admin SILAU dan Super Admin** (FR-64, BR-67, A-58 disetujui).
- **Aksi massal terbatas:** keputusan verifikasi berkas dan pembayaran tetap **satu per satu**; aksi massal hanya untuk aksi yang tidak mengubah status, yaitu ekspor baris terpilih (BR-66).
- **Nomor pendaftaran:** `PV-{TAHUN}-{NO_URUT_6_DIGIT}` (contoh `PV-2026-000418`), dibangkitkan saat Draf pertama kali disimpan dan tidak berubah (BR-68, A-57 disetujui). Kolom `pendaftarans.nomor_pendaftaran` ditambahkan di ERD.
- **Peminatan Teknik Elektro** untuk data contoh/preview memakai nama **"Umum"**, karena pedoman hanya menulis "Umum / Sesuai Skema".
- Diterapkan pada: `srs-fr-br.md` (BR-58, BR-64–BR-68, FR-11, FR-18, FR-20, FR-25, FR-31, FR-32, FR-63, FR-64, RBAC, transisi status), `user-stories.md` (glosarium, AC-04.1, AC-04.8, AC-04.9, AC-09.5, AC-12.6, AC-28.1, US-31), `use-case.md` (UC-05, UC-08, UC-12, UC-13, UC-29, UC-33 baru), `activity-diagram.md` (AD-02, AD-03, AD-04, AD-06, AD-09 baru), `sequence-diagram.md` (SD-05 dengan `SimpanDraf`, SD-08, SD-12, SD-13, SD-29, SD-33 baru), `erd.md` (`nomor_pendaftaran`, penyaringan Draf, VIEW `v_data_pendaftaran`, aksi audit baru), `class-diagram.md` (atribut dan metode `Pendaftaran`, `SimpanDraf`, `NomorPendaftaranGenerator`, `DataPendaftaranQuery`, `EksporDataPendaftaran`, `PendaftaranPolicy`), `arsitektur-teknologi.md` (rute Data Pendaftaran, otorisasi berlapis, *job* ekspor), dan `Skills/design-memory.md`.
## K-38 — File Ekspor Sementara & Arsip Periode Permanen (21 November 2026)
Keputusan ini menjawab pertanyaan apakah aplikasi tetap dapat menyimpan arsip bila file ekspor dihapus otomatis.
- **File ekspor sementara (A-59 disetujui):** file hasil ekspor data pendaftaran dan tautan unduhannya berlaku **24 jam**, lalu file dihapus otomatis oleh perintah terjadwal `ekspor:bersihkan` (setiap jam). Penghapusan hanya menyentuh file ekspor, bukan data di basis data dan bukan arsip periode (BR-67, NFR-50). Admin dapat mengekspor ulang kapan saja; hasil ekspor ulang menampilkan kondisi data saat itu.
- **Arsip periode (A-60 disetujui):** saat periode pendaftaran ditutup, sistem otomatis membuat **satu salinan beku** data pendaftaran periode itu (tanpa Draf, NIK tersamar, status per waktu penutupan) beserta ringkasan per peminatan dan status (BR-69, FR-65).
- **Retensi arsip: permanen.** Arsip tidak dapat diubah, ditimpa, atau dihapus melalui aplikasi, dikecualikan dari retensi sementara 5 tahun (K-26, NFR-13), disimpan di *disk* `arsip` yang *write-once*, dilindungi *checksum* SHA-256, dan ikut *backup* ke lokasi terpisah (NFR-24, NFR-49).
- **Akses arsip:** daftar dan unduhan hanya untuk **Admin SILAU dan Super Admin**; setiap unduhan dicatat di *audit log*. Super Admin dapat memicu ulang arsip yang gagal dibuat (FR-66, US-32, UC-34).
- **Menunggu konfirmasi (A-61):** arsip berformat `.xlsx` saja, dan perubahan status setelah penutupan (mis. NPD terbit, pembatalan peminatan) tidak mengubah arsip.
- Diterapkan pada: `srs-fr-br.md` (BR-58, BR-64, BR-67, BR-69 baru, FR-25, FR-64, FR-65–FR-66 baru, RBAC, entitas ArsipPeriode, transisi §4.6), `srs-nfr.md` (NFR-13, NFR-19, NFR-21, NFR-22, NFR-24, NFR-25, NFR-39, NFR-40, NFR-49–NFR-50 baru), `user-stories.md` (glosarium, AC-04.8, AC-28.1, AC-31.9, US-32 baru), `use-case.md` (UC-29, UC-30, UC-33, UC-34 baru), `activity-diagram.md` (AD-06, AD-09, AD-10 baru), `sequence-diagram.md` (SD-29, SD-30, SD-33, SD-34 baru), `erd.md` (`arsip_periodes`, aksi audit, `pengaturans`), `class-diagram.md` (`ArsipPeriode`, `StatusArsip`, `BuatArsipPeriode`, `UnduhArsipPeriode`, `BersihkanEkspor`, `ArsipPeriodePolicy`), dan `arsitektur-teknologi.md` (*disk* `ekspor`/`arsip`, rute arsip, *scheduler*, *job*, *backup*).
- **Belum diterapkan:** `keamanan-basis-data.md` (GRANT `pv_app` untuk `arsip_periodes` tanpa `DELETE`, *trigger* `trg_arsip_periodes_kunci`, uji timpa/hapus arsip) dan `Skills/design-memory.md` (menu Arsip Periode).
 
## K-39 — Satu Halaman Login, Halaman Tambahan & Formulir Bertab (21 November 2026)
Keputusan ini muncul saat menyusun daftar halaman web (`claude/daftar-halaman-web.md`).
- Login admin dan peserta memakai **satu halaman `/login`**. Setelah login, pengguna diarahkan ke dasbor sesuai *role*; admin melewati verifikasi 2FA lebih dulu (HW-07, HW-08).
- Halaman **HW-12** (profil & keamanan akun), **HW-47** (pengaturan sistem), dan **HW-48** (kriteria *checklist* dokumen) **disetujui** masuk daftar halaman.
- Formulir pendaftaran (**HW-15**) dibuat sebagai **satu halaman dengan tiga tab**: Data Diri · Berkas · Tinjau & Ajukan. Pemilihan track & peminatan tetap halaman terpisah (HW-14) yang membuat Draf dan nomor pendaftaran.
- Diterapkan pada: `daftar-halaman-web.md` dan desain Figma (halaman "🖥️ Halaman Web (HW)").
## K-40 — Rekening Institusi, Data Pengiriman & Kartu Pendaftar (21 November 2026)
Keputusan ini muncul dari peninjauan desain Figma tim (`claude/review-desain-figma.md`).
- **Halaman Rekening Institusi diperlukan.** Admin Keuangan dan Super Admin mengelola rekening tujuan transfer: tambah, ubah, aktifkan, nonaktifkan; satu rekening aktif per jenis tagihan; rekening yang pernah dipakai tidak dapat dihapus; perubahan tercatat di *audit log* (BR-70, FR-67, HW-56).
- Tagihan menyimpan ***snapshot* rekening tujuan** saat terbit, sehingga penggantian rekening tidak mengubah instruksi transfer tagihan lama. Tagihan tidak terbit bila belum ada rekening aktif (BR-71, FR-26, FR-27).
- **Data pengiriman pada unggah bukti dipertahankan:** tanggal transfer, nominal yang ditransfer, bank pengirim, dan nama pengirim wajib diisi; selisih nominal hanya memunculkan peringatan (BR-72, FR-29, FR-31).
- **Unduh kartu pendaftar dipertahankan:** PDF berisi nomor pendaftaran, nama, track, peminatan, periode, waktu pengajuan, dan status terkini; tersedia setelah formulir Diajukan, tanpa NIK (BR-73, FR-68).
- **Menunggu konfirmasi:** A-62 (rekening per jenis tagihan, nomor rekening institusi tidak dienkripsi) dan A-63 (kartu dibangkitkan saat diunduh, tanpa QR).
- Diterapkan pada: `srs-fr-br.md` (BR-58, BR-70–BR-73, FR-26, FR-27, FR-29, FR-31, FR-67–FR-68, RBAC, entitas `RekeningInstitusi`/`Tagihan`/`BuktiPembayaran`, transisi §4.4 & §4.7, keterlacakan, A-62–A-63), `daftar-halaman-web.md` (HW-56, isi HW-13, HW-18, HW-29), dan desain Figma (HW-56).
- **Belum diterapkan:** `user-stories.md` (US baru Rekening Institusi, AC data pengiriman pada US-06, AC kartu pendaftar pada US-07), `use-case.md`, `activity-diagram.md`, `sequence-diagram.md`, `erd.md` (tabel `rekening_institusis`, kolom *snapshot* di `tagihans`, kolom data pengiriman di `bukti_pembayarans`), `class-diagram.md`, `arsitektur-teknologi.md` (rute `/admin/rekening`), `keamanan-basis-data.md`, dan `srs-nfr.md`.
## K-41 — Desain Mobile Tim & Fast Verify (22 November 2026)
Keputusan ini muncul dari peninjauan file Figma *Microcredential – Mobile (Copy)* milik tim.
- **Gaya visual mobile mengikuti desain tim:** token warna dan tipografi yang sama dengan web, margin 20 px (area konten 350 px), kartu ringkasan dua kolom 169 px, tombol 48 px, navigasi bawah 76 px dengan **3 tab: Dashboard · Antrean · Profil** (label tab Antrean: "Pendaftaran" untuk Admin SILAU, "Pembayaran" untuk Admin Keuangan). Notifikasi *in-app* dibuka dari ikon lonceng di header.
- **Fast Verify diadopsi dengan penyesuaian** (BR-74, FR-69):
  - Admin SILAU: satu kartu = satu dokumen + *checklist*; geser kanan = Valid (hanya bila semua kriteria dicentang), geser kiri = Ditolak + alasan; ringkasan dan **Selesaikan** per pendaftar.
  - Admin Keuangan: satu kartu = satu tagihan; geser kanan → konfirmasi Tandai Lunas, geser kiri → alasan penolakan; tagihan yang belum lolos gerbang ganda tidak masuk Fast Verify.
  - Tetap satu keputusan per objek (bukan aksi massal), dengan *optimistic locking* dan *audit log*.
- **Tidak diadopsi dari desain tim** karena bertentangan dengan dokumen: keputusan Terima/Tolak/Revisi untuk seluruh pendaftaran (diganti keputusan per dokumen, FR-20–21), menu refund dan ekspor di mobile (K-30), ubah kata sandi di mobile (dilakukan di web).
- **Menunggu konfirmasi:** A-64 (alasan penolakan dari kriteria/alasan baku + catatan).
- Diterapkan pada: `srs-fr-br.md` (BR-74, FR-69, keterlacakan US-30, A-64), `daftar-halaman-mobile.md` (37 layar HM-01 – HM-37), dan desain Figma (halaman "📱 Aplikasi Mobile (HM)", komponen `PV/M *`).
- **Belum diterapkan:** `user-stories.md` (AC Fast Verify pada US-30), `use-case.md`, `activity-diagram.md`, `sequence-diagram.md`, `arsitektur-teknologi.md` (§11 struktur Flutter: modul `fast_verify`).
## K-42 — Kuota Maksimum & Daftar Tunggu Dihapus, Kuota Tidak Ditampilkan (26 November 2026)
Keputusan pemilik proyek saat uji P03: tujuan aplikasi adalah menjaring **pendaftar sebanyak-banyaknya**.
- **Tidak ada kuota maksimum.** Pendaftar yang seluruh dokumen wajibnya Valid langsung berstatus **Masuk Kuota**. Status **Daftar Tunggu** dan **Tidak Tertampung**, urutan FIFO, promosi otomatis, serta penempatan slot atomik ditiadakan (mengubah K-15 dan K-22).
- **Informasi kuota tidak ditampilkan kepada pengunjung maupun calon peserta**: katalog, pilih peminatan, dan dasbor peserta tidak menampilkan kuota, sisa kuota, label "Kuota Penuh", atau jumlah pendaftar (BR-75). Informasi kuota hanya untuk admin.
- **Kuota minimum tetap berlaku** dan dievaluasi **saat periode pendaftaran ditutup** terhadap jumlah pendaftar **NPD Terbit**. Sistem **menandai** peminatan yang di bawah kuota minimum; **Super Admin yang memutuskan** pembatalan (tidak otomatis). Bila dibatalkan, seluruh pendaftaran pada peminatan itu menjadi Dibatalkan dan pendaftar yang sudah membayar mendapat **refund** (BR-23, BR-24, BR-47).
- Batas 7 hari pelunasan tagihan formulir setelah Masuk Kuota tetap berlaku, tanpa konsep pelepasan slot (BR-21).
- Diterapkan pada: `srs-fr-br.md` (BR-08, BR-15–BR-24, BR-47, BR-75 baru, FR-08, FR-15, FR-21–FR-25, FR-48, FR-49, RBAC kuota, transisi §4.2, refund §4.6) dan `user stories.md` (glosarium status, US-01, AC-01.4, AC-04.6, AC-07.1, AC-07.3, AC-09.3, US-11, AC-20.4, US-23, AC-24.1, AC-32.1, A-05, A-13, A-15, A-16, A-18). Nomor BR/FR/AC yang dihapus dipertahankan dengan tanda *(Dihapus, K-42)* agar rujukan lama tidak putus.
- **Belum diterapkan:** `srs-nfr.md` (NFR-27 uji ≥ 50 permintaan simultan pada slot terakhir), `erd.md` & paket SQL (`peminatan_periodes.kuota_maks`, `slot_terpakai`, CHECK `chk_kuota_rentang`/`chk_kuota_slot`; `pendaftarans.nomor_urut_tunggu`), `class-diagram.md`, `use-case.md`, `activity-diagram.md`, `sequence-diagram.md`, `daftar-halaman-web.md` (HW-02, HW-26, dasbor kuota), `design-memory.md` (status Daftar Tunggu/Tidak Tertampung), `implementasi-basis-data.md`, `keamanan-basis-data.md`, `PRD-CLAUDE-CODE.md` (uji wajib P04), dan desain Figma (frame HW-13 Daftar Tunggu/Tidak Tertampung). Lihat `doc/registry/temuan.md` T-23.
