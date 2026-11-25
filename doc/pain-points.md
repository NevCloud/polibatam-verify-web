# Pain Points — Polibatam Verify
 
Disusun berdasarkan *Deskripsi Singkat Projek*, *Pedoman Microcredential 2026*, dan *Keputusan Klarifikasi Scope* (K-01 s.d. K-14).
 
> **Catatan sinkronisasi:** Kolom Solusi diisi pengguna dan dipertahankan apa adanya. Rincian penerapannya mengikuti keputusan lanjutan K-15 s.d. K-33, antara lain Daftar Tunggu kuota, refund seluruh biaya tidak terpakai, aplikasi mobile verifikasi untuk Admin SILAU dan Admin Keuangan (mendukung PP-07, PP-09, PP-10), serta 2FA wajib untuk admin (mendukung PP-17). Lihat dokumen SRS dan Keputusan Klarifikasi Scope.
 
> [ASUMSI] Kondisi saat ini (*as-is*): proses pendaftaran, pemberkasan, dan konfirmasi pembayaran masih semi-manual (formulir online terpisah, bukti transfer via email/WhatsApp, rekap memakai spreadsheet), dengan satu rekening penampung untuk seluruh pembayaran.
 
> Kolom **Solusi** telah dilengkapi oleh tim proyek.
 
## 1. Calon Peserta / Peserta Didik
 
| ID | Aktor Terkait | Deskripsi Masalah | Dampak | Tingkat Keparahan | Solusi |
|---|---|---|---|---|---|
| PP-01 | Calon Peserta | Informasi tarif formulir, tarif semester, kuota, dan daftar peminatan tersebar dan tidak selalu terkini, karena dokumen pedoman bersifat statis sementara angkanya berubah mengikuti kesepakatan terbaru. | Calon peserta salah memilih peminatan atau mentransfer nominal yang salah. Admin banyak menerima pertanyaan berulang. | High | **Portal Informasi & Katalog Peminatan Dinamis:** Menyediakan *landing page* dan halaman pendaftaran yang menarik data langsung dari *database* master peminatan, kuota *real-time*, dan tarif aktif periode berjalan sehingga informasi selalu mutakhir. |
| PP-02 | Calon Peserta | Tidak ada visibilitas status pendaftaran secara *real-time* (berkas diverifikasi atau ditolak, pembayaran formulir valid atau belum, NPD sudah terbit atau belum). | Peserta cemas dan menanyakan status berulang kali ke admin. Proses terasa tidak transparan. | High | **Pelacakan Status Real-Time (Status Tracker):** Menampilkan *progress tracker* bertahap pada dasbor peserta (Status Berkas, Validasi Pembayaran Formulir, hingga status penerbitan NPD) lengkap dengan notifikasi perubahan status. |
| PP-03 | Calon Peserta | Ketika berkas ditolak, alasan penolakan tidak jelas dan peserta harus mengulang seluruh pengiriman berkas, tidak hanya dokumen yang bermasalah. | Siklus revisi panjang dan penerbitan NPD tertunda. | Medium | **Revisi Berkas Parsial & Riwayat Penolakan:** Menyimpan catatan alasan penolakan per dokumen spesifik (misal: hanya KTP buram) dan mengizinkan peserta mengunggah ulang hanya dokumen yang ditolak tanpa mengulang dokumen lain yang sudah valid. |
| PP-04 | Peserta Didik | Nominal dan tenggat tagihan semester (dinamis per semester dan peminatan) tidak diinformasikan secara terpusat dan personal. | Peserta terlambat membayar atau membayar nominal yang salah, sehingga status aktif semester tertunda. | High | **Dasbor Tagihan Personal & Pengingat Tenggat:** Menampilkan daftar tagihan semester aktif beserta rincian nominal, jatuh tempo, dan riwayat pembayaran pada akun masing-masing peserta, disertai pengingat otomatis menjelang tenggat waktu. |
| PP-05 | Calon Peserta / Peserta Didik | Bukti pembayaran tidak terhubung dengan tagihan tertentu (formulir atau semester ke-berapa). | Pembayaran sulit dicocokkan, rawan ditolak, dan peserta harus mengirim ulang bukti. | Medium | **Unggah Bukti Berbasis ID Tagihan (Invoice-Linked Payment):** Mewajibkan peserta memilih tagihan spesifik (Formulir atau Semester ke-*N*) sebelum mengunggah bukti bayar sehingga sistem otomatis menautkan bukti transfer dengan kode tagihan terkait. |
| PP-06 | Calon Peserta | Tidak ada kepastian dan informasi status ketika peminatan yang dipilih tidak mencapai kuota minimum, termasuk proses *refund*. | Peserta kehilangan kepercayaan dan *refund* rawan terlewat atau terlambat. | Medium | **Modul Pembatalan Peminatan & Pelacakan Refund:** Memberikan status notifikasi otomatis jika peminatan dibatalkan karena kuota tidak terpenuhi, disertai formulir pengajuan rekening pengembalian dana (nomor rekening disimpan terenkripsi) dan pelacakan status proses *refund* oleh Keuangan. |
 
