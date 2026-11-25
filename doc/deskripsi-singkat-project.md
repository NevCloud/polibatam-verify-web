# Project Context: Polibatam Verify
 
## 1. Deskripsi Umum Sistem
**Polibatam Verify** adalah sistem informasi terintegrasi yang dirancang untuk mengelola seluruh ekosistem pendaftaran dan administrasi akademik program **Microcredential** di **Politeknik Negeri Batam**. 
 
Sistem ini mencakup alur proses *end-to-end*, mulai dari:
- Pengisian formulir pendaftaran berbayar.
- Verifikasi kelengkapan berkas calon peserta.
- Validasi pembayaran pendaftaran.
- Pengelolaan dan pembayaran tagihan semester pertama hingga semester-semester berikutnya.
---
 
## 2. Aturan Bisnis Utama (Core Business Logic)
1. **Manajemen Status & Siklus Semester (Semester Lifecycle):**
   - Setiap peserta terikat pada status semester masing-masing (misal: Semester 1, Semester 2, dst.).
   - Status semester peserta akan diperbarui secara bertahap seiring perpindahan ke semester selanjutnya berdasarkan periode akademik yang berlaku.
2. **Skema Biaya Dinamis (Dynamic Tuition Billing):**
   - Nominal tagihan pendidikan tidak bersifat statis (*flat*), melainkan dinamis.
   - Besaran tagihan semester dapat disesuaikan dan berbeda-beda bergantung pada **tingkat semester** maupun **pilihan peminatan (*specialization*)** yang diambil oleh masing-masing peserta.
---
 
## 3. Aktor Sistem & Hak Akses (Role-Based Access Control)
Sistem ini melibatkan 5 aktor utama dengan pembagian tugas dan hak akses yang terstruktur:
 
### 1. Calon Peserta / Mahasiswa
- Melakukan registrasi akun dan mengisi formulir pendaftaran.
- Melengkapi dan mengunggah berkas persyaratan pendaftaran.
- Memantau status verifikasi dan status semester aktif secara berkala.
- Menyelesaikan pembayaran biaya pendaftaran serta tagihan semester berjalan secara bertahap.
### 2. Admin SILAU
- Bertanggung jawab memeriksa, memverifikasi, dan memvalidasi kelengkapan serta keabsahan berkas pendaftaran yang diunggah oleh calon peserta.
### 3. Admin Keuangan
- Memverifikasi bukti pembayaran pendaftaran awal.
- Mengelola transaksi dan memvalidasi pembayaran tagihan dinamis untuk semester pertama maupun semester-semester selanjutnya.
### 4. Admin Track
- Mengelola data master terkait *track* program Microcredential.
- Mengatur daftar peminatan (*specialization*) yang tersedia.
- Mengatur skema dan nominal tarif semester per peminatan.
- Mengelola informasi akademik pendukung lainnya.
### 5. Super Admin
- Memiliki kendali penuh (*full access*) untuk mengelola seluruh konfigurasi dan data dalam sistem.
- Mencakup manajemen *track* dan peminatan, pengaturan periode akademik, eksekusi transisi semester peserta, hingga pembaruan konten informasi pada *landing page*.
---
 
## 4. Alur Proses Utama (End-to-End Flow)
1. **Konfigurasi Awal:** Super Admin / Admin Track menyiapkan data *track*, peminatan, tarif dinamis, dan periode pendaftaran.
2. **Registrasi & Pemberkasan:** Calon Peserta mendaftar, memilih peminatan, mengunggah berkas, dan melakukan pembayaran pendaftaran.
3. **Verifikasi Ganda:** 
   - Admin SILAU memvalidasi kelengkapan berkas.
   - Admin Keuangan memvalidasi bukti pembayaran pendaftaran.
4. **Aktivasi & Pembayaran Semester 1:** Setelah berkas dan pembayaran awal valid, peserta resmi terdaftar di Semester 1 dan mendapatkan tagihan semester sesuai peminatannya untuk divalidasi oleh Admin Keuangan.
5. **Transisi Semester Berikutnya:** Pada pergantian periode, sistem/Super Admin melakukan transisi status semester peserta, memicu pembuatan tagihan dinamis baru untuk semester berjalan.