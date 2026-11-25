# Spesifikasi Kebutuhan Perangkat Lunak — Functional Requirements & Business Rules
## Polibatam Verify
 
Disusun berdasarkan *User Stories* (US-01 s.d. US-32) dan *Keputusan Klarifikasi Scope* (K-01 s.d. K-41).
 
**Konvensi:**
- Kata **"harus"** menandakan kebutuhan wajib yang dapat diuji.
- Kolom **US** merujuk *user story* sumber; kolom **BR** merujuk aturan bisnis yang mengikat.
- Label `[ASUMSI]` dipakai untuk detail teknis baru yang belum pernah dikonfirmasi.
- Istilah status mengikuti Glosarium Status pada dokumen *User Stories*.
---
 
## 1. Business Rules (BR)
 
### 1.1 Akun & Akses
 
| ID | Aturan | Sumber |
|---|---|---|
| BR-01 | Satu alamat email hanya dapat dipakai untuk satu akun. | US-02 |
| BR-02 | Akun Calon Peserta wajib memverifikasi email sebelum dapat mengisi formulir pendaftaran. | K-01, A-01 |
| BR-03 | Akun dikunci 15 menit setelah 5 kali gagal login berturut-turut. Tautan reset password berlaku 60 menit dan hanya dapat dipakai sekali. | A-02 |
| BR-04 | Setiap pengguna hanya dapat mengakses fitur sesuai *role*-nya (Matriks Hak Akses, Bagian 3). | K-14, US-27 |
| BR-05 | Sistem harus selalu memiliki minimal satu akun Super Admin aktif. | US-27 |
| BR-06 | *Role* akun berubah dari Calon Peserta menjadi Peserta Didik secara otomatis saat NPD terbit. Perubahan *role* ini tidak dapat dilakukan manual. | K-01 |
 
### 1.2 Pendaftaran & Berkas
 
| ID | Aturan | Sumber |
|---|---|---|
| BR-07 | Formulir hanya dapat diajukan saat periode pendaftaran sedang dibuka, untuk peminatan berstatus aktif yang sudah memiliki kuota dan tarif pada periode tersebut. | US-01, US-04, US-23 |
| BR-08 | Satu akun hanya boleh memiliki satu pendaftaran yang belum berstatus akhir pada satu periode pendaftaran. Status akhir: Dibatalkan, NPD Terbit. | US-04, K-42 |
| BR-09 | Track dan peminatan dipilih saat pendaftaran dan tidak dapat diubah setelah formulir berstatus Diajukan. | Pedoman 1.2, US-04 |
| BR-10 | Tidak ada tes maupun interview untuk seluruh peminatan. | K-03 |
| BR-11 | Dokumen wajib: (1) Ijazah/SKL/Rapor semester 5, (2) SKHU/Nilai UN/SKL/Daftar Nilai, (3) KTP. Dokumen opsional: Bukti Pendaftaran Polibatam. | Pedoman 3.1 |
| BR-12 | File dokumen harus berformat PDF, JPG, atau PNG dengan ukuran maksimal 2 MB per file. | A-03 |
| BR-13 | Penolakan dokumen oleh Admin SILAU wajib disertai alasan. | US-10 |
| BR-14 | Saat revisi, peserta hanya dapat mengunggah ulang dokumen berstatus Ditolak. Dokumen berstatus Valid terkunci. | US-08 |
 
### 1.3 Kuota Minimum
 
| ID | Aturan | Sumber |
|---|---|---|
| BR-15 | Kuota minimum ditetapkan per peminatan per periode pendaftaran sebelum periode dimulai, dengan syarat kuota minimum ≥ 0. **Tidak ada kuota maksimum**: sistem menerima pendaftar sebanyak-banyaknya. | K-12, US-23, K-42 |
| BR-16 | *(Dihapus, K-42)* Slot terpakai tidak lagi dihitung karena tidak ada kuota maksimum. | K-12, K-42 |
| BR-17 | Saat seluruh dokumen wajib Valid, pendaftaran menjadi **Masuk Kuota**. Tidak ada batas jumlah, sehingga pendaftaran tidak pernah ditolak atau ditahan karena jumlah pendaftar. | K-15, K-42 |
| BR-18 | *(Dihapus, K-42)* Daftar Tunggu ditiadakan. | K-22, K-42 |
| BR-19 | *(Dihapus, K-42)* Daftar Tunggu ditiadakan. | K-15, K-42 |
| BR-20 | Kuota minimum hanya dapat diubah Super Admin dengan alasan wajib setelah periode pendaftaran dimulai. | K-15, A-13, K-42 |
| BR-21 | Pendaftaran Masuk Kuota yang tagihan formulirnya masih Belum Bayar atau Ditolak 7 hari setelah masuk kuota otomatis menjadi **Dibatalkan**. Tagihan berstatus Menunggu Verifikasi dikecualikan. | K-17, K-42 |
| BR-22 | *(Dihapus, K-42)* Status Tidak Tertampung ditiadakan karena tidak ada Daftar Tunggu. | K-22, K-42 |
| BR-23 | **Pendaftar terverifikasi** adalah pendaftar yang NPD-nya sudah terbit. Kuota minimum dievaluasi terhadap jumlah pendaftar terverifikasi saat periode pendaftaran ditutup; sistem otomatis **menandai** peminatan yang jumlahnya di bawah kuota minimum. | K-12, A-09, K-42 |
| BR-24 | Peminatan dapat dibatalkan jika (a) jumlah pendaftar terverifikasi < kuota minimum (ditandai sistem, BR-23), atau (b) ada kesepakatan terbaru (alasan wajib). Pembatalan hanya dapat dilakukan Super Admin, tidak otomatis. Saat peminatan dibatalkan, seluruh pendaftaran terkait berstatus Dibatalkan, termasuk Peserta Didik yang sudah memiliki NPD pada peminatan tersebut, dan pendaftar yang sudah membayar mendapat refund (BR-47). | K-16, K-34, K-42 |
 
### 1.4 Tarif & Tagihan
 
| ID | Aturan | Sumber |
|---|---|---|
| BR-25 | Tarif formulir ditetapkan **per peminatan**. Tarif semester ditetapkan **per peminatan per semester ke-*n*** (1 s.d. durasi). Seluruh nominal tarif harus > 0. | K-02, K-20 |
| BR-26 | Setiap perubahan tarif membuat versi baru dengan tanggal berlaku efektif. Versi lama tidak boleh diubah atau dihapus. | US-22 |
| BR-27 | Tarif yang dipakai saat tagihan terbit adalah versi dengan tanggal berlaku efektif terbaru yang ≤ tanggal penerbitan tagihan. | US-22 |
| BR-28 | Nominal tagihan disimpan sebagai *snapshot* saat terbit dan tidak berubah meskipun tarif diubah. | US-14, US-22 |
| BR-29 | **Nominal tagihan = nominal dasar (tarif) + kode unik 3 digit (001–999).** Kode unik dibangkitkan **secara acak** dan tidak boleh sama dengan tagihan lain yang masih terbuka (Belum Bayar, Ditolak, Menunggu Verifikasi) dengan nominal dasar yang sama. | K-09, K-23 |
| BR-30 | Selisih akibat kode unik tidak dicatat sebagai kelebihan bayar dan tidak dikembalikan. | K-09 |
| BR-31 | Setiap tagihan memiliki kode referensi unik yang tidak berubah. | US-06 |
| BR-32 | Waktu penerbitan tagihan: (a) **formulir**, saat formulir diajukan; (b) **Semester 1**, saat NPD terbit; (c) **Semester *N*+1**, saat *rollover*. | K-01, US-14 |
| BR-33 | Satu peserta hanya boleh memiliki satu tagihan untuk setiap semester ke-*n*. | US-14 |
| BR-34 | Jika tarif semester ke-*n* untuk peminatan belum diatur, tagihan tidak diterbitkan dan peringatan dikirim ke Admin Track dan Super Admin. | US-14 |
| BR-35 | **Jatuh tempo tagihan semester = H-7 sebelum tanggal mulai semester** (tanggal mulai periode akademik yang bersangkutan). Jika tagihan terbit pada atau setelah tanggal H-7 tersebut, jatuh tempo **diatur manual oleh Super Admin**. Selama belum diatur, tagihan berstatus Belum Bayar tanpa jatuh tempo, tidak dihitung sebagai tunggakan, dan tidak memicu pengingat. Jatuh tempo manual **boleh lebih awal dari tanggal penetapan** sesuai kesepakatan; tagihan seperti itu langsung dihitung sebagai lewat jatuh tempo. | K-23, K-24, K-25, K-32, US-15 |
 
