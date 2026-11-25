# Daftar Halaman Web — Polibatam Verify
 
Disusun berdasarkan *FR & BR* (FR-01 s.d. FR-68), *Use Case* (UC-01 s.d. UC-34), *Matriks RBAC*, *Dokumen Arsitektur* §6.5, dan *Keputusan Klarifikasi Scope* (K-01 s.d. K-40). Aplikasi mobile (khusus verifikasi Admin SILAU & Keuangan) disusun terpisah.
 
**Konvensi:**
- ID halaman memakai `HW-xx` (Halaman Web).
- **Rute** bersifat ilustratif dan dapat disesuaikan saat implementasi (K-35).
- **Akses:** CP = Calon Peserta / Peserta Didik, SIL = Admin SILAU, KEU = Admin Keuangan, TRK = Admin Track, SA = Super Admin, Semua admin = SIL + KEU + TRK + SA.
- **Prioritas** mengikuti *user story* sumber (MoSCoW).
- Seluruh halaman memakai layout desain di `Skills/design-memory.md` (header 72px, Plus Jakarta Sans, warna navy #1E376C).
**Keputusan K-39 (21 November 2026):**
- Login admin dan peserta memakai **satu halaman `/login`**; setelah login pengguna diarahkan ke dasbor sesuai *role* (admin melewati verifikasi 2FA lebih dulu).
- Halaman **HW-12** (profil & keamanan akun), **HW-47** (pengaturan sistem), dan **HW-48** (kriteria *checklist*) **disetujui** masuk daftar.
- Formulir pendaftaran (**HW-15**) dibuat sebagai **satu halaman dengan tab**, bukan halaman terpisah per tahap.
**Keputusan K-40 (21 November 2026):** halaman **Rekening Institusi (HW-56)** ditambahkan; data pengiriman pada unggah bukti (HW-18) dan unduh kartu pendaftar (HW-13) dipertahankan.
 
**Desain Figma:** seluruh halaman HW-01 s.d. HW-56 tersedia di file *Microcredential – Website (Copy)*, halaman **"🖥️ Halaman Web (HW)"**, dibangun dari komponen di halaman **"🧩 Komponen PV"** (lihat `claude/review-desain-figma.md`).
 
---
 
## Ringkasan
 
| Area | Jumlah halaman | ID |
|---|---|---|
| A. Publik | 4 | HW-01 – HW-04 |
| B. Autentikasi & akun | 8 | HW-05 – HW-12 |
| C. Calon Peserta / Peserta Didik | 8 | HW-13 – HW-20 |
| D. Admin bersama | 4 | HW-21 – HW-24 |
| E. Admin SILAU | 3 | HW-25 – HW-27 |
| F. Admin Keuangan | 6 | HW-28 – HW-32, HW-56 |
| G. Admin Track | 5 | HW-33 – HW-37 |
| H. Super Admin | 11 | HW-38 – HW-48 |
| I. Halaman sistem | 7 | HW-49 – HW-55 |
| **Total** | **56** | |
 
---
 
## A. Publik (tanpa login)
 
| ID | Halaman | Rute | Isi utama | FR / UC | Prioritas |
|---|---|---|---|---|---|
| HW-01 | Beranda (*landing page*) | `/` | Hero, jadwal periode, pengumuman, alur pendaftaran, ringkasan track, kontak bantuan (konten dari CMS); tarif & kuota dari data master | FR-08, FR-10 · UC-01, UC-27 | Must |
| HW-02 | Katalog peminatan | `/katalog` | Daftar peminatan aktif per track: durasi, tarif formulir, tarif per semester, sisa kuota, label "Kuota Penuh – Daftar Tunggu"; jadwal periode berikutnya bila pendaftaran tutup | FR-08, FR-09 · UC-01 | Must |
| HW-03 | Detail peminatan | `/katalog/{kode}` | Rincian peminatan, tarif semester 1..D, persyaratan berkas, tombol Daftar | FR-08 · UC-01 | Must |
| HW-04 | Kebijakan privasi | `/kebijakan-privasi` | Teks kebijakan privasi (UU PDP) yang disetujui saat registrasi | NFR-11 | Must |
 
## B. Autentikasi & Akun
 
| ID | Halaman | Rute | Isi utama | FR / UC | Akses | Prioritas |
|---|---|---|---|---|---|---|
| HW-05 | Registrasi | `/register` | Nama, email, password, konfirmasi, centang kebijakan privasi | FR-01 · UC-02 | Publik | Must |
| HW-06 | Verifikasi email | `/email/verify` | Info "cek email Anda", kirim ulang tautan, status tautan kedaluwarsa | FR-02 · UC-02 | CP | Must |
| HW-07 | Login (satu untuk semua *role*) | `/login` | Email, password, pesan gagal umum, info akun terkunci. Setelah berhasil: peserta → HW-13; admin → HW-08 lalu HW-21 (K-39) | FR-03, FR-04 · UC-03 | Semua | Must |
| HW-08 | Verifikasi 2FA | `/two-factor-challenge` | Input kode TOTP atau *recovery code* | FR-59 · UC-03 | Semua admin | Must |
| HW-09 | Lupa password | `/forgot-password` | Input email, pesan "Email tidak terdaftar" (K-32) | FR-05 · UC-04 | Semua | Must |
| HW-10 | Reset password | `/reset-password/{token}` | Password baru + konfirmasi, status tautan kedaluwarsa | FR-05 · UC-04 | Semua | Must |
| HW-11 | Aktivasi akun admin | `/aktivasi/{token}` | Atur password → pindai QR TOTP → konfirmasi kode → simpan *recovery code* | FR-06, FR-59 · UC-25 | Admin baru | Must |
| HW-12 | Profil & keamanan akun | `/akun` | Data akun, ganti password, lihat ulang / buat ulang *recovery code* (admin), daftar sesi aktif | FR-03, FR-59 · K-39 | Semua | Should |
 
## C. Calon Peserta / Peserta Didik
 
| ID | Halaman | Rute | Isi utama | FR / UC | Prioritas |
|---|---|---|---|---|---|
| HW-13 | Dasbor peserta | `/dasbor` | *Progress tracker* 5 tahap (FR-15), nomor pendaftaran, status khusus (Daftar Tunggu + nomor urut, Tidak Tertampung, Dibatalkan), NPD & status semester bagi Peserta Didik, tagihan terdekat, notifikasi terbaru, pengingat Draf sebelum periode ditutup, tombol **Unduh kartu pendaftar** (setelah Diajukan, K-40) | FR-15, FR-53, FR-68 · UC-08 | Must |
| HW-14 | Pilih peminatan | `/pendaftaran/baru` | Pilih track & peminatan, info kuota penuh/Daftar Tunggu; saat disimpan, Draf dan nomor pendaftaran dibuat lalu pengguna diarahkan ke HW-15 | FR-11 · UC-05 | Must |
| HW-15 | Formulir pendaftaran (satu halaman, tab) | `/pendaftaran/{nomor}` | Satu halaman dengan 3 tab (K-39), lihat rincian di bawah tabel | FR-11–14 · UC-05, UC-06 | Must |
| HW-16 | Detail berkas & revisi | `/pendaftaran/{nomor}/berkas` | Status per dokumen, alasan penolakan, unggah ulang hanya dokumen Ditolak, riwayat versi | FR-16, FR-17 · UC-07 | Must |
| HW-17 | Daftar tagihan | `/tagihan` | Tabel tagihan (jenis, kode referensi, nominal + kode unik, jatuh tempo / "Menunggu penetapan", status, tanggal lunas) | FR-37 · UC-09 | Must |
| HW-18 | Detail tagihan & unggah bukti | `/tagihan/{kode_referensi}` | Instruksi transfer, rekening tujuan (*snapshot* rekening institusi), nominal persis, data pengiriman wajib (tanggal transfer, nominal ditransfer, bank pengirim, nama pengirim — K-40), unggah bukti (hanya Belum Bayar/Ditolak), riwayat bukti & alasan tolak | FR-29, FR-30 · UC-10 | Must |
| HW-19 | Refund | `/refund` dan `/refund/{id}` | Daftar refund (sebab, rincian tagihan, nominal, status); formulir rekening (bank, nomor, nama pemilik); rekening tersamar | FR-43, FR-62 · UC-11 | Must |
| HW-20 | Notifikasi | `/notifikasi` | Daftar notifikasi *in-app*, tandai dibaca | FR-53 · UC-08 | Must |
 
### Rincian HW-15 — Formulir Pendaftaran (Tab)
 
| Tab | Isi | Aturan |
|---|---|---|
| 1. Data Diri | Nama lengkap, NIK, tempat & tanggal lahir, jenis kelamin, no. HP, alamat, asal sekolah, tahun lulus; ringkasan peminatan terpilih (baca-saja) | Simpan Draf tanpa harus lengkap; NIK dienkripsi (BR-62) |
| 2. Berkas | 4 slot unggah (3 wajib, 1 opsional) dengan pratinjau dan progres unggah | PDF/JPG/PNG ≤ 2 MB (BR-11, BR-12) |
| 3. Tinjau & Ajukan | Ringkasan data dan berkas, daftar kekurangan, tombol Ajukan | Ajukan hanya bila semua wajib lengkap (FR-12) |
 
Perilaku halaman:
- Pengguna bebas berpindah tab; isian tab tidak hilang saat berpindah. Tombol **Simpan Draf** tersedia di semua tab.
- Setiap tab menampilkan penanda: lengkap (✓), belum lengkap (!), atau ada kesalahan validasi.
- Saat **Ajukan** ditolak karena belum lengkap, sistem membuka tab pertama yang bermasalah dan menandai *field*/slot terkait (AC-04.3).
- Setelah status **Diajukan** atau Draf **Dibatalkan** (periode ditutup), seluruh tab menjadi baca-saja; revisi berkas dilakukan di HW-16.
- Di layar sempit (≥ 360 px, NFR-33), tab tampil sebagai *segmented control* yang dapat digeser.
## D. Admin Bersama
 
| ID | Halaman | Rute | Isi utama | FR / UC | Akses | Prioritas |
|---|---|---|---|---|---|---|
| HW-21 | Dasbor admin | `/admin` | Kartu ringkasan sesuai *role*: antrean berkas (SIL), antrean pembayaran & refund (KEU), keterisian kuota (TRK), peringatan *rollover*/tagihan tanpa jatuh tempo/arsip gagal (SA) | FR-18, FR-31, FR-49, FR-57, FR-58 | Semua admin | Must |
| HW-22 | Data Pendaftaran | `/admin/data-pendaftaran` | Tabel tanpa Draf, jumlah Draf (angka), filter periode/track/peminatan/status/tanggal, cari nama/nomor/NIK, paginasi; kotak centang + "Ekspor terpilih" dan tombol Export Excel/PDF hanya untuk SIL & SA | FR-63, FR-64 · UC-33 | Semua admin (ekspor: SIL, SA) | Should |
| HW-23 | Detail pendaftaran (baca-saja) | `/admin/data-pendaftaran/{nomor}` | Data formulir (NIK tersamar), dokumen, riwayat status, tagihan terkait; tanpa aksi verifikasi; Draf → 404 | FR-63 · UC-33 | Semua admin | Should |
| HW-24 | Notifikasi admin | `/admin/notifikasi` | Notifikasi *in-app*, termasuk tautan unduhan ekspor besar | FR-53, FR-64 | Semua admin | Must |
 
## E. Admin SILAU
 
| ID | Halaman | Rute | Isi utama | FR / UC | Akses | Prioritas |
|---|---|---|---|---|---|---|
| HW-25 | Antrean verifikasi berkas | `/admin/verifikasi-berkas` | Pendaftaran Diajukan, urut pengajuan terlama, filter, NIK tersamar, tanpa aksi massal | FR-18 · UC-12 | SIL, SA | Must |
| HW-26 | Detail verifikasi berkas | `/admin/verifikasi-berkas/{nomor}` | *Document viewer*, *checklist* per jenis dokumen, Valid/Ditolak + alasan, sisa kuota, tombol Selesaikan; dialog konflik 409 | FR-19–22, FR-61 · UC-12 | SIL, SA | Must |
| HW-27 | Arsip Periode | `/admin/arsip-periode` | Daftar arsip (periode, status, jumlah baris, ukuran, waktu dibuat), unduh, "Coba buat ulang" (SA, arsip Gagal); tanpa ubah/hapus | FR-65, FR-66 · UC-34 | SIL, SA | Should |
 
## F. Admin Keuangan
 
| ID | Halaman | Rute | Isi utama | FR / UC | Akses | Prioritas |
|---|---|---|---|---|---|---|
| HW-28 | Antrean verifikasi pembayaran | `/admin/verifikasi-pembayaran` | Tagihan Menunggu Verifikasi, filter jenis (formulir / semester ke-*n*), peminatan, periode; keterangan "Menunggu verifikasi berkas"; tanpa aksi massal | FR-31 · UC-13 | KEU, SA | Must |
| HW-29 | Detail verifikasi pembayaran | `/admin/verifikasi-pembayaran/{kode_referensi}` | Kode referensi, nominal + kode unik, pemilik, data pengiriman (tanggal, nominal, bank, nama pengirim) dengan peringatan selisih, pratinjau bukti, status gerbang ganda, Lunas / Tolak + alasan; dialog konflik 409 | FR-32–35, FR-61 · UC-13 | KEU, SA | Must |
| HW-30 | Daftar refund | `/admin/refund` | Refund per status, sebab, nominal, rekening tersamar | FR-44 · UC-14 | KEU, SA | Must |
| HW-31 | Proses refund | `/admin/refund/{id}` | Rincian tagihan, pencocokan mutasi (sertakan/kecualikan + alasan), buka rekening lengkap (tercatat), unggah bukti transfer, tandai Selesai | FR-42, FR-44, FR-62 · UC-14 | KEU, SA | Must |
| HW-32 | Rekap & ekspor keuangan | `/admin/rekap` | Jumlah & nominal per status, tunggakan, filter periode/peminatan/angkatan/jenis, ekspor Excel/PDF | FR-38 · UC-15 | KEU, SA | Should |
| HW-56 | Rekening institusi | `/admin/rekening` | Daftar rekening tujuan transfer (bank, nomor, atas nama, jenis tagihan, status), tambah/ubah, aktifkan/nonaktifkan (satu aktif per jenis tagihan, konfirmasi saat mengganti), tanpa hapus untuk rekening yang pernah dipakai; info bahwa tagihan terbit memakai *snapshot* | FR-67 · K-40 | KEU, SA | Must |
 
## G. Admin Track
 
| ID | Halaman | Rute | Isi utama | FR / UC | Akses | Prioritas |
|---|---|---|---|---|---|---|
| HW-33 | Track | `/admin/track` | Daftar + formulir tambah/ubah (kode, nama, status), nonaktifkan | FR-45 · UC-16 | TRK, SA | Must |
| HW-34 | Peminatan | `/admin/peminatan` | Daftar + formulir (kode, nama, track, durasi, status), nonaktifkan; hapus hanya jika belum dipakai | FR-46 · UC-16 | TRK, SA | Must |
| HW-35 | Tarif peminatan | `/admin/peminatan/{kode}/tarif` | Tarif berlaku (formulir & semester 1..D), formulir versi baru + tanggal efektif, riwayat versi (pengubah, waktu) | FR-47 · UC-17 | TRK, SA (KEU baca) | Must |
| HW-36 | Kuota per periode | `/admin/kuota` | Pilih periode → tabel peminatan dengan kuota min/maks, slot terpakai; tambah kuota maks + alasan; ubah kuota min saat berjalan hanya SA | FR-48 · UC-18 | TRK, SA (SIL baca) | Must |
| HW-37 | Dasbor kuota | `/admin/kuota/dasbor` | Per peminatan: Masuk Kuota, NPD Terbit, Daftar Tunggu, min/maks, persentase, penanda peringatan H-7 | FR-49 · UC-19 | TRK, SA, SIL | Should |
 
## H. Super Admin
 
| ID | Halaman | Rute | Isi utama | FR / UC | Prioritas |
|---|---|---|---|---|---|
| HW-38 | Periode pendaftaran | `/admin/periode-pendaftaran` | Daftar + formulir (nama, tanggal mulai/selesai), status dijadwalkan/dibuka/ditutup | FR-50 · UC-20 | Must |
| HW-39 | Periode akademik | `/admin/periode-akademik` | Daftar + formulir, jatuh tempo otomatis H-7, status *rollover* | FR-50 · UC-20 | Must |
| HW-40 | *Rollover* semester | `/admin/rollover` | Pilih periode akademik → pratinjau (naik, Selesai Masa Program, Tertahan + daftar nama) → konfirmasi → status proses | FR-51, FR-52 · UC-21 | Must |
| HW-41 | Evaluasi kuota & pembatalan peminatan | `/admin/evaluasi-kuota` | NPD Terbit vs kuota minimum per peminatan, pilih dasar pembatalan + alasan, konfirmasi dampak (jumlah pendaftar, refund) | FR-39–41 · UC-22 | Must |
| HW-42 | Tagihan tanpa jatuh tempo | `/admin/tagihan/tanpa-jatuh-tempo` | Daftar tagihan semester tanpa jatuh tempo, pilih satu/massal, isi tanggal | FR-57 · UC-23 | Must |
| HW-43 | Cari & koreksi tagihan | `/admin/tagihan` dan `/admin/tagihan/{kode_referensi}` | Pencarian tagihan, detail, koreksi status Lunas + alasan wajib | FR-36 · UC-24 | Must |
| HW-44 | Akun & *role* | `/admin/akun` | Daftar akun admin, buat akun (undangan), ubah *role*, nonaktifkan (Super Admin terakhir dilindungi) | FR-06, FR-07 · UC-25 | Must |
| HW-45 | *Audit log* | `/admin/audit-log` | Filter pelaku, aktivitas, entitas, kanal, hasil, tanggal; detail nilai sebelum/sesudah; tanpa ubah/hapus | FR-55, FR-56 · UC-26 | Must |
| HW-46 | CMS *landing page* | `/admin/cms` | Pilih bagian (jadwal, pengumuman, alur, kontak), editor, pratinjau, terbitkan | FR-10 · UC-27 | Should |
| HW-47 | Pengaturan sistem | `/admin/pengaturan` | Parameter NFR-40: batas pelunasan 7 hari, H-7, H-14, jadwal pengingat, format & ukuran file, format NPD, batas *lockout*, masa berlaku ekspor; setiap perubahan dicatat di *audit log* | NFR-40 · K-39 | Should |
| HW-48 | Kriteria *checklist* dokumen | `/admin/kriteria-checklist` | Kelola teks kriteria per jenis dokumen (urutan, aktif/nonaktif); kriteria lama tetap tersimpan di riwayat verifikasi | FR-20 · K-39 | Should |
 
Super Admin juga dapat membuka seluruh halaman Admin SILAU, Admin Keuangan (termasuk HW-56), dan Admin Track (Matriks RBAC).
 
## I. Halaman Sistem
 
| ID | Halaman | Kapan tampil | Ref |
|---|---|---|---|
| HW-49 | 403 Tidak berhak | Akses di luar *role* | FR-07 |
| HW-50 | 404 Tidak ditemukan | Data tidak ada, termasuk Draf yang dibuka admin | BR-64 |
| HW-51 | 410 Tautan kedaluwarsa | File ekspor > 24 jam | BR-67 |
| HW-52 | 419 Sesi berakhir | Sesi habis / token CSRF kedaluwarsa | NFR-03 |
| HW-53 | 429 Terlalu banyak permintaan | *Rate limit* | NFR-08 |
| HW-54 | 500 Kesalahan server | Tanpa detail teknis | NFR-35 |
| HW-55 | Pemeliharaan (503) | Pemeliharaan terjadwal | NFR-23 |
 
---
 
## Matriks Akses Ringkas
 
| Area | CP | SIL | KEU | TRK | SA |
|---|---|---|---|---|---|
| A. Publik | ✓ | ✓ | ✓ | ✓ | ✓ |
| B. Autentikasi | ✓ | ✓ (+2FA) | ✓ (+2FA) | ✓ (+2FA) | ✓ (+2FA) |
| C. Peserta | ✓ | — | — | — | — |
| D. Admin bersama | — | ✓ (+ekspor) | ✓ | ✓ | ✓ (+ekspor) |
| E. SILAU | — | ✓ | — | — | ✓ |
| F. Keuangan (termasuk rekening institusi) | — | — | ✓ | — | ✓ |
| G. Track | — | baca kuota & dasbor kuota | baca tarif | ✓ | ✓ |
| H. Super Admin | — | — | — | — | ✓ |
 
---
 
## Komponen yang Dipakai Ulang
 
| Komponen | Komponen Figma (halaman "🧩 Komponen PV") | Dipakai di |
|---|---|---|
| Header 72px + sidebar admin per *role* | `PV/Header Portal`, `PV/Header Publik`, `PV/Sidebar` (Role=Peserta/SILAU/Keuangan/Track/SuperAdmin), `PV/Footer` | Seluruh halaman |
| Judul halaman (breadcrumb, judul, deskripsi, chip konteks) | `PV/Page Header` | Seluruh halaman portal |
| Tabel data (filter, cari, urut, paginasi, kotak centang opsional) | `PV/Field` (Select) + baris tabel | HW-17, HW-22, HW-25, HW-28, HW-30, HW-33–38, HW-42–45, HW-48, HW-56 |
| *Badge* status resmi | `PV/Badge` (Tone=Success/Warning/Error/Neutral/Info) | Seluruh halaman yang menampilkan status |
| *Progress tracker* 5 tahap | `PV/Step` | HW-13, HW-11, HW-40 |
| Tab dengan penanda kelengkapan | `PV/Tab` | HW-15, HW-26, HW-38/39, HW-46, HW-48 |
| Kartu ringkasan angka | `PV/Stat Card` | HW-17, HW-21, HW-32, HW-37, HW-40 |
| Tombol | `PV/Button` (Primary/Secondary/Danger/Link) | Seluruh halaman |
| Peringatan / info | `PV/Alert` (Info/Warning/Error/Success) | Seluruh halaman |
| *Document viewer* (PDF/JPG/PNG, URL bertanda tangan) | — (area pratinjau) | HW-15, HW-16, HW-23, HW-26, HW-29, HW-31 |
| Unggah file (validasi format/ukuran, pratinjau, progres) | — (area unggah) | HW-15, HW-16, HW-18, HW-31 |
| Dialog konfirmasi + alasan wajib | — | HW-26, HW-29, HW-31, HW-36, HW-41, HW-43, HW-44, HW-47, HW-56 |
| Dialog konflik 409 "Sudah diverifikasi admin lain" | `PV/Alert` (Error) | HW-26, HW-29 |
| *Empty state* & *toast* notifikasi | — | Seluruh halaman |