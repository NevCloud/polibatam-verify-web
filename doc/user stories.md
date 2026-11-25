# User Stories & Acceptance Criteria — Polibatam Verify
 
Disusun berdasarkan *Pain Points*, *User Personas* (UP-01 s.d. UP-05), dan *Keputusan Klarifikasi Scope* (K-01 s.d. K-38).
 
**Prioritas** memakai MoSCoW: *Must* (wajib untuk rilis pertama), *Should* (penting, dapat menyusul), *Could* (nilai tambah).
 
---
 
## 0. Glosarium Status
 
Status di bawah ini dipakai secara konsisten di seluruh *acceptance criteria*.
 
| Entitas | Status | Keterangan |
|---|---|---|
| **Pendaftaran** | Draf | Formulir disimpan, belum diajukan. Hanya dapat diakses pemiliknya dan tidak terlihat oleh admin (K-37). |
| | Diajukan | Formulir dan berkas sudah dikirim, menunggu Admin SILAU. |
| | Perlu Revisi | Minimal satu dokumen ditolak Admin SILAU. |
| | Masuk Kuota | Seluruh dokumen wajib valid; pendaftar tinggal melunasi tagihan formulir. Tidak ada batas jumlah (K-42). |
| | Daftar Tunggu | *(Dihapus, K-42)* Tidak ada kuota maksimum. |
| | NPD Terbit | Pembayaran formulir lunas, NPD diterbitkan, akun menjadi akun Peserta Didik. |
| | Tidak Tertampung | *(Dihapus, K-42)* Tidak ada Daftar Tunggu. |
| | Dibatalkan | Peminatan dibatalkan (kuota minimum tidak tercapai atau kesepakatan terbaru, K-16), tagihan formulir tidak dilunasi dalam 7 hari setelah Masuk Kuota (K-17), atau formulir masih Draf saat periode pendaftaran ditutup (K-37). Status akhir. |
| **Dokumen** | Menunggu Verifikasi / Valid / Ditolak | Status per dokumen persyaratan. |
| **Tagihan** (formulir & semester) | Belum Bayar | Tagihan terbit, belum ada bukti pembayaran. |
| | Menunggu Verifikasi | Bukti pembayaran sudah diunggah. Unggah ulang dikunci. |
| | Ditolak | Bukti ditolak Admin Keuangan. Unggah ulang dibuka kembali. |
| | Lunas | Pembayaran tervalidasi. Status akhir. |
| | Dibatalkan | Tagihan tidak berlaku karena peminatan dibatalkan. |
| **Peserta Didik** | Belum Aktif Semester *N* | NPD sudah terbit atau semester sudah naik, tetapi tagihan semester *N* belum Lunas (K-21). |
| | Aktif Semester *N* | Tagihan semester *N* sudah Lunas. |
| | Tertahan (Tunggakan) | Tagihan semester berjalan belum Lunas saat *rollover*, sehingga tidak dinaikkan dan tidak dapat mengikuti semester selanjutnya (K-18). |
| | Selesai Masa Program | Telah menyelesaikan seluruh durasi semester peminatan. |
| | Dibatalkan | Peminatan dibatalkan setelah NPD terbit (K-34). Status akhir. |
| **Refund** | Menunggu Data Rekening / Diproses / Selesai / Ditolak | Status pengembalian dana. Ditolak berarti seluruh rincian dikecualikan karena pembayaran tidak ditemukan di mutasi (K-34). Nominal refund = seluruh biaya yang sudah dibayar tetapi tidak terpakai, termasuk biaya formulir, tanpa selisih kode unik (K-09, K-19). |
| **Arsip Periode** | Diproses / Tersedia / Gagal | Salinan beku data pendaftaran satu periode, dibuat otomatis saat periode ditutup dan disimpan permanen. Tersedia bersifat final (K-38). |
 
---
 
## Epic A — Informasi Publik & Akun
 
### US-01 — Melihat katalog peminatan
**Persona:** UP-01 · **Pain Point:** PP-01 · **Prioritas:** Must
 
> **As a** Calon Peserta, **I want to** melihat daftar track dan peminatan beserta tarif aktif dan durasi semester tanpa harus login, **so that** saya dapat memilih peminatan dan menyiapkan biaya berdasarkan informasi terbaru.
 
**Acceptance Criteria**
- **AC-01.1 (Happy path)** — **Given** periode pendaftaran sedang dibuka dan terdapat peminatan berstatus aktif, **When** pengunjung membuka halaman katalog, **Then** sistem menampilkan setiap peminatan aktif beserta track, durasi semester, tarif formulir, tarif per semester yang berlaku, dan sisa kuota.
- **AC-01.2 (Data dinamis)** — **Given** Admin Track mengubah tarif dengan tanggal berlaku efektif hari ini, **When** pengunjung memuat ulang katalog, **Then** tarif yang tampil adalah tarif versi terbaru.
- **AC-01.3 (Peminatan nonaktif)** — **Given** sebuah peminatan berstatus nonaktif, **When** katalog ditampilkan, **Then** peminatan tersebut tidak muncul.
- **AC-01.4 (Kuota tidak ditampilkan)** — **Given** pengunjung membuka katalog, **When** katalog ditampilkan, **Then** tidak ada informasi kuota, sisa kuota, maupun jumlah pendaftar, dan tombol daftar selalu aktif selama periode dibuka (K-42).
- **AC-01.5 (Di luar periode)** — **Given** tidak ada periode pendaftaran yang sedang dibuka, **When** pengunjung membuka katalog, **Then** sistem menampilkan informasi jadwal periode berikutnya (jika ada) dan tombol daftar dinonaktifkan.
### US-02 — Registrasi akun
**Persona:** UP-01 · **Pain Point:** PP-02 · **Prioritas:** Must
 
> **As a** Calon Peserta, **I want to** membuat akun dengan email dan password, **so that** saya dapat mengisi formulir pendaftaran dan memantau prosesnya.
 
**Acceptance Criteria**
- **AC-02.1 (Happy path)** — **Given** email belum terdaftar, **When** calon peserta mengisi email, password, dan konfirmasi password yang valid lalu mengirim, **Then** akun dibuat dengan *role* Calon Peserta dan tautan verifikasi dikirim ke email tersebut.
- **AC-02.2 (Email duplikat)** — **Given** email sudah terdaftar, **When** registrasi dikirim, **Then** sistem menolak dengan pesan "Email sudah terdaftar".
- **AC-02.3 (Password lemah)** — **Given** password tidak memenuhi kebijakan password (lihat NFR keamanan), **When** registrasi dikirim, **Then** sistem menolak dan menampilkan syarat password.
- **AC-02.4 (Belum verifikasi email)** — **Given** akun belum memverifikasi email, **When** pengguna login, **Then** sistem tidak mengizinkan pengisian formulir dan menawarkan pengiriman ulang tautan verifikasi.
### US-03 — Login dan reset password
**Persona:** UP-01 s.d. UP-05 · **Pain Point:** PP-17 · **Prioritas:** Must
 
> **As a** pengguna sistem, **I want to** login dan mereset password yang terlupa, **so that** saya dapat mengakses fitur sesuai peran saya dengan aman.
 
**Acceptance Criteria**
- **AC-03.1 (Happy path)** — **Given** kredensial benar, **When** pengguna login, **Then** pengguna diarahkan ke dasbor sesuai *role*-nya.
- **AC-03.2 (Kredensial salah)** — **Given** password salah, **When** pengguna login, **Then** sistem menampilkan pesan umum "Email atau password salah" tanpa menyebut bagian mana yang salah.
- **AC-03.3 (Pembatasan percobaan)** — **Given** terjadi 5 kali gagal login berturut-turut, **When** pengguna mencoba lagi, **Then** akun dikunci sementara selama 15 menit.
- **AC-03.4 (Reset password)** — **Given** email terdaftar, **When** pengguna meminta reset password, **Then** sistem mengirim tautan reset yang berlaku 60 menit dan hanya dapat dipakai sekali.
- **AC-03.5 (Email tidak terdaftar)** — **Given** email yang dimasukkan tidak terdaftar, **When** pengguna meminta reset password, **Then** sistem menampilkan pesan "Email tidak terdaftar" agar pengguna dapat memperbaiki salah ketik (K-32).
- **AC-03.6 (2FA admin wajib)** — **Given** pengguna ber-*role* Admin SILAU, Admin Keuangan, Admin Track, atau Super Admin, **When** ia login di web maupun mobile, **Then** sistem baru membuat sesi atau token setelah kode TOTP yang valid dimasukkan (K-33).
---
 