### 1.5 Pembayaran & NPD
 
| ID | Aturan | Sumber |
|---|---|---|
| BR-36 | Bukti pembayaran hanya dapat diunggah untuk tagihan berstatus Belum Bayar atau Ditolak. Unggahan dikunci saat tagihan berstatus Menunggu Verifikasi atau Lunas. | K-10 |
| BR-37 | File bukti pembayaran yang *hash*-nya sama dengan bukti yang pernah diunggah ditolak. *Hash check* hanya kontrol awal. Kontrol yang mengikat adalah pencocokan mutasi rekening dengan kode referensi dan nominal. | K-10 |
| BR-38 | Penolakan pembayaran oleh Admin Keuangan wajib disertai alasan. | US-13 |
| BR-39 | **Gerbang ganda:** tagihan formulir hanya dapat dinyatakan Lunas jika pendaftarannya berstatus Masuk Kuota, yang berarti seluruh berkas sudah Valid. | K-01, PP-09 |
| BR-40 | Status Lunas bersifat final. Koreksi hanya dapat dilakukan Super Admin dengan alasan wajib dan tercatat di *audit log*. | A-06 |
| BR-41 | NPD terbit otomatis saat tagihan formulir dinyatakan Lunas. NPD unik secara global dan tidak dapat diubah. Format sementara (*dummy*) hingga Polibatam menetapkan format resmi: `MC-{TAHUN}-{KODE_PEMINATAN}-{NO_URUT_4_DIGIT}`, contoh `MC-2026-AKT-0001`. Format harus dapat dikonfigurasi. | K-08, K-23, US-12 |
 
### 1.6 Status Peserta & Rollover
 
| ID | Aturan | Sumber |
|---|---|---|
| BR-42 | Saat NPD terbit, peserta berstatus **Belum Aktif Semester 1**. Saat tagihan semester *N* Lunas, peserta berstatus **Aktif Semester *N***. | K-21 |
| BR-43 | Durasi semester yang berlaku bagi peserta adalah durasi peminatan pada saat ia mendaftar (*snapshot*). Perubahan durasi hanya berlaku untuk pendaftar periode berikutnya. | A-11 |
| BR-44 | *Rollover* hanya dapat dijalankan Super Admin, satu kali per periode akademik, dan harus atomik: berhasil seluruhnya atau dibatalkan seluruhnya. Sistem memberi peringatan jika *rollover* belum dijalankan pada H-14 sebelum semester dimulai, agar tagihan terbit sebelum jatuh tempo. | US-26, K-24 |
| BR-45 | Aturan *rollover* untuk peserta dengan semester berjalan *N* dan durasi *D*:<br>• Aktif Semester *N* dan *N* < *D* → **Belum Aktif Semester *N*+1**, dan tagihan semester *N*+1 diterbitkan.<br>• Aktif Semester *N* dan *N* = *D* → **Selesai Masa Program**, tanpa tagihan baru.<br>• Tagihan semester *N* belum Lunas → **Tertahan (Tunggakan)**, tidak dinaikkan, tanpa tagihan baru. | K-07, K-11, K-18 |
| BR-46 | Peserta Tertahan tidak dapat mengikuti semester selanjutnya. Setelah tunggakan semester *N* Lunas, status menjadi Aktif Semester *N* dan peserta ikut *rollover* berikutnya. | K-18 |
 
### 1.7 Refund
 
| ID | Aturan | Sumber |
|---|---|---|
| BR-47 | Refund dibuat untuk setiap pendaftar yang sudah membayar jika peminatannya dibatalkan (BR-24). | K-19, K-42 |
| BR-48 | **Nominal refund = jumlah nominal dasar (tanpa kode unik) seluruh tagihan yang sudah dibayar tetapi tidak terpakai** (formulir dan semester). | K-09, K-19 |
| BR-49 | Pembayaran yang belum terverifikasi (tagihan Menunggu Verifikasi) wajib dicocokkan Admin Keuangan dengan mutasi rekening sebelum refund diproses. Jika tidak ditemukan di mutasi, tagihan tersebut dikeluarkan dari nominal refund dengan alasan tercatat. | K-22, K-23 |
| BR-50 | Nomor rekening refund disimpan terenkripsi, ditampilkan tersamar kecuali 4 digit terakhir, dan hanya dapat dilihat lengkap oleh Admin Keuangan atau Super Admin. Setiap akses lengkap tercatat di *audit log*. | K-13 |
| BR-51 | Status refund berjalan berurutan: Menunggu Data Rekening → Diproses → Selesai. Status Selesai wajib disertai bukti transfer. Jika seluruh rincian refund dikecualikan (nominal Rp0), refund berstatus **Ditolak** dengan alasan tercatat, tanpa bukti transfer, dan penerima diberi notifikasi. Selesai dan Ditolak adalah status akhir. | US-19, US-20, K-34 |
 
### 1.8 Data Master, Periode & Konten
 
| ID | Aturan | Sumber |
|---|---|---|
| BR-52 | Kode track dan kode peminatan harus unik. Durasi semester peminatan berupa bilangan bulat ≥ 1. | US-21 |
| BR-53 | Track atau peminatan yang sudah pernah dipakai pendaftaran tidak dapat dihapus, hanya dapat dinonaktifkan. Peminatan nonaktif tidak tampil di katalog, tetapi data peserta yang sudah ada tetap berjalan. | US-21 |
| BR-54 | Tanggal selesai periode harus lebih besar dari tanggal mulai. Periode pendaftaran tidak boleh tumpang-tindih. Pendaftaran dibuka dan ditutup otomatis sesuai tanggal periode. | US-25 |
| BR-55 | Konten *landing page* hanya dapat dikelola Super Admin. Data tarif dan kuota di *landing page* diambil dari data master, bukan diketik manual. | K-14, US-29 |
 
### 1.9 Notifikasi & Audit
 
| ID | Aturan | Sumber |
|---|---|---|
| BR-56 | Setiap perubahan status pendaftaran, dokumen, tagihan, peserta, dan refund memicu notifikasi email dan *in-app* ke pemilik akun. | A-04 |
| BR-57 | Pengingat tagihan dikirim H-7 dan H-1 sebelum jatuh tempo untuk tagihan berstatus Belum Bayar atau Ditolak. | A-04 |
| BR-58 | Aktivitas krusial wajib dicatat di *audit log*, yaitu: verifikasi berkas, verifikasi pembayaran, penerbitan NPD, perubahan tarif, perubahan kuota, *rollover*, pembatalan peminatan, koreksi status Lunas, perubahan *role*/akun, akses nomor rekening lengkap, penetapan jatuh tempo manual, perubahan konten CMS, ekspor data pendaftaran, pembuatan dan unduhan arsip periode, serta perubahan rekening institusi. Entri *audit log* tidak dapat diubah atau dihapus oleh siapa pun. | PP-17, US-28, K-37, K-38, K-40 |
 
### 1.10 Keamanan Akun, Kanal Mobile & Data Sensitif
 
| ID | Aturan | Sumber |
|---|---|---|
| BR-59 | **2FA (TOTP) wajib untuk seluruh akun admin** (Admin SILAU, Admin Keuangan, Admin Track, Super Admin), di web maupun mobile. Admin wajib mendaftarkan *authenticator* saat aktivasi akun. Tanpa kode TOTP yang valid, sesi atau token tidak diterbitkan. | K-30, K-33 |
| BR-60 | Aplikasi mobile hanya dapat dipakai Admin SILAU dan Admin Keuangan, terbatas pada dasbor, antrean dan keputusan verifikasi, serta notifikasi *in-app*. Token mobile berlaku 12 jam dan dicabut saat logout, akun dinonaktifkan, atau *role* berubah. | K-29, K-30 |
| BR-61 | Keputusan verifikasi berkas atau pembayaran ditolak jika data sudah diputuskan admin lain sejak dibuka (*optimistic locking*). | K-29 |
| BR-62 | NIK disimpan terenkripsi dan ditampilkan tersamar di daftar dan antrean. Pencarian NIK memakai *hash* (HMAC) tanpa mendekripsi data. | K-31 |
| BR-63 | Satu pendaftaran dapat memiliki lebih dari satu refund. Setiap tagihan hanya boleh menjadi rincian pada satu refund. | K-31 |
 