## 2. Admin SILAU
 
| ID | Aktor Terkait | Deskripsi Masalah | Dampak | Tingkat Keparahan | Solusi |
|---|---|---|---|---|---|
| PP-07 | Admin SILAU | Berkas pendaftaran (ijazah/SKL/rapor, SKHU, KTP) diterima dari berbagai kanal dengan format dan kualitas berbeda, lalu diperiksa satu per satu secara manual. | Verifikasi lambat saat puncak pendaftaran, berkas terlewat, dan beban kerja tinggi. | High | **Repositori Berkas Terpusat & Pratinjau Terintegrasi:** Menyatukan seluruh unggahan peserta dengan standar format/ukuran file (*PDF/JPG/PNG*) dalam satu antrean verifikasi dengan fitur *document viewer* langsung di aplikasi tanpa perlu mengunduh manual. |
| PP-08 | Admin SILAU | Tidak ada *checklist* verifikasi standar maupun riwayat keputusan (siapa memverifikasi, kapan, dan alasannya). | Keputusan antar-verifikator tidak konsisten dan sulit diaudit. | Medium | **Checklist Verifikasi Standar & Log Verifikator:** Menyediakan daftar periksa kriteria kelayakan per jenis dokumen dan mencatat identitas verifikator, waktu verifikasi, serta catatan alasan penolakan secara otomatis. |
| PP-09 | Admin SILAU, Admin Keuangan | Verifikasi ganda (berkas oleh SILAU, pembayaran formulir oleh Keuangan) tidak sinkron dan dikoordinasikan secara manual untuk menentukan kelulusan registrasi. | Penerbitan NPD terlambat dan bisa terbit sebelum kedua verifikasi selesai. | High | **Penerbitan NPD Otomatis Berbasis Gerbang Ganda (Dual-Gate Trigger):** Mengunci penerbitan Nomor Peserta Didik (NPD) hingga status berkas berstatus *Valid* (oleh SILAU) **dan** pembayaran formulir berstatus *Lunas* (oleh Keuangan) secara sinkron di sistem. Urutan verifikasi: berkas → masuk kuota → keuangan → NPD terbit. |
 
## 3. Admin Keuangan
 
| ID | Aktor Terkait | Deskripsi Masalah | Dampak | Tingkat Keparahan | Solusi |
|---|---|---|---|---|---|
| PP-10 | Admin Keuangan | Pencocokan bukti transfer dengan mutasi rekening dilakukan manual. Semua pembayaran masuk ke satu rekening tanpa penanda unik per peserta atau per tagihan. | Identifikasi pembayar lambat dan rawan salah, sehingga validasi tertunda. | High | **Kode Unik Nominal / Kode Referensi Tagihan:** Sistem men-generate kode referensi unik (atau nominal dengan 3 digit kode unik) pada setiap tagihan peserta untuk memudahkan penyaringan dan pencocokan instan dengan mutasi rekening koran. Selisih nominal akibat kode unik tidak dikembalikan. |
| PP-11 | Admin Keuangan | Nominal tagihan semester berbeda per semester dan peminatan, dan tarif dapat berubah sewaktu-waktu. Perhitungan tagihan per peserta masih manual. | Terjadi salah tagih (kurang atau lebih), sengketa dengan peserta, dan koreksi berulang. | High | **Generator Tagihan Otomatis (Automated Billing Engine):** Sistem menghitung dan menerbitkan tagihan secara otomatis berdasarkan peminatan, semester aktif peserta, dan skema tarif yang sedang berlaku pada periode tersebut. |
| PP-12 | Admin Keuangan | Sulit merekap status lunas atau tunggakan per peserta, per semester, per peminatan, dan per periode. | Laporan keuangan lambat dan tunggakan tidak terpantau. | Medium | **Dasbor Rekapitulasi Keuangan & Ekspor Laporan:** Menyediakan filter laporan status pembayaran (*Lunas, Menunggu Verifikasi, Belum Bayar/Tunggakan*) per periode, peminatan, dan angkatan yang dapat diekspor ke format Excel/PDF. |
| PP-13 | Admin Keuangan | Tidak ada kontrol terhadap bukti pembayaran ganda, bukti yang dipakai ulang, atau bukti tidak valid. | Ada risiko kerugian finansial dan validasi yang keliru. | Medium | **Validasi Hash File & Kunci Status Tagihan:** Mencegah satu file bukti transfer yang sama diunggah berulang kali (*file hash check*) serta mengunci opsi unggah ulang pada tagihan yang sudah berstatus *Menunggu Verifikasi* atau *Lunas*. *Hash check* hanya kontrol awal; kontrol pengikat adalah pencocokan mutasi rekening dengan kode referensi. Tagihan berstatus *Ditolak* membuka kembali opsi unggah ulang. |
 