## Epic B — Pendaftaran Calon Peserta
 
### US-04 — Mengisi formulir dan memilih peminatan
**Persona:** UP-01 · **Pain Point:** PP-01 · **Prioritas:** Must
 
> **As a** Calon Peserta, **I want to** mengisi formulir pendaftaran dan memilih satu track serta satu peminatan, **so that** saya terdaftar pada program yang sesuai minat saya.
 
**Acceptance Criteria**
- **AC-04.1 (Simpan draf)** — **Given** calon peserta sudah login dan email terverifikasi, **When** ia mengisi sebagian formulir lalu menyimpan untuk pertama kali, **Then** pendaftaran tersimpan dengan status Draf, mendapat nomor pendaftaran unik berformat `PV-{TAHUN}-{6 digit}` yang tidak berubah, dan dapat dilanjutkan kemudian (K-37).
- **AC-04.2 (Ajukan)** — **Given** seluruh *field* wajib terisi dan seluruh dokumen wajib terunggah, **When** calon peserta mengajukan formulir, **Then** status pendaftaran menjadi Diajukan dan sistem menerbitkan tagihan formulir (lihat US-06).
- **AC-04.3 (Field wajib kosong)** — **Given** ada *field* wajib atau dokumen wajib yang belum lengkap, **When** formulir diajukan, **Then** sistem menolak dan menandai bagian yang belum lengkap.
- **AC-04.4 (Peminatan terkunci)** — **Given** status pendaftaran sudah Diajukan, **When** calon peserta mencoba mengubah track atau peminatan, **Then** sistem tidak mengizinkan perubahan.
- **AC-04.5 (Satu pendaftaran aktif)** — **Given** akun sudah memiliki pendaftaran yang belum berstatus akhir pada periode berjalan, **When** ia mencoba membuat pendaftaran baru, **Then** sistem menolak.
- **AC-04.6 (Tanpa informasi kuota)** — **Given** calon peserta memilih track dan peminatan, **When** halaman pilih peminatan ditampilkan, **Then** sistem tidak menampilkan kuota, sisa kuota, maupun jumlah pendaftar; seluruh peminatan yang memenuhi BR-07 dapat dipilih (K-42).
- **AC-04.7 (Data formulir & NIK)** — **Given** calon peserta mengisi formulir, **When** formulir disimpan, **Then** sistem menyimpan nama lengkap, NIK, tempat & tanggal lahir, jenis kelamin, no. HP, alamat, asal sekolah, dan tahun lulus; NIK disimpan terenkripsi dan tampil tersamar di antrean admin (K-31).
- **AC-04.8 (Draf privat)** — **Given** pendaftaran berstatus Draf, **When** admin dengan *role* apa pun membuka antrean verifikasi, daftar data pendaftaran, laporan, ekspor, atau arsip periode, **Then** Draf tersebut tidak tampil dan isinya tidak dapat dibuka; admin hanya melihat jumlah Draf sebagai angka (K-37).
- **AC-04.9 (Draf saat periode ditutup)** — **Given** pendaftaran masih berstatus Draf, **When** periode pendaftaran ditutup, **Then** status menjadi Dibatalkan dengan alasan "Periode pendaftaran ditutup sebelum formulir diajukan", formulir terkunci (hanya dapat dilihat), pemilik menerima notifikasi, dan tidak ada tagihan maupun refund yang dibuat (K-37).
### US-05 — Mengunggah berkas persyaratan
**Persona:** UP-01 · **Pain Point:** PP-07 · **Prioritas:** Must
 
> **As a** Calon Peserta, **I want to** mengunggah setiap dokumen persyaratan pada slot dokumen masing-masing, **so that** Admin SILAU dapat memeriksanya tanpa saya mengirim berkas lewat kanal lain.
 
**Acceptance Criteria**
- **AC-05.1 (Happy path)** — **Given** formulir berstatus Draf, **When** calon peserta mengunggah file berformat PDF, JPG, atau PNG berukuran ≤ 2 MB pada slot dokumen, **Then** file tersimpan dan dapat dipratinjau.
- **AC-05.2 (Format/ukuran salah)** — **Given** file berformat lain atau melebihi batas ukuran, **When** diunggah, **Then** sistem menolak dengan pesan yang menyebutkan format dan ukuran yang diizinkan.
- **AC-05.3 (Dokumen wajib vs opsional)** — **Given** slot dokumen terdiri dari Ijazah/SKL/Rapor, SKHU/Nilai, dan KTP (wajib) serta Bukti Pendaftaran Polibatam (opsional), **When** formulir diajukan tanpa dokumen opsional, **Then** pengajuan tetap diterima.
- **AC-05.4 (Ganti file sebelum diajukan)** — **Given** formulir masih berstatus Draf, **When** calon peserta mengganti file pada suatu slot, **Then** file lama digantikan.
### US-06 — Membayar dan mengunggah bukti pembayaran per tagihan
**Persona:** UP-01 · **Pain Point:** PP-04, PP-05, PP-10, PP-13 · **Prioritas:** Must
 
> **As a** Calon Peserta / Peserta Didik, **I want to** melihat nominal pembayaran beserta kode uniknya lalu mengunggah bukti transfer pada tagihan yang dimaksud, **so that** pembayaran saya langsung terhubung ke tagihan yang benar dan mudah divalidasi.
 
**Acceptance Criteria**
- **AC-06.1 (Tagihan formulir terbit)** — **Given** formulir baru saja diajukan, **When** sistem menerbitkan tagihan formulir, **Then** tagihan memuat kode referensi, nominal = tarif formulir peminatan yang dipilih (versi yang berlaku) + kode unik 3 digit (K-20), nomor rekening tujuan, dan status Belum Bayar.
- **AC-06.2 (Unggah bukti)** — **Given** tagihan berstatus Belum Bayar atau Ditolak, **When** pengguna memilih tagihan tersebut dan mengunggah bukti transfer yang valid, **Then** bukti tertaut ke tagihan dan status tagihan menjadi Menunggu Verifikasi.
- **AC-06.3 (Unggah dikunci)** — **Given** tagihan berstatus Menunggu Verifikasi atau Lunas, **When** pengguna mencoba mengunggah bukti, **Then** opsi unggah tidak tersedia.
- **AC-06.4 (Hash duplikat)** — **Given** file bukti yang identik (*hash* sama) sudah pernah diunggah untuk tagihan mana pun, **When** file itu diunggah lagi, **Then** sistem menolak unggahan dan menampilkan pesan bahwa bukti sudah pernah digunakan.
- **AC-06.5 (Selisih kode unik)** — **Given** peserta mentransfer sesuai nominal tagihan termasuk kode unik, **When** pembayaran dinyatakan Lunas, **Then** selisih kode unik tidak dicatat sebagai kelebihan bayar dan tidak dikembalikan.
### US-07 — Memantau status pendaftaran
**Persona:** UP-01 · **Pain Point:** PP-02 · **Prioritas:** Must
 
> **As a** Calon Peserta, **I want to** melihat tahap pendaftaran saya dan menerima notifikasi setiap kali statusnya berubah, **so that** saya tidak perlu menanyakan status ke admin.
 
**Acceptance Criteria**
- **AC-07.1 (Tracker)** — **Given** pendaftaran sudah Diajukan, **When** calon peserta membuka dasbor, **Then** sistem menampilkan *progress tracker*: Formulir Diajukan → Verifikasi Berkas → Masuk Kuota → Verifikasi Pembayaran Formulir → NPD Terbit, dengan tahap saat ini disorot.
- **AC-07.2 (Notifikasi)** — **Given** status pendaftaran, status dokumen, atau status tagihan berubah, **When** perubahan tersimpan, **Then** sistem mengirim notifikasi email dan notifikasi di dalam aplikasi berisi status baru.
- **AC-07.3 (Status khusus)** — **Given** status pendaftaran menjadi Dibatalkan (termasuk karena peminatan dibatalkan), **When** calon peserta membuka dasbor, **Then** sistem menampilkan status tersebut beserta penjelasan dan langkah berikutnya (refund bila sudah membayar).
### US-08 — Merevisi dokumen yang ditolak
**Persona:** UP-01 · **Pain Point:** PP-03 · **Prioritas:** Must
 