### 1.11 Draf, Data Pendaftaran, Ekspor & Arsip
 
| ID | Aturan | Sumber |
|---|---|---|
| BR-64 | Pendaftaran berstatus **Draf** hanya dapat diakses pemiliknya. Draf tidak tampil di antrean verifikasi, daftar data pendaftaran, laporan, ekspor, maupun arsip periode, dan isinya tidak dapat dibuka oleh *role* admin mana pun, termasuk Super Admin. Admin hanya boleh melihat **jumlah** Draf sebagai angka agregat (per periode/track/peminatan), tanpa identitas pemilik. | K-37 |
| BR-65 | Saat periode pendaftaran ditutup, seluruh pendaftaran berstatus Draf pada periode tersebut otomatis menjadi **Dibatalkan** dengan alasan "Periode pendaftaran ditutup sebelum formulir diajukan". Formulir dan berkasnya terkunci (hanya dapat dilihat pemilik), dan pemilik menerima notifikasi. Karena tagihan formulir baru terbit saat formulir diajukan (BR-32), pembatalan ini tidak menimbulkan tagihan maupun refund. | K-37 |
| BR-66 | Keputusan verifikasi berkas dan verifikasi pembayaran **tidak boleh dilakukan secara massal**; setiap keputusan diambil satu per satu melalui halaman detail (*checklist*, alasan wajib saat menolak, dan *optimistic locking* tetap berlaku). Aksi massal pada daftar data hanya diizinkan untuk aksi yang **tidak mengubah status**, yaitu ekspor baris terpilih. Penetapan jatuh tempo massal oleh Super Admin (FR-57) bukan keputusan verifikasi dan tetap berlaku. | K-37 |
| BR-67 | Ekspor data pendaftaran hanya dapat dilakukan Admin SILAU dan Super Admin, hanya memuat pendaftaran yang sudah diajukan (bukan Draf), menampilkan NIK tersamar, tidak memuat nomor rekening maupun file dokumen, dan setiap ekspor (pelaku, filter, jumlah baris, format) dicatat di *audit log*. **File hasil ekspor dan tautan unduhannya berlaku 24 jam**, lalu file dihapus otomatis oleh proses terjadwal. Penghapusan ini tidak menyentuh data di basis data maupun arsip periode (BR-69). | K-37, K-38 |
| BR-68 | Setiap pendaftaran memiliki **nomor pendaftaran** unik berformat `PV-{TAHUN}-{NO_URUT_6_DIGIT}` (contoh `PV-2026-000418`) yang dibangkitkan saat Draf pertama kali disimpan dan tidak dapat diubah. | K-37 |
| BR-69 | **Arsip periode:** saat periode pendaftaran ditutup (setelah BR-22 dan BR-65 dijalankan), sistem otomatis membuat **satu arsip** berisi salinan beku seluruh pendaftaran yang sudah diajukan pada periode tersebut, dengan kondisi status per waktu penutupan, kolom sesuai FR-63, NIK tersamar, serta ringkasan jumlah per peminatan dan per status (termasuk jumlah Draf yang dibatalkan sebagai angka). Arsip disimpan **permanen**, tidak dapat diubah, ditimpa, atau dihapus melalui aplikasi, dan dikecualikan dari penghapusan file ekspor (BR-67). Arsip dilengkapi *checksum* SHA-256 yang diperiksa setiap kali diunduh. Hanya Admin SILAU dan Super Admin yang dapat mengunduh arsip, dan setiap unduhan dicatat di *audit log*. | K-38 |
 
### 1.12 Rekening Institusi, Data Pengiriman & Kartu Pendaftar
 
| ID | Aturan | Sumber |
|---|---|---|
| BR-70 | **Rekening institusi** adalah rekening tujuan transfer seluruh tagihan dan dikelola oleh Admin Keuangan dan Super Admin. Untuk setiap jenis tagihan (formulir, semester) hanya boleh ada **satu rekening berstatus Aktif**; mengaktifkan rekening lain untuk jenis yang sama otomatis menonaktifkan rekening sebelumnya. Rekening yang pernah dipakai tagihan tidak dapat dihapus, hanya dinonaktifkan. Setiap penambahan, perubahan, dan perubahan status dicatat di *audit log*. | K-40 |
| BR-71 | Saat tagihan terbit, data rekening tujuan (bank, nomor rekening, atas nama) disimpan sebagai *snapshot* pada tagihan. Perubahan rekening institusi tidak mengubah tagihan yang sudah terbit. Jika tidak ada rekening aktif untuk jenis tagihan tersebut, tagihan tidak diterbitkan dan peringatan dikirim ke Admin Keuangan dan Super Admin. | K-40 |
| BR-72 | Saat mengunggah bukti pembayaran, pengguna wajib mengisi **data pengiriman**: tanggal transfer (tidak boleh melebihi hari ini), nominal yang ditransfer, bank pengirim, dan nama pengirim. Data ini membantu Admin Keuangan mencocokkan mutasi rekening tetapi tidak menggantikan pencocokan mutasi (BR-37). Jika nominal yang diisi berbeda dari nominal tagihan, sistem menampilkan peringatan kepada pengguna dan Admin Keuangan, tetapi unggahan tetap diterima. | K-40 |
| BR-73 | Setelah formulir berstatus Diajukan, pemilik pendaftaran dapat mengunduh **kartu pendaftar** (PDF) berisi nomor pendaftaran, nama lengkap, track, peminatan, periode, waktu pengajuan, dan status terkini. Kartu tidak memuat NIK maupun data rekening, dan tidak tersedia untuk Draf. | K-40 |
 
### 1.13 Fast Verify (Aplikasi Mobile)
 
| ID | Aturan | Sumber |
|---|---|---|
| BR-74 | **Fast Verify** adalah mode antrean berurutan di aplikasi mobile; setiap gestur geser tetap menghasilkan **satu keputusan untuk satu objek** dan bukan aksi massal (BR-66). **Admin SILAU:** satu kartu = satu dokumen; geser kanan = Valid dan hanya diterima bila seluruh kriteria *checklist* dicentang; geser kiri = Ditolak dan wajib alasan (kriteria yang tidak terpenuhi dan/atau catatan). Setelah dokumen terakhir seorang pendaftar, sistem menampilkan ringkasan hasil dan admin menekan **Selesaikan** sebelum lanjut ke pendaftar berikutnya. **Admin Keuangan:** satu kartu = satu tagihan; geser kanan membuka konfirmasi Tandai Lunas, geser kiri membuka formulir alasan penolakan. Tagihan formulir yang belum lolos gerbang ganda (BR-39) tidak dimasukkan ke Fast Verify. *Optimistic locking* (BR-61) dan *audit log* (BR-58) berlaku untuk setiap keputusan. | K-41 |
| BR-75 | Informasi kuota (kuota minimum, jumlah pendaftar, persentase keterisian) **tidak ditampilkan** kepada pengunjung maupun calon peserta, termasuk di katalog, saat memilih peminatan, dan di dasbor peserta. Informasi kuota hanya untuk admin (dasbor kuota, evaluasi kuota minimum). | K-42 |
 
---
 
## 2. Functional Requirements (FR)
 
### 2.1 Modul Akun & Akses
 