## 4. Admin Track
 
| ID | Aktor Terkait | Deskripsi Masalah | Dampak | Tingkat Keparahan | Solusi |
|---|---|---|---|---|---|
| PP-14 | Admin Track | Perubahan tarif formulir, tarif semester, dan kuota tidak dikelola terpusat dan tidak berversi, sehingga tidak jelas tarif mana yang berlaku untuk tagihan yang sudah atau akan terbit. | Tagihan peserta tidak konsisten, terjadi sengketa nominal, dan koreksi dilakukan manual. | High | **Manajemen Tarif Berversi (Tariff Versioning):** Menyimpan histori tarif dengan tanggal berlaku efektif (*effective date*) dan *snapshot* nominal pada saat tagihan dibuat, sehingga perubahan tarif baru tidak merusak nominal tagihan lama yang sudah terbit. |
| PP-15 | Admin Track | Data master track dan peminatan rawan tidak lengkap atau duplikat (contoh: peminatan Teknik Elektro belum terdefinisi dan terdapat baris prodi ganda di pedoman), dan durasi semester per peminatan berbeda-beda. | Peserta salah memilih peminatan dan durasi siklus semester salah dihitung. | Medium | **Master Data Peminatan Terstruktur & Validasi Duplikasi:** Menyediakan modul CRUD master jurusan/track dan peminatan dengan validasi kode unik, status aktif/nonaktif, serta parameter durasi semester per peminatan. |
 
## 5. Super Admin
 
| ID | Aktor Terkait | Deskripsi Masalah | Dampak | Tingkat Keparahan | Solusi |
|---|---|---|---|---|---|
| PP-16 | Super Admin | Transisi semester peserta saat pergantian periode dilakukan manual per peserta, termasuk menentukan peserta yang sudah mencapai akhir durasi peminatan. | Status semester salah, tagihan baru tidak terbit, atau peserta yang sudah selesai tetap ditagih. | High | **Otomatisasi Siklus Pergantian Semester (Batch Semester Rollover):** Fitur pergantian periode akademik massal yang otomatis menaikkan tingkat semester peserta aktif, menerbitkan tagihan semester berikutnya, dan mengubah status peserta yang mencapai batas durasi peminatan menjadi *Selesai Masa Program*. |
| PP-17 | Super Admin | Belum ada pembagian hak akses berbasis peran (RBAC) maupun *audit log* atas perubahan data penting (tarif, status peserta, validasi). | Perubahan tidak terlacak, berisiko penyalahgunaan akses, dan sulit ditelusuri saat terjadi sengketa. | High | **Role-Based Access Control (RBAC) & Immutable Audit Trail:** Memisahkan hak akses secara ketat antar peran (*Calon Peserta, Admin SILAU, Admin Keuangan, Admin Track, Super Admin*) dan mencatat seluruh aktivitas krusial (perubahan tarif, status validasi, pembatalan) dalam tabel *audit log*. |
| PP-18 | Super Admin, Admin Track | Pemantauan jumlah pendaftar terhadap kuota minimum dan maksimum per peminatan per periode sulit dilakukan. | Keputusan pembatalan peminatan dan *refund* terlambat, atau pendaftar melebihi kapasitas. | Medium | **Dasbor Monitoring Kapasitas & Peringatan Kuota:** Menampilkan indikator progres jumlah pendaftar terverifikasi (NPD sudah terbit) dibandingkan batas kuota minimum dan maksimum secara *real-time*, serta menutup pendaftaran peminatan otomatis saat kuota maksimum penuh. |
| PP-19 | Super Admin | Pembaruan konten informasi *landing page* (jadwal, tarif, pengumuman) bergantung pada tim teknis. | Informasi publik terlambat diperbarui sehingga memperparah PP-01. | Low | **Content Management System (CMS) Informasi Publik:** Menyediakan antarmuka pengelolaan konten (jadwal pendaftaran, pengumuman, alur, dan kontak bantuan) agar Super Admin dapat memperbarui informasi *landing page* secara mandiri tanpa mengubah kode program. |
 
## Ringkasan
 
| Tingkat Keparahan | Jumlah | ID |
|---|---|---|
| High | 10 | PP-01, PP-02, PP-04, PP-07, PP-09, PP-10, PP-11, PP-14, PP-16, PP-17 |
| Medium | 8 | PP-03, PP-05, PP-06, PP-08, PP-12, PP-13, PP-15, PP-18 |
| Low | 1 | PP-19 |