> **As a** Calon Peserta, **I want to** melihat alasan penolakan per dokumen dan mengunggah ulang hanya dokumen yang ditolak, **so that** proses revisi cepat tanpa mengulang dokumen yang sudah valid.
 
**Acceptance Criteria**
- **AC-08.1 (Lihat alasan)** — **Given** status pendaftaran Perlu Revisi, **When** calon peserta membuka detail berkas, **Then** setiap dokumen Ditolak menampilkan alasan penolakan dari Admin SILAU.
- **AC-08.2 (Unggah parsial)** — **Given** satu dokumen Ditolak dan dokumen lain Valid, **When** calon peserta mengunggah ulang dokumen yang ditolak, **Then** hanya dokumen tersebut yang berubah menjadi Menunggu Verifikasi, dokumen Valid tetap Valid, dan status pendaftaran kembali menjadi Diajukan.
- **AC-08.3 (Dokumen valid terkunci)** — **Given** dokumen berstatus Valid, **When** calon peserta mencoba menggantinya, **Then** sistem tidak mengizinkan.
- **AC-08.4 (Riwayat)** — **Given** sebuah dokumen pernah ditolak lebih dari sekali, **When** detail dokumen dibuka, **Then** riwayat penolakan beserta alasan dan waktunya tetap tersimpan.
---
 
## Epic C — Verifikasi Berkas (Admin SILAU)
 
### US-09 — Antrean verifikasi berkas
**Persona:** UP-02 · **Pain Point:** PP-07 · **Prioritas:** Must
 
> **As an** Admin SILAU, **I want to** melihat seluruh pendaftaran yang perlu diverifikasi dalam satu antrean dan membuka dokumennya langsung di aplikasi, **so that** tidak ada berkas yang terlewat dan verifikasi lebih cepat.
 
**Acceptance Criteria**
- **AC-09.1 (Antrean)** — **Given** terdapat pendaftaran berstatus Diajukan, **When** Admin SILAU membuka antrean, **Then** sistem menampilkan pendaftaran tersebut diurutkan dari waktu pengajuan terlama, dengan filter periode, track, peminatan, dan status.
- **AC-09.2 (Pratinjau)** — **Given** Admin SILAU membuka sebuah pendaftaran, **When** ia memilih dokumen, **Then** dokumen tampil di *document viewer* tanpa harus diunduh.
- **AC-09.3 (Info kuota)** — **Given** detail pendaftaran dibuka admin, **When** halaman tampil, **Then** sistem menampilkan kuota minimum dan jumlah NPD Terbit peminatan yang dipilih pendaftar (informasi admin, BR-75).
- **AC-09.4 (Akses)** — **Given** pengguna tanpa *role* Admin SILAU atau Super Admin, **When** ia mengakses antrean verifikasi berkas, **Then** sistem menolak akses (HTTP 403).
- **AC-09.5 (Tanpa verifikasi massal)** — **Given** Admin SILAU memilih beberapa pendaftaran di antrean atau daftar data, **When** ia mencari aksi massal, **Then** sistem tidak menyediakan aksi Valid/Ditolak massal; keputusan hanya dapat diambil satu per satu dari halaman detail dengan *checklist* (K-37).
### US-10 — Memverifikasi dokumen dengan checklist
**Persona:** UP-02 · **Pain Point:** PP-08 · **Prioritas:** Must
 
> **As an** Admin SILAU, **I want to** memverifikasi setiap dokumen menggunakan *checklist* standar dan memberi alasan saat menolak, **so that** keputusan konsisten antarverifikator dan dapat diaudit.
 
**Acceptance Criteria**
- **AC-10.1 (Checklist)** — **Given** Admin SILAU membuka sebuah dokumen, **When** panel verifikasi tampil, **Then** sistem menampilkan *checklist* kriteria sesuai jenis dokumen.
- **AC-10.2 (Valid)** — **Given** seluruh kriteria terpenuhi, **When** Admin SILAU menandai dokumen Valid, **Then** status dokumen menjadi Valid.
- **AC-10.3 (Tolak tanpa alasan)** — **Given** Admin SILAU menolak dokumen, **When** alasan penolakan dikosongkan, **Then** sistem tidak menyimpan keputusan dan meminta alasan diisi.
- **AC-10.4 (Perlu revisi)** — **Given** seluruh dokumen sudah diputuskan dan minimal satu Ditolak, **When** Admin SILAU menyelesaikan verifikasi, **Then** status pendaftaran menjadi Perlu Revisi.
- **AC-10.5 (Log)** — **Given** keputusan verifikasi disimpan, **When** tercatat, **Then** sistem menyimpan identitas verifikator, waktu, keputusan, dan alasan ke *audit log*.
### US-11 — Penempatan kuota setelah berkas valid
**Persona:** UP-02, UP-04 · **Pain Point:** PP-09, PP-18 · **Prioritas:** Must
 
> **As an** Admin SILAU, **I want** pendaftar otomatis berstatus Masuk Kuota ketika seluruh berkasnya valid, **so that** Admin Keuangan tahu pendaftar mana yang siap diverifikasi pembayarannya.
 
**Acceptance Criteria**
- **AC-11.1 (Masuk kuota)** — **Given** seluruh dokumen wajib berstatus Valid, **When** Admin SILAU menyelesaikan verifikasi, **Then** status pendaftaran menjadi Masuk Kuota tanpa memeriksa batas jumlah (K-42).
- **AC-11.2 (Daftar tunggu)** — *(Dihapus, K-42)* Tidak ada kuota maksimum.
- **AC-11.3 (Konkurensi slot terakhir)** — *(Dihapus, K-42)* Tidak ada slot terbatas.
- **AC-11.4 (Batal karena tidak bayar)** — **Given** pendaftaran berstatus Masuk Kuota dan tagihan formulir masih Belum Bayar atau Ditolak, **When** 7 hari berlalu sejak Masuk Kuota, **Then** status pendaftaran otomatis menjadi Dibatalkan. Tagihan berstatus Menunggu Verifikasi tidak ikut dibatalkan.
- **AC-11.5 (Promosi daftar tunggu)** — *(Dihapus, K-42)*
- **AC-11.6 (Periode ditutup)** — *(Dihapus, K-42)* Penandaan kuota minimum saat periode ditutup diatur di US-18.
---
 
## Epic D — Verifikasi Pembayaran & Tagihan (Admin Keuangan)
 
### US-12 — Memverifikasi pembayaran formulir dan menerbitkan NPD
**Persona:** UP-03 · **Pain Point:** PP-09, PP-10 · **Prioritas:** Must
 
> **As an** Admin Keuangan, **I want to** memverifikasi pembayaran formulir pendaftar yang sudah Masuk Kuota sehingga NPD terbit otomatis, **so that** NPD hanya terbit setelah berkas dan pembayaran keduanya valid.
 
**Acceptance Criteria**
- **AC-12.1 (Antrean)** — **Given** tagihan formulir berstatus Menunggu Verifikasi, **When** Admin Keuangan membuka antrean pembayaran formulir, **Then** hanya tagihan milik pendaftaran berstatus Masuk Kuota yang dapat diverifikasi; sisanya tampil dengan keterangan "Menunggu verifikasi berkas".
- **AC-12.2 (Pencocokan)** — **Given** Admin Keuangan membuka sebuah tagihan, **When** detail tampil, **Then** sistem menampilkan kode referensi, nominal termasuk kode unik, dan pratinjau bukti transfer.
- **AC-12.3 (Lunas → NPD)** — **Given** pendaftaran Masuk Kuota dan tagihan formulir Menunggu Verifikasi, **When** Admin Keuangan menandai Lunas, **Then** tagihan menjadi Lunas, sistem menerbitkan NPD unik, status pendaftaran menjadi NPD Terbit, *role* akun berubah menjadi Peserta Didik, dan peserta menerima notifikasi berisi NPD.
- **AC-12.4 (Gerbang ganda)** — **Given** pendaftaran belum berstatus Masuk Kuota, **When** ada upaya menandai tagihan formulir Lunas, **Then** sistem menolak.
- **AC-12.5 (NPD unik)** — **Given** NPD diterbitkan, **When** disimpan, **Then** NPD tidak boleh sama dengan NPD lain dan tidak dapat diubah.
- **AC-12.6 (Tanpa verifikasi massal)** — **Given** Admin Keuangan memilih beberapa tagihan di antrean, **When** ia mencari aksi massal, **Then** sistem tidak menyediakan aksi Lunas/Ditolak massal; setiap tagihan diputuskan satu per satu dari halaman detail (K-37).
### US-13 — Menolak bukti pembayaran
**Persona:** UP-03 · **Pain Point:** PP-13 · **Prioritas:** Must
 