| ID | Kebutuhan | Aktor | US | BR |
|---|---|---|---|---|
| FR-01 | Sistem harus menyediakan registrasi akun dengan input email, password, dan konfirmasi password, lalu membuat akun ber-*role* Calon Peserta. | Calon Peserta | US-02 | BR-01 |
| FR-02 | Sistem harus mengirim tautan verifikasi email setelah registrasi, menyediakan fitur kirim ulang tautan, dan memblokir akses formulir sebelum email terverifikasi. | Calon Peserta | US-02 | BR-02 |
| FR-03 | Sistem harus menyediakan login dengan email dan password, lalu mengarahkan pengguna ke dasbor sesuai *role*. Pesan kegagalan login harus bersifat umum. | Semua | US-03 | BR-04 |
| FR-04 | Sistem harus mengunci akun sementara setelah batas gagal login terlampaui. | Semua | US-03 | BR-03 |
| FR-05 | Sistem harus menyediakan reset password melalui tautan email yang berbatas waktu dan sekali pakai. Jika email yang dimasukkan tidak terdaftar, sistem harus menampilkan pesan "Email tidak terdaftar" agar pengguna dapat memperbaiki salah ketik. | Semua | US-03 | BR-03 |
| FR-06 | Sistem harus memungkinkan Super Admin membuat akun admin (nama, email, *role*), mengirim undangan aktivasi, mengubah *role*, serta menonaktifkan akun beserta sesi dan token mobile aktifnya. | Super Admin | US-27 | BR-04, BR-05, BR-60 |
| FR-07 | Sistem harus memeriksa hak akses di setiap *endpoint* dan halaman sesuai Matriks Hak Akses. Akses tidak sah dijawab HTTP 403. | Sistem | US-09, US-27 | BR-04 |
 
### 2.2 Modul Informasi Publik
 
| ID | Kebutuhan | Aktor | US | BR |
|---|---|---|---|---|
| FR-08 | Sistem harus menampilkan katalog publik (tanpa login) berisi peminatan aktif per track beserta durasi semester, tarif formulir, dan tarif per semester yang berlaku. Katalog tidak menampilkan kuota, sisa kuota, maupun jumlah pendaftar (BR-75). | Publik | US-01 | BR-07, BR-27, BR-55, BR-75 |
| FR-09 | Sistem harus menonaktifkan tombol daftar dan menampilkan jadwal periode berikutnya saat tidak ada periode pendaftaran yang dibuka. | Publik | US-01 | BR-54 |
| FR-10 | Sistem harus menyediakan CMS bagi Super Admin untuk mengedit, mempratinjau, dan menerbitkan konten *landing page* (jadwal, pengumuman, alur, kontak bantuan). | Super Admin | US-29 | BR-55, BR-58 |
 
### 2.3 Modul Pendaftaran
 
| ID | Kebutuhan | Aktor | US | BR |
|---|---|---|---|---|
| FR-11 | Sistem harus menyediakan formulir pendaftaran dengan pemilihan satu track dan satu peminatan, *field* nama lengkap, NIK, tempat & tanggal lahir, jenis kelamin, no. HP, alamat, asal sekolah, dan tahun lulus, fitur simpan sebagai Draf, dan pengajuan formulir. Saat Draf pertama kali disimpan, sistem membangkitkan nomor pendaftaran. Draf hanya dapat diakses pemiliknya. | Calon Peserta | US-04 | BR-07, BR-08, BR-09, BR-62, BR-64, BR-68 |
| FR-12 | Sistem harus memvalidasi kelengkapan *field* wajib dan dokumen wajib sebelum formulir diajukan, serta menandai bagian yang belum lengkap. | Sistem | US-04 | BR-11 |
| FR-13 | Sistem harus mengunci pilihan track dan peminatan setelah formulir berstatus Diajukan. | Sistem | US-04 | BR-09 |
| FR-14 | Sistem harus menyediakan slot unggah per jenis dokumen, memvalidasi format dan ukuran file, serta menampilkan pratinjau dokumen. | Calon Peserta | US-05 | BR-11, BR-12 |
| FR-15 | Sistem harus menampilkan *progress tracker* pendaftaran (Formulir Diajukan → Verifikasi Berkas → Masuk Kuota → Verifikasi Pembayaran Formulir → NPD Terbit), termasuk status Dibatalkan beserta penjelasannya. | Calon Peserta | US-07 | BR-17, BR-21, BR-24 |
| FR-16 | Sistem harus menampilkan alasan penolakan per dokumen dan memungkinkan unggah ulang hanya untuk dokumen berstatus Ditolak. Setelah unggah ulang, status pendaftaran kembali menjadi Diajukan. | Calon Peserta | US-08 | BR-14 |
| FR-17 | Sistem harus menyimpan riwayat seluruh versi dokumen dan riwayat penolakannya (alasan dan waktu). | Sistem | US-08 | BR-13 |
 
### 2.4 Modul Verifikasi Berkas
 
| ID | Kebutuhan | Aktor | US | BR |
|---|---|---|---|---|
| FR-18 | Sistem harus menampilkan antrean pendaftaran berstatus Diajukan, diurutkan dari pengajuan terlama, dengan filter periode, track, peminatan, dan status. Antrean tidak menyediakan aksi verifikasi massal. | Admin SILAU | US-09 | BR-04, BR-64, BR-66 |
| FR-19 | Sistem harus menampilkan dokumen dalam *document viewer* di aplikasi beserta kuota minimum dan jumlah NPD Terbit peminatan pendaftar (informasi admin). | Admin SILAU | US-09 | BR-23, BR-75 |
| FR-20 | Sistem harus menampilkan *checklist* kriteria per jenis dokumen dan menerima keputusan Valid atau Ditolak per dokumen. Alasan wajib diisi untuk keputusan Ditolak. | Admin SILAU | US-10 | BR-13, BR-58, BR-66 |
| FR-21 | Saat verifikasi diselesaikan, sistem harus menetapkan status: Perlu Revisi jika ada dokumen Ditolak; Masuk Kuota jika seluruh dokumen wajib Valid. | Sistem | US-10, US-11 | BR-17 |
| FR-22 | *(Dihapus, K-42)* Penempatan kuota atomik tidak diperlukan karena tidak ada kuota maksimum. | Sistem | US-11 | BR-17 |
| FR-23 | *(Dihapus, K-42)* Promosi Daftar Tunggu ditiadakan. | Sistem | US-11 | — |
| FR-24 | Sistem harus menjalankan proses terjadwal (minimal harian) yang membatalkan pendaftaran Masuk Kuota yang melewati batas 7 hari tanpa pelunasan formulir. | Sistem | US-11 | BR-21 |
| FR-25 | Saat periode pendaftaran ditutup, sistem harus (a) mengubah seluruh Draf pada periode tersebut menjadi Dibatalkan dengan alasan tercatat serta mengunci formulirnya, (b) menandai peminatan yang jumlah pendaftar terverifikasinya di bawah kuota minimum untuk diputuskan Super Admin (FR-39), lalu (c) memicu pembuatan arsip periode (FR-65). Seluruh pemilik pendaftaran terdampak menerima notifikasi. | Sistem | US-04, US-18, US-32 | BR-23, BR-65, BR-69 |
 
### 2.5 Modul Tagihan & Pembayaran
 
