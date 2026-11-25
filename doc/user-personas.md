# User Personas — Polibatam Verify
 
Disusun berdasarkan *Pain Points* (versi terbaru), *Keputusan Klarifikasi Scope* (K-01 s.d. K-33), *Deskripsi Singkat Projek*, dan *Pedoman Microcredential 2026*.
 
> [ASUMSI] Nama, usia, dan latar belakang persona bersifat ilustratif untuk mewakili karakteristik tiap peran.
 
## Ringkasan
 
| ID | Nama Persona | Peran / Aktor | Pain Points Terkait |
|---|---|---|---|
| UP-01 | Rina Maharani | Calon Peserta / Peserta Didik | PP-01, PP-02, PP-03, PP-04, PP-05, PP-06 |
| UP-02 | Dedi Saputra | Admin SILAU | PP-07, PP-08, PP-09 |
| UP-03 | Sari Wulandari | Admin Keuangan | PP-06, PP-09, PP-10, PP-11, PP-12, PP-13 |
| UP-04 | Andi Pratama | Admin Track | PP-14, PP-15, PP-18 |
| UP-05 | Bambang Hartono | Super Admin | PP-16, PP-17, PP-18, PP-19 |
 
---
 
## UP-01 — Rina Maharani
 
| Atribut | Keterangan |
|---|---|
| **Peran / Aktor** | Calon Peserta → Peserta Didik (setelah NPD terbit) |
| **Profil Singkat** | 19 tahun, lulusan SMK Akuntansi di Batam, bekerja paruh waktu di toko ritel. Mengakses internet terutama lewat *smartphone*. Berminat pada track Manajemen & Bisnis, peminatan Akuntansi. |
| **Tujuan Utama (*Goals*)** | • Mendaftar dan memperoleh NPD tanpa perlu bolak-balik konfirmasi ke admin.<br>• Mengetahui dengan pasti nominal dan tenggat biaya formulir serta tagihan tiap semester.<br>• Membayar tepat waktu agar status Peserta Didik Aktif Semester tidak tertunda. |
| **Hambatan / Pain Points** | • Informasi tarif, kuota, dan peminatan tidak selalu terkini (PP-01).<br>• Tidak tahu status pendaftaran sudah sampai tahap mana (PP-02).<br>• Harus mengirim ulang semua berkas jika satu dokumen ditolak (PP-03).<br>• Tidak tahu nominal dan tenggat tagihan semester (PP-04).<br>• Bukti transfer tidak jelas terhubung ke tagihan mana (PP-05).<br>• Khawatir uangnya tidak kembali jika peminatan batal (PP-06). |
| **Kebutuhan / Ekspektasi terhadap Sistem** | • Katalog peminatan yang menampilkan tarif aktif dan sisa kuota secara *real-time*.<br>• *Status tracker*: Formulir → Verifikasi Berkas → Masuk Kuota → Verifikasi Pembayaran Formulir → NPD Terbit → Pembayaran Semester.<br>• Alasan penolakan per dokumen dan unggah ulang hanya untuk dokumen yang ditolak.<br>• Dasbor tagihan personal berisi kode referensi/kode unik, nominal, jatuh tempo, riwayat, dan pengingat otomatis.<br>• Notifikasi jika peminatan dibatalkan, beserta formulir *refund* dan status prosesnya.<br>• Tampilan responsif yang nyaman dipakai di *smartphone*. |
 
---
 
## UP-02 — Dedi Saputra
 
| Atribut | Keterangan |
|---|---|
| **Peran / Aktor** | Admin SILAU |
| **Profil Singkat** | 35 tahun, staf layanan administrasi akademik Polibatam. Saat puncak pendaftaran harus memeriksa ratusan berkas dalam waktu singkat. |
| **Tujuan Utama (*Goals*)** | • Memverifikasi berkas dengan cepat dan konsisten.<br>• Memastikan tidak ada berkas pendaftar yang terlewat.<br>• Setiap keputusan verifikasi dapat dipertanggungjawabkan. |
| **Hambatan / Pain Points** | • Berkas datang dari berbagai kanal dengan format dan kualitas berbeda (PP-07).<br>• Tidak ada *checklist* standar dan riwayat keputusan (PP-08).<br>• Koordinasi manual dengan Admin Keuangan untuk menentukan kelulusan registrasi (PP-09). |
| **Kebutuhan / Ekspektasi terhadap Sistem** | • Antrean verifikasi terpusat dengan filter periode, track, peminatan, dan status.<br>• *Document viewer* di dalam aplikasi, tanpa mengunduh manual.<br>• *Checklist* kriteria kelayakan per jenis dokumen.<br>• Penolakan per dokumen dengan alasan wajib diisi.<br>• Pencatatan otomatis identitas verifikator, waktu, dan alasan.<br>• Informasi sisa kuota peminatan saat memverifikasi, karena berkas yang valid langsung masuk hitungan kuota.<br>• Aplikasi mobile untuk memverifikasi berkas saat tidak berada di depan komputer (K-29). |
 
---
 
## UP-03 — Sari Wulandari
 