> **As an** Admin Keuangan, **I want to** menolak bukti pembayaran yang tidak sesuai beserta alasannya, **so that** peserta tahu harus memperbaiki apa dan kerugian akibat bukti tidak valid dapat dicegah.
 
**Acceptance Criteria**
- **AC-13.1 (Tolak)** — **Given** tagihan berstatus Menunggu Verifikasi, **When** Admin Keuangan menolak dengan alasan, **Then** status tagihan menjadi Ditolak, opsi unggah ulang dibuka untuk peserta, dan peserta menerima notifikasi berisi alasan.
- **AC-13.2 (Alasan wajib)** — **Given** Admin Keuangan menolak, **When** alasan dikosongkan, **Then** sistem tidak menyimpan penolakan.
- **AC-13.3 (Log)** — **Given** keputusan Lunas atau Ditolak disimpan, **When** tercatat, **Then** sistem menyimpan identitas Admin Keuangan, waktu, keputusan, dan alasan ke *audit log*.
- **AC-13.4 (Lunas final)** — **Given** tagihan sudah Lunas, **When** ada upaya mengubah statusnya melalui antarmuka biasa, **Then** sistem menolak.
### US-14 — Penerbitan tagihan semester otomatis
**Persona:** UP-03 · **Pain Point:** PP-11, PP-14 · **Prioritas:** Must
 
> **As an** Admin Keuangan, **I want** tagihan semester terbit otomatis sesuai peminatan, tingkat semester, dan tarif yang berlaku, **so that** tidak ada salah hitung tagihan.
 
**Acceptance Criteria**
- **AC-14.1 (Semester 1)** — **Given** NPD baru saja terbit, **When** proses penerbitan NPD selesai, **Then** sistem menerbitkan tagihan Semester 1 dengan nominal = tarif semester 1 peminatan yang berlaku + kode unik, beserta jatuh tempo, dan status peserta menjadi Belum Aktif Semester 1.
- **AC-14.2 (Semester berikutnya)** — **Given** *rollover* menaikkan peserta ke semester *N*, **When** *rollover* selesai, **Then** sistem menerbitkan tagihan semester *N* sesuai tarif semester *N* peminatan tersebut.
- **AC-14.3 (Snapshot tarif)** — **Given** tagihan sudah terbit, **When** Admin Track kemudian mengubah tarif, **Then** nominal tagihan yang sudah terbit tidak berubah.
- **AC-14.4 (Tarif belum diatur)** — **Given** tarif semester *N* untuk peminatan tersebut belum diatur, **When** tagihan akan diterbitkan, **Then** sistem tidak menerbitkan tagihan dan memberi peringatan kepada Admin Track dan Super Admin.
- **AC-14.5 (Tanpa duplikasi)** — **Given** tagihan semester *N* untuk seorang peserta sudah ada, **When** proses penerbitan berjalan lagi, **Then** sistem tidak membuat tagihan ganda.
### US-15 — Melihat dasbor tagihan dan menerima pengingat
**Persona:** UP-01 · **Pain Point:** PP-04 · **Prioritas:** Must
 
> **As a** Peserta Didik, **I want to** melihat seluruh tagihan saya beserta nominal, jatuh tempo, dan riwayat pembayaran, serta menerima pengingat sebelum jatuh tempo, **so that** saya membayar tepat waktu dengan nominal yang benar.
 
**Acceptance Criteria**
- **AC-15.1 (Daftar tagihan)** — **Given** peserta memiliki tagihan, **When** membuka dasbor tagihan, **Then** sistem menampilkan setiap tagihan dengan jenis (Formulir / Semester *N*), kode referensi, nominal, jatuh tempo, status, dan tanggal pelunasan jika Lunas.
- **AC-15.2 (Pengingat)** — **Given** tagihan berstatus Belum Bayar atau Ditolak, **When** jatuh tempo tinggal 7 hari dan 1 hari, **Then** sistem mengirim pengingat lewat email dan notifikasi aplikasi.
- **AC-15.3 (Isolasi data)** — **Given** peserta login, **When** ia mencoba mengakses tagihan milik peserta lain, **Then** sistem menolak akses.
### US-16 — Memverifikasi pembayaran semester
**Persona:** UP-03 · **Pain Point:** PP-10, PP-11 · **Prioritas:** Must
 
> **As an** Admin Keuangan, **I want to** memverifikasi pembayaran tagihan semester, **so that** status peserta berubah menjadi aktif pada semester tersebut.
 
**Acceptance Criteria**
- **AC-16.1 (Lunas → Aktif)** — **Given** tagihan semester *N* berstatus Menunggu Verifikasi, **When** Admin Keuangan menandai Lunas, **Then** tagihan menjadi Lunas dan status peserta menjadi Aktif Semester *N*.
- **AC-16.2 (Antrean terpisah)** — **Given** Admin Keuangan membuka antrean pembayaran, **When** memfilter jenis tagihan, **Then** tagihan formulir dan tagihan semester dapat dipisahkan, termasuk filter semester ke-*N*, peminatan, dan periode.
- **AC-16.3 (Tertahan → Aktif)** — **Given** peserta berstatus Tertahan (Tunggakan), **When** tagihan semester berjalannya dinyatakan Lunas, **Then** status peserta menjadi Aktif Semester *N* dan peserta kembali ikut *rollover* berikutnya.
### US-17 — Rekap dan ekspor laporan keuangan
**Persona:** UP-03 · **Pain Point:** PP-12 · **Prioritas:** Should
 
> **As an** Admin Keuangan, **I want to** melihat rekap status pembayaran dan mengekspornya, **so that** saya dapat memantau tunggakan dan menyusun laporan tanpa rekap manual.
 
**Acceptance Criteria**
- **AC-17.1 (Rekap)** — **Given** terdapat data tagihan, **When** Admin Keuangan membuka dasbor rekap, **Then** sistem menampilkan jumlah dan total nominal per status (Lunas, Menunggu Verifikasi, Belum Bayar, Ditolak) dengan filter periode, peminatan, angkatan, dan jenis tagihan.
- **AC-17.2 (Tunggakan)** — **Given** tagihan melewati jatuh tempo dan belum Lunas, **When** rekap ditampilkan, **Then** tagihan tersebut dihitung sebagai tunggakan.
- **AC-17.3 (Ekspor)** — **Given** filter sudah diterapkan, **When** Admin Keuangan memilih ekspor Excel atau PDF, **Then** file yang diunduh berisi data sesuai filter.
---
 
## Epic E — Kuota Minimum, Pembatalan & Refund
 
### US-18 — Mengevaluasi kuota minimum dan membatalkan peminatan
**Persona:** UP-05, UP-04 · **Pain Point:** PP-06, PP-18 · **Prioritas:** Must
 
> **As a** Super Admin, **I want to** melihat peminatan yang tidak mencapai kuota minimum saat periode pendaftaran ditutup dan membatalkan peminatan yang tidak dapat berjalan, **so that** keputusan pembatalan dan *refund* tidak terlambat.
 