| ID | Kebutuhan | Aktor | US | BR |
|---|---|---|---|---|
| FR-26 | Sistem harus menerbitkan tagihan formulir saat formulir diajukan, berisi kode referensi, nominal (tarif formulir peminatan + kode unik), rekening tujuan dari rekening institusi aktif untuk jenis formulir (disimpan sebagai *snapshot*), dan status Belum Bayar. | Sistem | US-06 | BR-25, BR-27–BR-32, BR-70, BR-71 |
| FR-27 | Sistem harus menerbitkan tagihan Semester 1 saat NPD terbit dan tagihan Semester *N*+1 saat *rollover*, memakai tarif semester dan rekening institusi aktif untuk jenis semester yang berlaku, lengkap dengan jatuh tempo, tanpa duplikasi. | Sistem | US-14 | BR-27, BR-28, BR-32–BR-35, BR-71 |
| FR-28 | Sistem harus membangkitkan kode unik 3 digit dan kode referensi tagihan sesuai aturan keunikan. | Sistem | US-06 | BR-29, BR-31 |
| FR-29 | Sistem harus memungkinkan pengguna memilih tagihan tertentu, mengisi data pengiriman (tanggal transfer, nominal yang ditransfer, bank pengirim, nama pengirim), lalu mengunggah bukti transfer, kemudian mengubah status tagihan menjadi Menunggu Verifikasi. Jika nominal yang diisi berbeda dari nominal tagihan, sistem menampilkan peringatan. | Calon Peserta, Peserta Didik | US-06 | BR-36, BR-72 |
| FR-30 | Sistem harus menghitung *hash* setiap file bukti dan menolak unggahan yang *hash*-nya sudah pernah tercatat. | Sistem | US-06 | BR-37 |
| FR-31 | Sistem harus menyediakan antrean verifikasi pembayaran dengan filter jenis tagihan (formulir / semester ke-*n*), peminatan, periode, dan status, serta menampilkan kode referensi, nominal, data pengiriman, dan pratinjau bukti. Antrean tidak menyediakan aksi verifikasi massal. | Admin Keuangan | US-12, US-16 | BR-04, BR-66, BR-72 |
| FR-32 | Sistem harus memungkinkan Admin Keuangan menandai tagihan Lunas atau Ditolak (dengan alasan wajib). Status Ditolak membuka kembali unggahan bukti. | Admin Keuangan | US-13 | BR-36, BR-38, BR-58, BR-66 |
| FR-33 | Sistem harus menolak penandaan Lunas pada tagihan formulir yang pendaftarannya belum berstatus Masuk Kuota, dan menampilkan keterangan "Menunggu verifikasi berkas". | Sistem | US-12 | BR-39 |
| FR-34 | Saat tagihan formulir Lunas, sistem harus secara atomik: menerbitkan NPD, mengubah status pendaftaran menjadi NPD Terbit, mengubah *role* akun menjadi Peserta Didik, menetapkan status Belum Aktif Semester 1, menerbitkan tagihan Semester 1, dan mengirim notifikasi berisi NPD. | Sistem | US-12, US-14 | BR-06, BR-41, BR-42 |
| FR-35 | Saat tagihan semester *N* Lunas, sistem harus mengubah status peserta menjadi Aktif Semester *N*, termasuk bagi peserta berstatus Tertahan. | Sistem | US-16 | BR-42, BR-46 |
| FR-36 | Sistem harus memungkinkan Super Admin mengoreksi tagihan berstatus Lunas dengan alasan wajib. | Super Admin | US-13 | BR-40, BR-58 |
| FR-37 | Sistem harus menampilkan dasbor tagihan peserta (jenis, kode referensi, nominal, jatuh tempo, status, tanggal pelunasan) dan hanya menampilkan tagihan milik akun tersebut. | Calon Peserta, Peserta Didik | US-15 | BR-04 |
| FR-38 | Sistem harus menyediakan dasbor rekap pembayaran (jumlah dan total nominal per status, termasuk tunggakan) dengan filter periode, peminatan, angkatan, dan jenis tagihan, serta ekspor ke Excel dan PDF. | Admin Keuangan | US-17 | — |
 
### 2.6 Modul Kuota, Pembatalan & Refund
 
| ID | Kebutuhan | Aktor | US | BR |
|---|---|---|---|---|
| FR-39 | Sistem harus menampilkan evaluasi kuota minimum setelah periode ditutup, berisi jumlah pendaftar terverifikasi dibandingkan kuota minimum per peminatan, dan menandai peminatan yang tidak memenuhi. | Super Admin | US-18 | BR-23 |
| FR-40 | Sistem harus memungkinkan Super Admin membatalkan peminatan dengan memilih dasar pembatalan (kuota minimum / kesepakatan terbaru) dan alasan wajib untuk dasar kesepakatan. | Super Admin | US-18 | BR-24, BR-58 |
| FR-41 | Saat peminatan dibatalkan, sistem harus mengubah seluruh pendaftaran terkait menjadi Dibatalkan, mengubah status Peserta Didik terkait menjadi Dibatalkan, membatalkan tagihan yang belum Lunas, membuat refund bagi yang sudah membayar, dan mengirim notifikasi. | Sistem | US-18 | BR-47, BR-48 |
| FR-42 | Sistem harus menghitung nominal refund secara otomatis sesuai aturan refund, dari rincian tagihan yang belum pernah masuk refund lain. | Sistem | US-18, US-20 | BR-48, BR-63 |
| FR-43 | Sistem harus menyediakan formulir data rekening refund (nama bank, nomor rekening, nama pemilik), menyimpan nomor rekening terenkripsi, dan menampilkan status refund kepada pendaftar. | Calon Peserta, Peserta Didik | US-19 | BR-50, BR-51 |
| FR-44 | Sistem harus menyediakan modul refund bagi Admin Keuangan untuk melihat daftar refund, mencocokkan pembayaran yang belum terverifikasi, dan menandai refund Selesai dengan bukti transfer. Jika seluruh rincian refund dikecualikan, sistem harus otomatis mengubah status refund menjadi Ditolak dengan alasan dan mengirim notifikasi ke penerima. | Admin Keuangan | US-20 | BR-49, BR-50, BR-51 |
 
### 2.7 Modul Data Master
 
| ID | Kebutuhan | Aktor | US | BR |
|---|---|---|---|---|
| FR-45 | Sistem harus menyediakan CRUD track (kode, nama, status aktif/nonaktif). | Admin Track, Super Admin | US-21 | BR-52, BR-53 |
| FR-46 | Sistem harus menyediakan CRUD peminatan (kode, nama, track induk, durasi semester, status aktif/nonaktif). | Admin Track, Super Admin | US-21 | BR-43, BR-52, BR-53 |
| FR-47 | Sistem harus menyediakan pengelolaan tarif berversi per peminatan (tarif formulir dan tarif semester 1 s.d. *D*) dengan tanggal berlaku efektif dan riwayat versi (pengubah, waktu). | Admin Track, Super Admin | US-22 | BR-25–BR-27, BR-58 |
| FR-48 | Sistem harus menyediakan pengaturan kuota minimum per peminatan per periode. Setelah periode dimulai, kuota minimum hanya dapat diubah Super Admin dengan alasan wajib. | Admin Track, Super Admin | US-23 | BR-15, BR-20 |
| FR-49 | Sistem harus menampilkan dasbor kuota per peminatan untuk admin (jumlah pendaftaran Diajukan, Masuk Kuota, NPD Terbit, kuota minimum, persentase NPD Terbit terhadap kuota minimum), dengan penanda peringatan bila ≤ 7 hari sebelum periode ditutup jumlah NPD Terbit masih di bawah kuota minimum. | Admin Track, Super Admin | US-24 | BR-23, BR-75 |
 
### 2.8 Modul Periode & Rollover
 
| ID | Kebutuhan | Aktor | US | BR |
|---|---|---|---|---|
| FR-50 | Sistem harus menyediakan pengelolaan periode pendaftaran dan periode akademik (nama, tanggal mulai, tanggal selesai), menghitung jatuh tempo tagihan semester secara otomatis (H-7 sebelum tanggal mulai), serta membuka dan menutup pendaftaran otomatis sesuai tanggal. | Super Admin | US-25 | BR-35, BR-54 |
| FR-51 | Sistem harus menampilkan pratinjau *rollover* berisi jumlah dan daftar peserta yang akan naik semester, Selesai Masa Program, dan Tertahan. | Super Admin | US-26 | BR-45 |
| FR-52 | Sistem harus mengeksekusi *rollover* sesuai aturan transisi secara atomik, menolak eksekusi ulang untuk periode akademik yang sama, dan memicu FR-27 untuk peserta yang naik semester. | Super Admin | US-26 | BR-44, BR-45, BR-58 |
 
### 2.9 Modul Notifikasi & Audit
 
| ID | Kebutuhan | Aktor | US | BR |
|---|---|---|---|---|
| FR-53 | Sistem harus mengirim notifikasi email dan *in-app* untuk setiap perubahan status, serta menampilkan daftar notifikasi di dasbor pengguna. | Sistem | US-07 | BR-56 |
| FR-54 | Sistem harus mengirim pengingat tagihan otomatis pada H-7 dan H-1 sebelum jatuh tempo. | Sistem | US-15 | BR-57 |
| FR-55 | Sistem harus mencatat setiap aktivitas krusial ke *audit log* (pelaku, *role*, kanal web/mobile, waktu, entitas, nilai sebelum/sesudah, alasan). | Sistem | US-28 | BR-58 |
| FR-56 | Sistem harus menyediakan penelusuran *audit log* dengan filter pelaku, jenis aktivitas, entitas, dan rentang tanggal, tanpa fungsi ubah atau hapus. | Super Admin | US-28 | BR-58 |
| FR-57 | Sistem harus menampilkan daftar tagihan semester yang belum memiliki jatuh tempo, mengirim notifikasi ke Super Admin saat tagihan seperti itu terbit, dan memungkinkan Super Admin menetapkan jatuh tempo secara manual (per tagihan atau massal). Penetapan dicatat di *audit log*. | Super Admin | US-14, US-15 | BR-35, BR-58 |
| FR-58 | Sistem harus mengirim peringatan ke Super Admin jika *rollover* periode akademik belum dijalankan pada H-14 sebelum semester dimulai, dan mengulanginya setiap hari hingga *rollover* dijalankan. | Sistem | US-26 | BR-44 |
 