| Atribut | Keterangan |
|---|---|
| **Peran / Aktor** | Admin Keuangan |
| **Profil Singkat** | 40 tahun, staf keuangan BLU Polibatam. Setiap hari mencocokkan mutasi satu rekening penampung dengan bukti transfer peserta. |
| **Tujuan Utama (*Goals*)** | • Memvalidasi pembayaran formulir dan semester secara cepat dan akurat.<br>• Memastikan setiap tagihan sesuai tarif yang berlaku.<br>• Memantau tunggakan dan menyusun laporan tanpa rekap manual.<br>• Memproses *refund* secara tertib jika peminatan batal. |
| **Hambatan / Pain Points** | • Sulit mengidentifikasi pembayar dari mutasi rekening (PP-10).<br>• Tagihan dinamis dihitung manual (PP-11).<br>• Rekap lunas/tunggakan lambat (PP-12).<br>• Tidak ada kontrol atas bukti ganda atau tidak valid (PP-13).<br>• Verifikasi tidak sinkron dengan SILAU (PP-09).<br>• Proses *refund* tidak terlacak (PP-06). |
| **Kebutuhan / Ekspektasi terhadap Sistem** | • Kode referensi atau kode unik nominal pada setiap tagihan.<br>• Antrean verifikasi pembayaran yang dipisah per jenis tagihan (formulir / semester ke-*N*).<br>• Opsi menolak pembayaran dengan alasan (status *Ditolak*).<br>• Peringatan otomatis jika file bukti sama terdeteksi (*hash check*).<br>• Tagihan semester terbit otomatis dengan *snapshot* tarif.<br>• Dasbor rekap dengan filter dan ekspor Excel/PDF.<br>• Modul *refund* dengan akses terbatas ke nomor rekening terenkripsi.<br>• Aplikasi mobile untuk memverifikasi pembayaran; refund dan rekap tetap di web (K-29, K-30). |
 
---
 
## UP-04 — Andi Pratama
 
| Atribut | Keterangan |
|---|---|
| **Peran / Aktor** | Admin Track |
| **Profil Singkat** | 38 tahun, koordinator program Microcredential di tingkat jurusan. Rutin menyesuaikan tarif dan kuota mengikuti kesepakatan terbaru. |
| **Tujuan Utama (*Goals*)** | • Menjaga data master track dan peminatan tetap rapi dan lengkap.<br>• Memberlakukan tarif dan kuota terbaru tanpa mengubah tagihan yang sudah terbit.<br>• Memantau keterisian kuota tiap peminatan. |
| **Hambatan / Pain Points** | • Tarif dan kuota tidak terpusat dan tidak berversi (PP-14).<br>• Data master rawan tidak lengkap atau duplikat (PP-15).<br>• Pemantauan kuota sulit (PP-18). |
| **Kebutuhan / Ekspektasi terhadap Sistem** | • CRUD track dan peminatan dengan kode unik, status aktif/nonaktif, dan parameter durasi semester.<br>• Pengaturan tarif formulir dan tarif per semester per peminatan dengan tanggal berlaku efektif dan riwayat versi.<br>• Pengaturan kuota minimum dan maksimum yang dikunci sebelum periode pendaftaran dimulai.<br>• Dasbor keterisian kuota per peminatan. |
 
---
 
## UP-05 — Bambang Hartono
 
| Atribut | Keterangan |
|---|---|
| **Peran / Aktor** | Super Admin |
| **Profil Singkat** | 45 tahun, pengelola unit Microcredential sekaligus penanggung jawab sistem. Bertanggung jawab atas konfigurasi, keamanan akses, dan pergantian periode akademik. |
| **Tujuan Utama (*Goals*)** | • Memegang kendali penuh atas konfigurasi dan data sistem.<br>• Menjalankan transisi semester massal dengan akurat.<br>• Memastikan akses aman dan seluruh perubahan penting teraudit.<br>• Menjaga informasi publik di *landing page* tetap mutakhir. |
| **Hambatan / Pain Points** | • Transisi semester dilakukan manual per peserta (PP-16).<br>• Belum ada RBAC dan *audit log* (PP-17).<br>• Keputusan pembatalan peminatan terlambat karena kuota sulit dipantau (PP-18).<br>• Pembaruan *landing page* bergantung pada tim teknis (PP-19). |
| **Kebutuhan / Ekspektasi terhadap Sistem** | • Pengaturan periode akademik dan periode pendaftaran.<br>• *Batch rollover* semester dengan pratinjau dampak sebelum dieksekusi (naik semester, tagihan baru, status *Selesai Masa Program*).<br>• Manajemen akun dan *role* pengguna.<br>• *Audit log* yang tidak dapat diubah dan dapat dicari.<br>• CMS *landing page* yang hanya dapat diakses Super Admin.<br>• Eksekusi pembatalan peminatan yang tidak mencapai kuota minimum. [ASUMSI] |
 
---
 
## Matriks Keterlacakan Pain Point → Persona
 
| Pain Point | UP-01 | UP-02 | UP-03 | UP-04 | UP-05 |
|---|:---:|:---:|:---:|:---:|:---:|
| PP-01 | ✓ | | | | |
| PP-02 | ✓ | | | | |
| PP-03 | ✓ | | | | |
| PP-04 | ✓ | | | | |
| PP-05 | ✓ | | | | |
| PP-06 | ✓ | | ✓ | | |
| PP-07 | | ✓ | | | |
| PP-08 | | ✓ | | | |
| PP-09 | | ✓ | ✓ | | |
| PP-10 | | | ✓ | | |
| PP-11 | | | ✓ | | |
| PP-12 | | | ✓ | | |
| PP-13 | | | ✓ | | |
| PP-14 | | | | ✓ | |
| PP-15 | | | | ✓ | |
| PP-16 | | | | | ✓ |
| PP-17 | | | | | ✓ |
| PP-18 | | | | ✓ | ✓ |
| PP-19 | | | | | ✓ |
 