**Acceptance Criteria**
- **AC-18.1 (Evaluasi)** — **Given** periode pendaftaran sudah ditutup, **When** Super Admin membuka evaluasi kuota, **Then** sistem menampilkan setiap peminatan dengan jumlah pendaftar terverifikasi (NPD Terbit) dibandingkan kuota minimum, dan menandai peminatan yang tidak memenuhi.
- **AC-18.2 (Batalkan)** — **Given** peminatan tidak memenuhi kuota minimum, **When** Super Admin mengonfirmasi pembatalan, **Then** status seluruh pendaftaran pada peminatan itu menjadi Dibatalkan, Peserta Didik yang sudah memiliki NPD ikut berstatus Dibatalkan (K-34), tagihan yang belum Lunas menjadi Dibatalkan, dan setiap pendaftar yang sudah membayar mendapat refund berstatus Menunggu Data Rekening dengan nominal seluruh tagihan Lunas (formulir dan semester), tanpa selisih kode unik (K-19).
- **AC-18.3 (Notifikasi)** — **Given** peminatan dibatalkan, **When** pembatalan tersimpan, **Then** seluruh pendaftar terkait menerima notifikasi pembatalan dan instruksi *refund*.
- **AC-18.4 (Pembatalan karena kesepakatan)** — **Given** peminatan memenuhi kuota minimum tetapi ada kesepakatan terbaru untuk tidak menjalankannya, **When** Super Admin membatalkan peminatan, **Then** sistem mewajibkan alasan pembatalan diisi dan memproses pembatalan sama seperti AC-18.2, termasuk *refund* (K-19).
- **AC-18.5 (Log)** — **Given** pembatalan peminatan tersimpan, **When** tercatat, **Then** sistem menyimpan pelaksana, waktu, dasar pembatalan (kuota minimum / kesepakatan), dan alasan ke *audit log*.
### US-19 — Mengajukan data rekening refund
**Persona:** UP-01 · **Pain Point:** PP-06 · **Prioritas:** Must
 
> **As a** pendaftar yang peminatannya dibatalkan, **I want to** mengisi data rekening untuk pengembalian dana dan memantau prosesnya, **so that** saya yakin dana saya akan kembali.
 
**Acceptance Criteria**
- **AC-19.1 (Isi rekening)** — **Given** refund berstatus Menunggu Data Rekening, **When** pendaftar mengisi nama bank, nomor rekening, dan nama pemilik rekening, **Then** data tersimpan dengan nomor rekening terenkripsi dan status refund menjadi Diproses.
- **AC-19.2 (Validasi)** — **Given** nomor rekening berisi karakter non-angka atau *field* wajib kosong, **When** dikirim, **Then** sistem menolak.
- **AC-19.3 (Pelacakan)** — **Given** refund sudah diajukan, **When** pendaftar membuka dasbor, **Then** sistem menampilkan status refund, nominal, dan tanggal perubahan status.
- **AC-19.4 (Tampilan tersamar)** — **Given** data rekening tersimpan, **When** ditampilkan kepada pendaftar, **Then** nomor rekening tampil tersamar kecuali 4 digit terakhir.
- **AC-19.5 (Lebih dari satu refund)** — **Given** pendaftar memiliki lebih dari satu refund, **When** ia membuka menu refund, **Then** setiap refund tampil terpisah beserta rincian tagihan, nominal, dan statusnya, serta data rekening diisi per refund (K-31).
### US-20 — Memproses refund
**Persona:** UP-03 · **Pain Point:** PP-06 · **Prioritas:** Must
 
> **As an** Admin Keuangan, **I want to** melihat daftar refund dan menandai yang sudah ditransfer, **so that** seluruh pengembalian dana tercatat dan tidak ada yang terlewat.
 
**Acceptance Criteria**
- **AC-20.1 (Daftar)** — **Given** terdapat refund berstatus Diproses, **When** Admin Keuangan membuka modul refund, **Then** sistem menampilkan pendaftar, peminatan, nominal, dan data rekening.
- **AC-20.2 (Selesai)** — **Given** Admin Keuangan sudah mentransfer dana, **When** ia menandai refund Selesai dan mengunggah bukti transfer, **Then** status refund menjadi Selesai dan pendaftar menerima notifikasi.
- **AC-20.3 (Akses rekening)** — **Given** pengguna dengan *role* selain Admin Keuangan dan Super Admin, **When** mencoba melihat nomor rekening lengkap, **Then** sistem menolak akses, dan setiap akses nomor rekening lengkap tercatat di *audit log*.
- **AC-20.4 (Pembayaran belum terverifikasi)** — **Given** refund berasal dari tagihan formulir yang masih berstatus Menunggu Verifikasi (misalnya pendaftar pada peminatan yang dibatalkan), **When** Admin Keuangan membuka refund tersebut, **Then** sistem mewajibkan Admin Keuangan mencocokkan bukti dengan mutasi rekening terlebih dahulu sebelum refund dapat diproses (K-22).
- **AC-20.5 (Tidak ada refund ganda)** — **Given** sebuah tagihan sudah menjadi rincian pada satu refund, **When** sistem membuat refund baru untuk pendaftaran yang sama, **Then** tagihan tersebut tidak dapat dimasukkan lagi (K-31).
- **AC-20.6 (Refund ditolak)** — **Given** seluruh rincian sebuah refund dikecualikan karena pembayaran tidak ditemukan di mutasi rekening, **When** pengecualian terakhir disimpan, **Then** status refund menjadi Ditolak dengan alasan, tanpa bukti transfer, dan penerima menerima notifikasi (K-34).
---
 
## Epic F — Data Master (Admin Track)
 
### US-21 — Mengelola track dan peminatan
**Persona:** UP-04 · **Pain Point:** PP-15 · **Prioritas:** Must
 
> **As an** Admin Track, **I want to** menambah, mengubah, dan menonaktifkan track serta peminatan beserta durasi semesternya, **so that** data master selalu lengkap dan tidak duplikat.
 
**Acceptance Criteria**
- **AC-21.1 (Tambah)** — **Given** Admin Track mengisi kode, nama, track induk, dan durasi semester (bilangan bulat ≥ 1), **When** disimpan, **Then** peminatan baru tercatat dengan status aktif.
- **AC-21.2 (Kode duplikat)** — **Given** kode track atau peminatan sudah dipakai, **When** disimpan, **Then** sistem menolak dengan pesan kode sudah digunakan.
- **AC-21.3 (Nonaktifkan)** — **Given** peminatan masih memiliki peserta aktif, **When** Admin Track menonaktifkannya, **Then** peminatan tidak tampil di katalog untuk pendaftaran baru, tetapi data dan tagihan peserta yang sudah ada tetap berjalan.
- **AC-21.4 (Hapus dibatasi)** — **Given** peminatan sudah pernah dipakai pendaftaran, **When** Admin Track mencoba menghapusnya, **Then** sistem menolak dan menyarankan menonaktifkan.
- **AC-21.5 (Durasi terkunci untuk peserta lama)** — **Given** durasi semester peminatan diubah, **When** perubahan disimpan, **Then** durasi baru hanya berlaku untuk pendaftar periode berikutnya.
### US-22 — Mengelola tarif berversi
**Persona:** UP-04 · **Pain Point:** PP-14 · **Prioritas:** Must
 
> **As an** Admin Track, **I want to** mengatur tarif formulir dan tarif per semester tiap peminatan dengan tanggal berlaku efektif, **so that** tarif baru diterapkan tanpa mengubah tagihan yang sudah terbit.
 
**Acceptance Criteria**
- **AC-22.1 (Tarif semester)** — **Given** peminatan berdurasi *D* semester, **When** Admin Track mengatur tarif, **Then** sistem menyediakan isian nominal untuk semester 1 sampai *D* beserta tanggal berlaku efektif.
- **AC-22.2 (Tarif formulir)** — **Given** Admin Track mengatur tarif formulir, **When** disimpan dengan tanggal berlaku efektif, **Then** tarif formulir tersimpan per peminatan, dan tagihan formulir peminatan tersebut yang terbit sejak tanggal itu memakai tarif baru (K-20).
- **AC-22.3 (Riwayat versi)** — **Given** tarif diubah, **When** disimpan, **Then** versi lama tetap tersimpan dan dapat dilihat beserta pengubah dan waktunya.
- **AC-22.4 (Validasi)** — **Given** nominal ≤ 0 atau tanggal berlaku efektif kosong, **When** disimpan, **Then** sistem menolak.
- **AC-22.5 (Tagihan lama aman)** — **Given** tarif baru berlaku, **When** tagihan lama dibuka, **Then** nominalnya tetap sesuai *snapshot* saat terbit.
### US-23 — Mengatur kuota per peminatan
**Persona:** UP-04 · **Pain Point:** PP-18 · **Prioritas:** Must
 
> **As an** Admin Track, **I want to** menetapkan kuota minimum per peminatan sebelum periode pendaftaran dimulai, **so that** aturan kuota jelas sejak awal.
 