### 2.10 Modul Keamanan Akun, Aplikasi Mobile & Refund Bertahap
 
| ID | Kebutuhan | Aktor | US | BR |
|---|---|---|---|---|
| FR-59 | Sistem harus mewajibkan pendaftaran *authenticator* TOTP saat aktivasi akun admin dan meminta kode TOTP setiap login admin (web dan mobile), serta menyediakan kode cadangan (*recovery code*). | Semua admin | US-03, US-27 | BR-59 |
| FR-60 | Sistem harus menyediakan REST API v1 untuk aplikasi mobile yang mencakup login + TOTP, logout, dasbor, notifikasi *in-app*, antrean dan detail verifikasi berkas, antrean dan detail verifikasi pembayaran, tautan dokumen bertanda tangan, serta penyimpanan keputusan verifikasi. *Role* selain Admin SILAU dan Admin Keuangan dijawab HTTP 403. | Admin SILAU, Admin Keuangan | US-30 | BR-60 |
| FR-61 | Sistem harus menolak keputusan verifikasi berkas atau pembayaran (web dan mobile) jika data sudah diputuskan admin lain sejak dibuka, menjawab HTTP 409 `SUDAH_DIVERIFIKASI` di API, dan menampilkan data terbaru. | Sistem | US-10, US-12, US-30 | BR-61 |
| FR-62 | Sistem harus menyimpan setiap refund beserta rincian tagihannya (tagihan yang dikembalikan atau dikecualikan beserta alasannya), mendukung lebih dari satu refund per pendaftaran, dan menolak tagihan yang sudah menjadi rincian refund lain. | Sistem | US-19, US-20 | BR-49, BR-63 |
 
### 2.11 Modul Data Pendaftaran & Arsip (Admin)
 
| ID | Kebutuhan | Aktor | US | BR |
|---|---|---|---|---|
| FR-63 | Sistem harus menyediakan halaman **Data Pendaftaran** yang menampilkan seluruh pendaftaran yang sudah diajukan (semua status kecuali Draf) dengan kolom nomor pendaftaran, nama, track, peminatan, waktu pengajuan, dan status pendaftaran; filter periode, track, peminatan, status, dan rentang tanggal pengajuan; pencarian nama, nomor pendaftaran, atau NIK (pencocokan *hash*); pengurutan kolom; paginasi; jumlah total data; serta jumlah Draf pada periode terpilih sebagai angka saja. Setiap baris membuka detail pendaftaran secara baca-saja. Halaman ini tidak menyediakan aksi verifikasi; aksi massal hanya **ekspor baris terpilih** (FR-64). | Admin SILAU, Admin Keuangan, Admin Track, Super Admin | US-31 | BR-04, BR-62, BR-64, BR-66, BR-68 |
| FR-64 | Sistem harus menyediakan ekspor data pendaftaran ke Excel dan PDF, untuk seluruh hasil filter aktif atau hanya baris terpilih, dengan kolom sesuai FR-63 dan NIK tersamar. Ekspor > 10.000 baris diproses di latar belakang dan tautan unduhan dikirim melalui notifikasi. File hasil ekspor dan tautannya berlaku 24 jam, lalu file dihapus otomatis. Setiap ekspor dicatat di *audit log*. *Role* selain Admin SILAU dan Super Admin dijawab HTTP 403. | Admin SILAU, Super Admin | US-31 | BR-58, BR-64, BR-67 |
| FR-65 | Saat periode pendaftaran ditutup, sistem harus membuat **arsip periode** secara otomatis di latar belakang: satu file Excel berisi lembar data pendaftaran (kolom FR-63 ditambah alasan batal, NIK tersamar, tanpa Draf) dan lembar ringkasan (jumlah per peminatan dan per status, jumlah Draf yang dibatalkan), lalu menyimpannya di penyimpanan privat permanen beserta metadata (periode, waktu dibuat, jumlah baris, ukuran, *checksum* SHA-256). Proses harus idempoten (satu arsip per periode); jika gagal, proses dicoba ulang dan Super Admin menerima notifikasi. Pembuatan arsip dicatat di *audit log* sebagai aksi sistem. | Sistem | US-32 | BR-58, BR-64, BR-69 |
| FR-66 | Sistem harus menyediakan halaman **Arsip Periode** yang menampilkan daftar arsip (periode, waktu dibuat, jumlah baris, ukuran, status) dan tombol unduh. Sebelum file dikirim, sistem memeriksa *checksum* SHA-256; jika tidak cocok, unduhan dibatalkan dan Super Admin diberi peringatan. Setiap unduhan dicatat di *audit log*. Halaman tidak menyediakan aksi ubah atau hapus arsip. *Role* selain Admin SILAU dan Super Admin dijawab HTTP 403. | Admin SILAU, Super Admin | US-32 | BR-04, BR-58, BR-69 |
 
### 2.12 Modul Rekening Institusi, Kartu Pendaftar & Fast Verify
 
| ID | Kebutuhan | Aktor | US | BR |
|---|---|---|---|---|
| FR-67 | Sistem harus menyediakan halaman **Rekening Institusi** yang menampilkan daftar rekening (bank, nomor rekening, atas nama, jenis tagihan yang dilayani, status) serta formulir tambah dan ubah rekening, aktifkan, dan nonaktifkan. Mengaktifkan rekening untuk satu jenis tagihan otomatis menonaktifkan rekening aktif sebelumnya untuk jenis yang sama, setelah konfirmasi. Rekening yang pernah dipakai tagihan tidak dapat dihapus. Setiap perubahan dicatat di *audit log*. *Role* selain Admin Keuangan dan Super Admin dijawab HTTP 403. | Admin Keuangan, Super Admin | US-06, US-12 | BR-58, BR-70, BR-71 |
| FR-68 | Sistem harus menyediakan unduhan **kartu pendaftar** (PDF) di dasbor dan halaman status bagi pemilik pendaftaran yang formulirnya sudah Diajukan, berisi nomor pendaftaran, nama lengkap, track, peminatan, periode, waktu pengajuan, dan status terkini saat diunduh. Tombol tidak tersedia untuk Draf, dan pengguna lain dijawab HTTP 403. | Calon Peserta, Peserta Didik | US-07 | BR-64, BR-73 |
| FR-69 | Aplikasi mobile harus menyediakan **Fast Verify**: Admin SILAU memeriksa antrean berkas satu dokumen per kartu (pratinjau, *checklist*, geser kanan Valid / kiri Ditolak + alasan, ringkasan dan Selesaikan per pendaftar); Admin Keuangan memeriksa tagihan yang siap diputuskan satu per kartu (bukti, data pengiriman, kecocokan nominal, geser kanan → konfirmasi Lunas / kiri → alasan penolakan). Tersedia tombol Tolak/Valid (atau Tolak/Lunas) sebagai alternatif gestur, penanda progres, dan layar ringkasan saat antrean habis. API memakai *endpoint* keputusan yang sama dengan FR-60. | Admin SILAU, Admin Keuangan | US-30 | BR-39, BR-61, BR-66, BR-74 |
 
---
 
## 3. Matriks Hak Akses (RBAC)
 
**C** = Create, **R** = Read, **U** = Update, **V** = Verifikasi/Putuskan, **X** = Eksekusi, **—** = tidak ada akses. "Milik sendiri" berarti terbatas pada data akun tersebut.
 
| Fitur | Calon Peserta / Peserta Didik | Admin SILAU | Admin Keuangan | Admin Track | Super Admin |
|---|---|---|---|---|---|
| Katalog & *landing page* (lihat) | R | R | R | R | R |
| CMS *landing page* | — | — | — | — | CRU |
| Formulir & dokumen pendaftaran | CRU (milik sendiri, termasuk Draf) | R (tanpa Draf) | R (tanpa Draf) | R (tanpa Draf) | R (tanpa Draf) |
| Kartu pendaftar (unduh) | R (milik sendiri, setelah Diajukan) | — | — | — | — |
| Daftar Data Pendaftaran (tanpa Draf, jumlah Draf sebagai angka) | — | R | R | R | R |
| Ekspor data pendaftaran | — | X | — | — | X |
| Arsip periode (lihat daftar & unduh; tanpa ubah/hapus) | — | R | — | — | R |
| Verifikasi dokumen (satu per satu) | — | V | — | — | V |
| Tagihan & unggah bukti (dengan data pengiriman) | R, unggah (milik sendiri) | — | R | — | R |
| Rekening institusi | R (pada instruksi tagihan) | — | CRU | — | CRU |
| Verifikasi pembayaran (satu per satu) | — | — | V | — | V |
| Koreksi status Lunas | — | — | — | — | U |
| Penetapan jatuh tempo manual | — | — | — | — | U |
| Rekap & ekspor keuangan | — | — | R | — | R |
| Track & peminatan | — | — | — | CRU | CRU |
| Tarif berversi | — | — | R | CRU | CRU |
| Kuota minimum (atur, ubah) | — | R | — | CRU | CRU |
| Ubah kuota minimum saat periode berjalan | — | — | — | — | U |
| Dasbor kuota | — | R | — | R | R |
| Evaluasi kuota & pembatalan peminatan | — | — | — | R | X |
| Data rekening refund | CRU (milik sendiri, tersamar) | — | R (lengkap) | — | R (lengkap) |
| Proses refund | R (status milik sendiri) | — | X | — | X |
| Periode pendaftaran & akademik | — | — | — | — | CRU |
| *Rollover* semester | — | — | — | — | X |
| Akun & *role* admin | — | — | — | — | CRU |
| *Audit log* | — | — | — | — | R |
| Aplikasi mobile (verifikasi, termasuk Fast Verify) | — | V (berkas) | V (pembayaran) | — | — |
 
---
 
## 4. Data & State Notes
 
### 4.1 Entitas Utama
 
| Entitas | Atribut Kunci |
|---|---|
| **Akun** | email, password_hash, role, status_verifikasi_email, status_aktif, two_factor_secret (wajib untuk admin) |
| **Track** | kode, nama, status_aktif |
| **Peminatan** | kode, nama, track_id, durasi_semester, status_aktif |
| **Tarif** | peminatan_id, jenis (formulir / semester), semester_ke, nominal, tanggal_efektif, dibuat_oleh |
| **PeriodePendaftaran** | nama, tanggal_mulai, tanggal_selesai |
| **Kuota** | peminatan_id, periode_id, kuota_min, kuota_maks |
| **Pendaftaran** | nomor_pendaftaran (unik, BR-68), akun_id, periode_id, peminatan_id, status, data formulir (nama lengkap, NIK terenkripsi + nik_hash, tempat & tanggal lahir, jenis kelamin, no. HP, alamat, asal sekolah, tahun lulus), waktu_pengajuan, waktu_masuk_kuota, nomor_urut_tunggu, durasi_snapshot, alasan_batal |
| **Dokumen** | pendaftaran_id, jenis, file, status, alasan_tolak, versi |
| **PesertaDidik** | akun_id, pendaftaran_id, NPD, semester_berjalan, status |
| **PeriodeAkademik** | nama, tanggal_mulai, tanggal_selesai, status_rollover (jatuh tempo diturunkan dari tanggal_mulai) |
| **RekeningInstitusi** | nama_bank, nomor_rekening, atas_nama, jenis_tagihan (formulir / semester), status_aktif, dibuat_oleh, diubah_oleh |
| **Tagihan** | kode_referensi, pemilik_id, jenis, semester_ke, nominal_dasar, kode_unik, nominal_total, jatuh_tempo, status, rekening_institusi_id, *snapshot* rekening tujuan (bank, nomor, atas nama) |
| **BuktiPembayaran** | tagihan_id, file, file_hash, waktu_unggah, tanggal_transfer, nominal_transfer, bank_pengirim, nama_pengirim, keputusan, alasan, diverifikasi_oleh |
| **Refund** | pendaftaran_id (satu pendaftaran dapat memiliki beberapa refund), sebab, nominal, bank, no_rekening_terenkripsi, nama_pemilik, status, alasan_ditolak, bukti_transfer |
| **RincianRefund** | refund_id, tagihan_id (unik), nominal_dasar, disertakan/dikecualikan, alasan |
| **ArsipPeriode** | periode_pendaftaran_id (unik), status (diproses / tersedia / gagal), path, nama_file, jumlah_baris, ukuran_byte, sha256, dibuat_pada |
| **AuditLog** | pelaku_id, role, kanal, aksi, entitas, nilai_sebelum, nilai_sesudah, alasan, waktu |
| **Notifikasi** | akun_id, jenis, isi, kanal, status_baca, waktu |
 
### 4.2 Transisi Status Pendaftaran
 
| Dari | Ke | Pemicu | BR |
|---|---|---|---|
| — | Draf | Calon Peserta menyimpan formulir (nomor pendaftaran dibangkitkan) | BR-64, BR-68 |
| Draf | Diajukan | Formulir diajukan | BR-07 |
| Draf | Dibatalkan | Periode pendaftaran ditutup sebelum formulir diajukan | BR-65 |
| Diajukan | Perlu Revisi | Ada dokumen Ditolak | BR-13 |
| Perlu Revisi | Diajukan | Dokumen yang ditolak diunggah ulang | BR-14 |
| Diajukan | Masuk Kuota | Semua dokumen wajib Valid | BR-17 |
| Masuk Kuota | NPD Terbit | Tagihan formulir Lunas | BR-39, BR-41 |
| Masuk Kuota | Dibatalkan | 7 hari tanpa pelunasan formulir | BR-21 |
| Semua status non-akhir dan NPD Terbit | Dibatalkan | Peminatan dibatalkan | BR-24 |
 
### 4.3 Transisi Status Peserta Didik
 
| Dari | Ke | Pemicu | BR |
|---|---|---|---|
| — | Belum Aktif Semester 1 | NPD terbit | BR-42 |
| Belum Aktif Semester *N* | Aktif Semester *N* | Tagihan semester *N* Lunas | BR-42 |
| Aktif Semester *N* (*N* < *D*) | Belum Aktif Semester *N*+1 | *Rollover* | BR-45 |
| Aktif Semester *N* (*N* = *D*) | Selesai Masa Program | *Rollover* | BR-45 |
| Belum Aktif Semester *N* | Tertahan (Tunggakan) | *Rollover* dengan tagihan *N* belum Lunas | BR-45 |
| Tertahan (Tunggakan) | Aktif Semester *N* | Tagihan semester *N* Lunas | BR-46 |
| Belum Aktif / Aktif / Tertahan | Dibatalkan | Peminatan dibatalkan | BR-24 |
 
### 4.4 Transisi Status Tagihan
 
| Dari | Ke | Pemicu | BR |
|---|---|---|---|
| — | Belum Bayar | Tagihan diterbitkan (rekening institusi aktif tersedia) | BR-32, BR-71 |
| Belum Bayar / Ditolak | Menunggu Verifikasi | Bukti dan data pengiriman diunggah | BR-36, BR-72 |
| Menunggu Verifikasi | Lunas | Admin Keuangan memverifikasi (web, mobile, atau Fast Verify) | BR-39, BR-74 |
| Menunggu Verifikasi | Ditolak | Admin Keuangan menolak dengan alasan | BR-38 |
| Belum Bayar / Ditolak / Menunggu Verifikasi | Dibatalkan | Peminatan dibatalkan atau pendaftaran dibatalkan | BR-21, BR-24 |
| Lunas | (koreksi) | Super Admin dengan alasan | BR-40 |
 
### 4.5 Transisi Status Refund
 