**Acceptance Criteria**
- **AC-23.1 (Atur)** — **Given** periode pendaftaran belum dimulai, **When** Admin Track mengisi kuota minimum per peminatan, **Then** kuota tersimpan untuk periode tersebut.
- **AC-23.2 (Validasi)** — **Given** kuota minimum bernilai negatif, **When** disimpan, **Then** sistem menolak.
- **AC-23.3 (Kuota minimum terkunci)** — **Given** periode pendaftaran sudah dimulai, **When** Admin Track mencoba mengubah kuota minimum, **Then** sistem menolak. Hanya Super Admin yang dapat mengubahnya dengan alasan tercatat.
- **AC-23.4 (Tambah kuota maksimum)** — *(Dihapus, K-42)* Tidak ada kuota maksimum.
- **AC-23.5 (Batas pengurangan)** — *(Dihapus, K-42)* Tidak ada kuota maksimum.
- **AC-23.6 (Wajib sebelum dibuka)** — **Given** peminatan aktif belum memiliki kuota untuk suatu periode, **When** periode tersebut akan dibuka, **Then** sistem memberi peringatan dan peminatan itu tidak tampil di katalog.
### US-24 — Memantau keterisian kuota
**Persona:** UP-04, UP-05 · **Pain Point:** PP-18 · **Prioritas:** Should
 
> **As an** Admin Track, **I want to** melihat keterisian kuota tiap peminatan secara *real-time*, **so that** saya dapat mengantisipasi peminatan yang berisiko tidak memenuhi kuota minimum.
 
**Acceptance Criteria**
- **AC-24.1 (Dasbor)** — **Given** periode pendaftaran berjalan, **When** dasbor kuota dibuka admin, **Then** sistem menampilkan per peminatan: jumlah Diajukan, jumlah Masuk Kuota, jumlah NPD Terbit, kuota minimum, dan persentase NPD Terbit terhadap kuota minimum.
- **AC-24.2 (Peringatan)** — **Given** sisa waktu periode pendaftaran ≤ 7 hari dan jumlah NPD Terbit masih di bawah kuota minimum, **When** dasbor dibuka, **Then** peminatan tersebut diberi penanda peringatan.
---
 
## Epic G — Administrasi Sistem (Super Admin)
 
### US-25 — Mengelola periode akademik dan periode pendaftaran
**Persona:** UP-05 · **Pain Point:** PP-16, PP-18 · **Prioritas:** Must
 
> **As a** Super Admin, **I want to** mengatur periode pendaftaran dan periode akademik beserta tanggalnya, **so that** pendaftaran, penerbitan tagihan, dan transisi semester berjalan sesuai kalender.
 
**Acceptance Criteria**
- **AC-25.1 (Buat periode)** — **Given** Super Admin mengisi nama periode, tanggal mulai, dan tanggal selesai, **When** disimpan, **Then** periode tercatat.
- **AC-25.2 (Validasi tanggal)** — **Given** tanggal selesai lebih awal dari tanggal mulai, atau periode pendaftaran tumpang-tindih dengan periode pendaftaran lain, **When** disimpan, **Then** sistem menolak.
- **AC-25.3 (Buka/tutup otomatis)** — **Given** periode pendaftaran tersimpan, **When** tanggal mulai dan selesai tercapai, **Then** pendaftaran dibuka dan ditutup otomatis.
### US-26 — Menjalankan rollover semester massal
**Persona:** UP-05 · **Pain Point:** PP-16 · **Prioritas:** Must
 
> **As a** Super Admin, **I want to** menjalankan pergantian semester massal setelah melihat pratinjau dampaknya, **so that** status semester dan tagihan seluruh peserta diperbarui akurat tanpa proses manual.
 
**Acceptance Criteria**
- **AC-26.1 (Pratinjau)** — **Given** periode akademik baru sudah dibuat, **When** Super Admin membuka *rollover*, **Then** sistem menampilkan jumlah peserta yang akan naik semester, yang akan berstatus Selesai Masa Program, dan yang akan Tertahan (Tunggakan), beserta daftar namanya.
- **AC-26.2 (Naik semester)** — **Given** peserta Aktif Semester *N* dengan *N* < durasi peminatan, **When** *rollover* dieksekusi, **Then** status peserta menjadi Belum Aktif Semester *N*+1 dan tagihan semester *N*+1 terbit (US-14).
- **AC-26.3 (Selesai)** — **Given** peserta Aktif Semester *N* dengan *N* = durasi peminatan, **When** *rollover* dieksekusi, **Then** status peserta menjadi Selesai Masa Program dan tidak ada tagihan baru.
- **AC-26.4 (Tunggakan)** — **Given** tagihan semester berjalan peserta belum Lunas, **When** *rollover* dieksekusi, **Then** peserta tidak dinaikkan, statusnya menjadi Tertahan (Tunggakan), dan tagihan semester berikutnya tidak diterbitkan (K-18).
- **AC-26.5 (Idempoten)** — **Given** *rollover* untuk periode akademik yang sama sudah dieksekusi, **When** Super Admin mencoba menjalankannya lagi, **Then** sistem menolak.
- **AC-26.6 (Atomik & log)** — **Given** terjadi kegagalan di tengah eksekusi, **When** proses berhenti, **Then** seluruh perubahan dibatalkan (*rollback*); jika berhasil, sistem mencatat pelaksana, waktu, dan ringkasan hasil ke *audit log*.
### US-27 — Mengelola akun dan role pengguna
**Persona:** UP-05 · **Pain Point:** PP-17 · **Prioritas:** Must
 
> **As a** Super Admin, **I want to** membuat akun admin, menetapkan *role*, dan menonaktifkan akun, **so that** setiap pengguna hanya mengakses fitur sesuai tanggung jawabnya.
 
**Acceptance Criteria**
- **AC-27.1 (Buat admin)** — **Given** Super Admin mengisi nama, email, dan *role* (Admin SILAU, Admin Keuangan, Admin Track, atau Super Admin), **When** disimpan, **Then** akun dibuat dan undangan aktivasi dikirim ke email tersebut.
- **AC-27.2 (Nonaktifkan)** — **Given** akun admin dinonaktifkan, **When** pemilik akun mencoba login, **Then** sistem menolak, dan sesi yang sedang aktif diakhiri.
- **AC-27.3 (Batas peran)** — **Given** pengguna login dengan *role* tertentu, **When** ia mengakses fitur di luar hak aksesnya, **Then** sistem menolak (HTTP 403).
- **AC-27.4 (Super Admin terakhir)** — **Given** hanya tersisa satu Super Admin aktif, **When** ada upaya menonaktifkan atau menurunkan *role*-nya, **Then** sistem menolak.
- **AC-27.5 (Aktivasi dengan 2FA)** — **Given** admin baru membuka undangan aktivasi, **When** ia mengatur password, **Then** ia wajib mendaftarkan aplikasi *authenticator* (TOTP) sebelum akun dapat dipakai (K-33).
- **AC-27.6 (Cabut token mobile)** — **Given** akun Admin SILAU atau Admin Keuangan dinonaktifkan atau *role*-nya diubah, **When** perubahan disimpan, **Then** seluruh token mobile akun tersebut langsung dicabut (K-30).
### US-28 — Menelusuri audit log
**Persona:** UP-05 · **Pain Point:** PP-17 · **Prioritas:** Must
 
> **As a** Super Admin, **I want to** mencari dan melihat riwayat aktivitas penting di sistem, **so that** setiap perubahan dapat ditelusuri saat terjadi sengketa.
 
**Acceptance Criteria**
- **AC-28.1 (Tercatat)** — **Given** terjadi aktivitas krusial (verifikasi berkas, verifikasi pembayaran, penerbitan NPD, perubahan tarif, perubahan kuota, *rollover*, pembatalan peminatan, perubahan *role*, akses nomor rekening, ekspor data pendaftaran, pembuatan dan unduhan arsip periode), **When** aktivitas selesai, **Then** sistem mencatat pelaku, *role*, waktu, entitas, nilai sebelum dan sesudah, serta alasan jika ada.
- **AC-28.2 (Pencarian)** — **Given** Super Admin membuka *audit log*, **When** memfilter berdasarkan pelaku, jenis aktivitas, entitas, atau rentang tanggal, **Then** sistem menampilkan entri yang sesuai.
- **AC-28.3 (Tidak dapat diubah)** — **Given** entri *audit log* sudah tersimpan, **When** siapa pun, termasuk Super Admin, mencoba mengubah atau menghapusnya, **Then** sistem tidak menyediakan fungsi tersebut.
### US-29 — Mengelola konten landing page
**Persona:** UP-05 · **Pain Point:** PP-19 · **Prioritas:** Should
 