| Dari | Ke | Pemicu | BR |
|---|---|---|---|
| — | Menunggu Data Rekening | Refund dibuat (peminatan dibatalkan) | BR-47 |
| Menunggu Data Rekening | Diproses | Penerima mengisi data rekening | BR-50, BR-51 |
| Diproses | Selesai | Admin Keuangan menandai Selesai dengan bukti transfer | BR-51 |
| Menunggu Data Rekening / Diproses | Ditolak | Seluruh rincian dikecualikan (nominal Rp0) | BR-49, BR-51 |
 
### 4.6 Transisi Status Arsip Periode
 
| Dari | Ke | Pemicu | BR |
|---|---|---|---|
| — | Diproses | Periode pendaftaran ditutup | BR-69 |
| Diproses | Tersedia | File dibuat dan *checksum* tersimpan | BR-69 |
| Diproses | Gagal | Pembuatan gagal setelah seluruh percobaan ulang | BR-69 |
| Gagal | Diproses | Percobaan ulang (otomatis atau dipicu Super Admin) | BR-69 |
 
Status Tersedia bersifat final: arsip tidak dapat dibuat ulang, ditimpa, atau dihapus.
 
### 4.7 Transisi Status Rekening Institusi
 
| Dari | Ke | Pemicu | BR |
|---|---|---|---|
| — | Nonaktif | Rekening ditambahkan | BR-70 |
| Nonaktif | Aktif | Admin Keuangan / Super Admin mengaktifkan (rekening aktif lain untuk jenis yang sama menjadi Nonaktif) | BR-70 |
| Aktif | Nonaktif | Dinonaktifkan manual atau digantikan rekening lain | BR-70, BR-71 |
 
---
 
## 5. Matriks Keterlacakan US → FR
 
| US | FR |
|---|---|
| US-01 | FR-08, FR-09 |
| US-02 | FR-01, FR-02 |
| US-03 | FR-03, FR-04, FR-05, FR-59 |
| US-04 | FR-11, FR-12, FR-13, FR-25 |
| US-05 | FR-14 |
| US-06 | FR-26, FR-28, FR-29, FR-30, FR-67 |
| US-07 | FR-15, FR-53, FR-68 |
| US-08 | FR-16, FR-17 |
| US-09 | FR-07, FR-18, FR-19 |
| US-10 | FR-20, FR-21, FR-61 |
| US-11 | FR-21, FR-22, FR-23, FR-24, FR-25 |
| US-12 | FR-31, FR-33, FR-34, FR-61, FR-67 |
| US-13 | FR-32, FR-36 |
| US-14 | FR-27, FR-34, FR-57 |
| US-15 | FR-37, FR-54, FR-57 |
| US-16 | FR-31, FR-35 |
| US-17 | FR-38 |
| US-18 | FR-39, FR-40, FR-41, FR-42 |
| US-19 | FR-43, FR-62 |
| US-20 | FR-42, FR-44, FR-62 |
| US-21 | FR-45, FR-46 |
| US-22 | FR-47 |
| US-23 | FR-48 |
| US-24 | FR-49 |
| US-25 | FR-50 |
| US-26 | FR-51, FR-52, FR-58 |
| US-27 | FR-06, FR-07, FR-59 |
| US-28 | FR-55, FR-56 |
| US-29 | FR-10 |
| US-30 | FR-60, FR-61, FR-69 |
| US-31 | FR-63, FR-64 |
| US-32 | FR-25, FR-65, FR-66 |
 
Seluruh *user story* US-01 s.d. US-32 tercakup oleh minimal satu FR. FR-67 dan FR-68 sementara ditautkan ke US-06, US-07, dan US-12 sampai *user story* khusus Rekening Institusi ditambahkan (K-40). FR-69 ditautkan ke US-30 sampai *acceptance criteria* Fast Verify ditambahkan (K-41).
 
---
 
## 6. Status Asumsi
 
| # | Asumsi | Dipakai di | Status |
|---|---|---|---|
| A-19 | Kode unik 3 digit bernilai 001–999, dibangkitkan acak, dan unik di antara tagihan terbuka dengan nominal dasar yang sama. | BR-29 | Disetujui (K-23) |
| A-20 | Format NPD sementara: `MC-{TAHUN}-{KODE_PEMINATAN}-{NO_URUT_4_DIGIT}`. | BR-41 | Disetujui sebagai *dummy* (K-23) |
| A-21 | Jatuh tempo tagihan semester = H-7 sebelum semester dimulai. | BR-35, FR-50 | Disetujui (K-23) |
| A-22 | Pembayaran formulir yang tidak ditemukan di mutasi rekening dikeluarkan dari nominal refund dengan alasan tercatat. | BR-49 | Disetujui (K-23) |
| A-23 | Tagihan yang terbit pada atau setelah H-7 sebelum semester dimulai diberi jatuh tempo secara manual. | BR-35, FR-57 | Disetujui (K-24) |
| A-24 | Peringatan kepada Super Admin jika *rollover* belum dijalankan pada H-14 sebelum semester dimulai. | BR-44, FR-58 | Disetujui (K-24) |
| A-25 | Jatuh tempo manual hanya ditetapkan oleh Super Admin. | BR-35, FR-57 | Disetujui (K-25) |
| A-44 | Reset password menampilkan pesan "Email tidak terdaftar" untuk email yang tidak terdaftar. | FR-05 | Diputuskan (K-32) |
| A-45 | Jatuh tempo manual boleh lebih awal dari tanggal penetapan, sesuai kesepakatan. | BR-35, FR-57 | Diputuskan (K-32) |
| A-48 | Peserta Didik ikut berstatus Dibatalkan saat peminatannya dibatalkan. | BR-24, FR-41 | Disetujui (K-34) |
| A-49 | Refund yang seluruh rinciannya dikecualikan berstatus Ditolak dengan alasan. | BR-51, FR-44 | Disetujui (K-34) |
| A-57 | Nomor pendaftaran unik `PV-{TAHUN}-{NO_URUT_6_DIGIT}`, dibangkitkan saat Draf pertama kali disimpan dan tidak berubah. | BR-68, FR-11, FR-63 | Disetujui (K-37) |
| A-58 | Ekspor data pendaftaran hanya untuk Admin SILAU dan Super Admin. Admin Keuangan memakai ekspor rekap keuangan (FR-38); Admin Track memakai dasbor kuota (FR-49). | BR-67, FR-64, RBAC | Disetujui (K-37) |
| A-59 | File hasil ekspor dan tautan unduhannya berlaku 24 jam, lalu file dihapus otomatis. | BR-67, FR-64 | Disetujui (K-38) |
| A-60 | Arsip periode dibuat otomatis saat periode pendaftaran ditutup dan disimpan permanen, dikecualikan dari retensi sementara 5 tahun (K-26). | BR-69, FR-65, FR-66 | Disetujui (K-38) |
| A-61 | `[ASUMSI]` Arsip periode berformat Excel (.xlsx) saja, berisi kondisi status per waktu penutupan; perubahan status setelah penutupan (mis. NPD terbit, pembatalan peminatan) tidak mengubah arsip dan tetap dapat dilihat melalui Data Pendaftaran. | BR-69, FR-65 | Menunggu konfirmasi |
| A-62 | `[ASUMSI]` Rekening institusi dipisah per jenis tagihan (formulir, semester) dengan satu rekening aktif per jenis; satu rekening boleh aktif untuk kedua jenis sekaligus. Nomor rekening institusi bukan data pribadi sehingga tidak dienkripsi dan ditampilkan lengkap pada instruksi transfer. | BR-70, BR-71, FR-67 | Menunggu konfirmasi |
| A-63 | `[ASUMSI]` Kartu pendaftar dibangkitkan saat diunduh (bukan disimpan), memakai status terkini, dan tidak memuat QR/kode verifikasi. | BR-73, FR-68 | Menunggu konfirmasi |
| A-64 | `[ASUMSI]` Alasan penolakan di mobile dapat dibentuk dari kriteria *checklist* yang tidak terpenuhi (berkas) atau daftar alasan baku — mutasi tidak ditemukan, nominal tidak sesuai, bukti tidak terbaca (pembayaran) — ditambah catatan bebas; minimal salah satu wajib diisi. | BR-13, BR-38, BR-74 | Disetujui (26-11-2026) |