> **As a** Super Admin, **I want to** memperbarui konten *landing page* tanpa bantuan tim teknis, **so that** informasi publik selalu mutakhir.
 
**Acceptance Criteria**
- **AC-29.1 (Ubah konten)** — **Given** Super Admin membuka CMS, **When** ia mengubah bagian jadwal, pengumuman, alur pendaftaran, atau kontak bantuan lalu menerbitkan, **Then** perubahan langsung tampil di *landing page*.
- **AC-29.2 (Pratinjau)** — **Given** Super Admin mengedit konten, **When** memilih pratinjau, **Then** sistem menampilkan tampilan sebelum diterbitkan.
- **AC-29.3 (Hanya Super Admin)** — **Given** pengguna selain Super Admin, **When** mengakses CMS, **Then** sistem menolak akses (K-14).
- **AC-29.4 (Data dinamis tidak diketik manual)** — **Given** tarif dan kuota ditampilkan di *landing page*, **When** halaman dimuat, **Then** data tersebut diambil dari data master (US-01), bukan dari konten CMS.
---
 
## Epic H — Aplikasi Mobile Verifikasi (Admin SILAU & Admin Keuangan)
 
### US-30 — Memverifikasi berkas dan pembayaran melalui aplikasi mobile
**Persona:** UP-02, UP-03 · **Pain Point:** PP-07, PP-09, PP-10 · **Prioritas:** Must
 
> **As an** Admin SILAU atau Admin Keuangan, **I want to** memverifikasi berkas atau pembayaran dari aplikasi mobile, **so that** antrean verifikasi tetap berjalan saat saya tidak berada di depan komputer.
 
**Acceptance Criteria**
- **AC-30.1 (Login)** — **Given** Admin SILAU atau Admin Keuangan aktif, **When** ia login di aplikasi mobile dengan email, password, dan kode TOTP yang valid, **Then** sistem menerbitkan token per perangkat yang berlaku 12 jam (K-30, K-33).
- **AC-30.2 (Role lain ditolak)** — **Given** pengguna ber-*role* selain Admin SILAU dan Admin Keuangan, **When** ia login di aplikasi mobile, **Then** sistem menolak (HTTP 403) (K-29).
- **AC-30.3 (Aturan sama dengan web)** — **Given** admin memverifikasi dari mobile, **When** keputusan disimpan, **Then** sistem menerapkan aturan yang sama dengan web, yaitu alasan wajib saat menolak, gerbang ganda, penempatan kuota, dan penerbitan NPD, serta mencatat kanal "mobile" di *audit log*.
- **AC-30.4 (Sudah diverifikasi admin lain)** — **Given** data sudah diputuskan admin lain setelah dibuka, **When** admin menyimpan keputusan, **Then** sistem menolak (HTTP 409 `SUDAH_DIVERIFIKASI`), menampilkan pesan, dan memuat ulang data.
- **AC-30.5 (Ruang lingkup)** — **Given** Admin Keuangan memakai aplikasi mobile, **When** ia membuka menu, **Then** hanya tersedia dasbor, antrean verifikasi, dan notifikasi; proses refund serta rekap dan ekspor laporan hanya tersedia di web (K-30).
- **AC-30.6 (Notifikasi)** — **Given** ada notifikasi baru untuk admin, **When** aplikasi dibuka, **Then** notifikasi tampil di dalam aplikasi tanpa notifikasi *push* (K-30).
- **AC-30.7 (Token berakhir)** — **Given** token sudah lewat 12 jam, dicabut saat logout, atau akun dinonaktifkan, **When** aplikasi memanggil API, **Then** sistem menjawab HTTP 401 dan aplikasi kembali ke layar login.
---
 
## Epic I — Data Pendaftaran & Arsip (Admin)
 
### US-31 — Melihat dan mengekspor data pendaftaran
**Persona:** UP-02, UP-05 · **Pain Point:** PP-07 · **Prioritas:** Should
 
> **As an** Admin SILAU atau Super Admin, **I want to** melihat seluruh pendaftaran yang sudah diajukan dalam satu daftar yang dapat difilter dan diekspor, **so that** saya dapat memantau perkembangan pendaftaran lintas status dan menyusun laporan tanpa rekap manual.
 
**Acceptance Criteria**
- **AC-31.1 (Daftar)** — **Given** terdapat pendaftaran pada periode terpilih, **When** admin membuka halaman Data Pendaftaran, **Then** sistem menampilkan seluruh pendaftaran yang sudah diajukan (semua status kecuali Draf) dengan nomor pendaftaran, nama, track, peminatan, waktu pengajuan, dan status, beserta jumlah total data.
- **AC-31.2 (Draf disembunyikan)** — **Given** terdapat pendaftaran berstatus Draf, **When** daftar ditampilkan, **Then** Draf tidak muncul sebagai baris, dan sistem hanya menampilkan jumlah Draf sebagai angka (K-37).
- **AC-31.3 (Filter & cari)** — **Given** admin mengisi filter periode, track, peminatan, status, atau rentang tanggal pengajuan, atau mencari berdasarkan nama, nomor pendaftaran, atau NIK, **When** filter diterapkan, **Then** sistem menampilkan data yang sesuai, dan NIK tetap tampil tersamar.
- **AC-31.4 (Detail baca-saja)** — **Given** admin membuka satu baris, **When** detail tampil, **Then** data dan dokumen dapat dilihat tetapi tidak tersedia aksi verifikasi; verifikasi hanya melalui antrean (US-09, US-12).
- **AC-31.5 (Aksi massal terbatas)** — **Given** admin memilih beberapa baris, **When** ia membuka aksi massal, **Then** satu-satunya aksi yang tersedia adalah ekspor baris terpilih (K-37).
- **AC-31.6 (Ekspor)** — **Given** Admin SILAU atau Super Admin memilih ekspor Excel atau PDF, **When** ekspor dijalankan untuk hasil filter atau baris terpilih, **Then** file berisi kolom daftar dengan NIK tersamar dan tanpa Draf; ekspor > 10.000 baris diproses di latar belakang dan tautannya dikirim melalui notifikasi.
- **AC-31.7 (Log ekspor)** — **Given** ekspor selesai, **When** tercatat, **Then** sistem menyimpan pelaku, filter, jumlah baris, dan format ke *audit log*.
- **AC-31.8 (Akses ekspor)** — **Given** pengguna ber-*role* Admin Keuangan atau Admin Track, **When** ia mencoba mengekspor data pendaftaran, **Then** sistem menolak (HTTP 403), dan tombol ekspor serta kotak centang tidak ditampilkan untuk *role* tersebut (K-37).
- **AC-31.9 (File ekspor berlaku 24 jam)** — **Given** file ekspor sudah dibuat, **When** 24 jam berlalu, **Then** file dihapus otomatis dan tautan unduhannya menampilkan pesan "Tautan sudah kedaluwarsa, silakan ekspor ulang", sementara data di sistem dan arsip periode tidak terpengaruh (K-38).
### US-32 — Mengunduh arsip periode
**Persona:** UP-02, UP-05 · **Pain Point:** PP-12, PP-17 · **Prioritas:** Should
 
> **As an** Admin SILAU atau Super Admin, **I want to** mengunduh salinan beku data pendaftaran setiap periode yang dibuat otomatis saat periode ditutup, **so that** institusi memiliki arsip resmi yang tidak berubah untuk laporan, akreditasi, atau penelusuran sengketa.
 
**Acceptance Criteria**
- **AC-32.1 (Dibuat otomatis)** — **Given** periode pendaftaran ditutup dan proses penutupan (pembatalan Draf dan penandaan kuota minimum) selesai, **When** sistem menjalankan pembuatan arsip, **Then** satu arsip periode tersedia berisi seluruh pendaftaran yang sudah diajukan dengan status per waktu penutupan, NIK tersamar, tanpa Draf, beserta lembar ringkasan jumlah per peminatan dan per status (K-38).
- **AC-32.2 (Satu arsip per periode)** — **Given** arsip periode sudah berstatus Tersedia, **When** proses penutupan atau pembuatan arsip terpicu lagi, **Then** sistem tidak membuat arsip kedua dan tidak menimpa arsip yang ada.
- **AC-32.3 (Daftar & unduh)** — **Given** Admin SILAU atau Super Admin membuka halaman Arsip Periode, **When** ia memilih unduh pada sebuah arsip, **Then** file terunduh dan sistem mencatat pelaku, waktu, dan arsip yang diunduh di *audit log*.
- **AC-32.4 (Permanen & tidak dapat diubah)** — **Given** arsip periode tersimpan, **When** admin mana pun, termasuk Super Admin, membuka halaman Arsip Periode, **Then** tidak tersedia aksi ubah, ganti, atau hapus, dan proses pembersihan file ekspor 24 jam tidak menyentuh arsip.
- **AC-32.5 (Integritas)** — **Given** arsip akan diunduh, **When** *checksum* SHA-256 file tidak cocok dengan yang tersimpan, **Then** unduhan dibatalkan, pengguna melihat pesan bahwa arsip tidak dapat diverifikasi, dan Super Admin menerima peringatan.
- **AC-32.6 (Gagal dibuat)** — **Given** pembuatan arsip gagal, **When** seluruh percobaan ulang otomatis habis, **Then** status arsip menjadi Gagal, Super Admin menerima notifikasi, dan Super Admin dapat memicu percobaan ulang.
- **AC-32.7 (Akses)** — **Given** pengguna ber-*role* Admin Keuangan, Admin Track, atau peserta, **When** ia membuka halaman atau tautan unduh arsip periode, **Then** sistem menolak (HTTP 403).
---
 
## Matriks Keterlacakan
 
| US | Persona | Pain Point | Prioritas |
|---|---|---|---|
| US-01 | UP-01 | PP-01 | Must |
| US-02 | UP-01 | PP-02 | Must |
| US-03 | UP-01–UP-05 | PP-17 | Must |
| US-04 | UP-01 | PP-01 | Must |
| US-05 | UP-01 | PP-07 | Must |
| US-06 | UP-01 | PP-04, PP-05, PP-10, PP-13 | Must |
| US-07 | UP-01 | PP-02 | Must |
| US-08 | UP-01 | PP-03 | Must |
| US-09 | UP-02 | PP-07 | Must |
| US-10 | UP-02 | PP-08 | Must |
| US-11 | UP-02, UP-04 | PP-09, PP-18 | Must |
| US-12 | UP-03 | PP-09, PP-10 | Must |
| US-13 | UP-03 | PP-13 | Must |
| US-14 | UP-03 | PP-11, PP-14 | Must |
| US-15 | UP-01 | PP-04 | Must |
| US-16 | UP-03 | PP-10, PP-11 | Must |
| US-17 | UP-03 | PP-12 | Should |
| US-18 | UP-05, UP-04 | PP-06, PP-18 | Must |
| US-19 | UP-01 | PP-06 | Must |
| US-20 | UP-03 | PP-06 | Must |
| US-21 | UP-04 | PP-15 | Must |
| US-22 | UP-04 | PP-14 | Must |
| US-23 | UP-04 | PP-18 | Must |
| US-24 | UP-04, UP-05 | PP-18 | Should |
| US-25 | UP-05 | PP-16, PP-18 | Must |
| US-26 | UP-05 | PP-16 | Must |
| US-27 | UP-05 | PP-17 | Must |
| US-28 | UP-05 | PP-17 | Must |
| US-29 | UP-05 | PP-19 | Should |
| US-30 | UP-02, UP-03 | PP-07, PP-09, PP-10 | Must |
| US-31 | UP-02, UP-05 | PP-07 | Should |
| US-32 | UP-02, UP-05 | PP-12, PP-17 | Should |
 
Setiap *pain point* PP-01 s.d. PP-19 tercakup oleh minimal satu *user story*.
 
---
 
## Daftar Asumsi
 
Status per 21 November 2026: asumsi A-01 s.d. A-14 disetujui pengguna, kecuali A-05, A-10, A-12, dan A-13 yang direvisi. A-17 disetujui melalui K-19. A-15, A-16, dan A-18 disetujui melalui K-22. Seluruh asumsi telah dikonfirmasi. Keputusan lanjutan K-29 s.d. K-34 (aplikasi mobile, data formulir, refund ganda, reset password, 2FA wajib, pembatalan Peserta Didik, refund Ditolak) sudah diterapkan pada US-03, US-04, US-18, US-19, US-20, US-27, dan US-30. Keputusan K-37 (Draf privat, Draf saat periode ditutup, nomor pendaftaran, daftar & ekspor data pendaftaran, larangan verifikasi massal) diterapkan pada US-04, US-09, US-12, US-28, dan US-31 baru. A-57 (nomor pendaftaran) dan A-58 (hak ekspor) disetujui melalui K-37. Keputusan K-38 (file ekspor 24 jam, arsip periode permanen) diterapkan pada AC-04.8, AC-28.1, AC-31.9, dan US-32 baru. Format arsip (A-61) masih menunggu konfirmasi; lihat dokumen FR & BR.
 
| # | Asumsi | Dipakai di | Status |
|---|---|---|---|
| A-01 | Verifikasi email wajib sebelum mengisi formulir. | US-02 | Disetujui |
| A-02 | Kunci akun 15 menit setelah 5 kali gagal login; tautan reset berlaku 60 menit. | US-03 | Disetujui |
| A-03 | Batas ukuran file berkas 2 MB (PDF/JPG/PNG). | US-05 | Disetujui |
| A-04 | Notifikasi lewat email dan *in-app*; pengingat tagihan H-7 dan H-1. | US-07, US-15 | Disetujui |
| A-05 | Pendaftaran Masuk Kuota yang tagihan formulirnya tidak dilunasi dalam 7 hari otomatis Dibatalkan. Ketentuan Daftar Tunggu saat kuota maksimum penuh dihapus (K-42). | US-11 | Direvisi (K-15, K-17, K-42) |
| A-06 | Status Lunas hanya dapat dikoreksi oleh Super Admin dengan alasan tercatat. | US-13 | Disetujui |
| A-07 | Status Belum Aktif Semester *N* dipakai sebelum tagihan semester *N* Lunas. | US-14, US-26 | Disetujui (K-21) |
| A-08 | Peserta dengan tagihan semester berjalan belum Lunas tidak dinaikkan saat *rollover* (status Tertahan) dan tidak dapat mengikuti semester selanjutnya. | US-16, US-26 | Disetujui (K-18) |
| A-09 | Kuota minimum dievaluasi saat periode pendaftaran ditutup; pembatalan dieksekusi oleh Super Admin. | US-18 | Disetujui |
| A-10 | Refund mencakup seluruh biaya yang tidak terpakai, termasuk biaya formulir, tanpa selisih kode unik. | US-18, US-20 | Direvisi (K-19) |
| A-11 | Perubahan durasi semester hanya berlaku untuk pendaftar periode berikutnya. | US-21 | Disetujui |
| A-12 | Tarif formulir bervariasi per peminatan. | US-06, US-22 | Direvisi (K-20) |
| A-13 | Kuota minimum terkunci setelah periode dimulai (hanya Super Admin yang dapat mengubah dengan alasan). Kuota maksimum dihapus (K-42). | US-23 | Direvisi (K-15, K-42) |
| A-14 | Ambang peringatan kuota minimum: 7 hari sebelum periode ditutup. | US-24 | Disetujui |
| A-15 | Urutan Daftar Tunggu berdasarkan waktu pengajuan formulir (FIFO). | US-11 | Dibatalkan (K-42) |
| A-16 | Pendaftar yang masih di Daftar Tunggu saat periode ditutup berstatus Tidak Tertampung dan mendapat refund. | US-11 | Dibatalkan (K-42) |
| A-17 | Pembatalan peminatan karena kesepakatan terbaru diperlakukan sama dengan pembatalan karena kuota minimum, termasuk *refund*. | US-18 | Disetujui (K-19) |
| A-18 | Refund dari tagihan formulir yang belum terverifikasi (mis. pendaftar pada peminatan yang dibatalkan) baru diproses setelah Admin Keuangan mencocokkan bukti dengan mutasi rekening. | US-20 | Disetujui (K-22, K-42) |