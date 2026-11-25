# Daftar Layar Aplikasi Mobile — Polibatam Verify
 
Disusun berdasarkan K-29, K-30, K-33, **K-41**, BR-59–BR-61, BR-66, BR-72, BR-74, FR-18–FR-22, FR-31–FR-35, FR-53, FR-59–FR-61, FR-69, dan *Dokumen Arsitektur* §5, §6.4, §7, §11.
 
**Ruang lingkup:** aplikasi Flutter **khusus Admin SILAU dan Admin Keuangan** untuk mempercepat verifikasi (K-29). Data Pendaftaran, ekspor, Arsip Periode, refund, rekap keuangan, dan ubah kata sandi **tidak** ada di mobile (K-30, K-41).
 
**Acuan gaya:** desain tim *Microcredential – Mobile (Copy)* (K-41): margin 20 px, area konten 350 px, kartu ringkasan 2 × 169 px (gutter 12), tombol & pencarian 48 px, navigasi bawah 76 px, radius badge 6 / tombol 8 / kartu 12 / *bottom sheet* 16, Plus Jakarta Sans, token warna sama dengan web.
 
**Konvensi:**
- ID layar `HM-xx`. Ukuran 390 × 844 (frame memanjang bila isi lebih panjang dan dapat digulir).
- Navigasi bawah 3 tab: **Dashboard · Antrean · Profil**. Label tab Antrean: **Pendaftaran** (Admin SILAU) atau **Pembayaran** (Admin Keuangan). Notifikasi dibuka dari ikon lonceng di header.
- Tanpa notifikasi *push*, tanpa kunci PIN/biometrik (K-30). Berkas tidak disimpan di perangkat.
- Setiap keputusan diambil satu per satu (BR-66), termasuk di Fast Verify (BR-74).
**Desain Figma:** file *Microcredential – Website (Copy)*, halaman **"📱 Aplikasi Mobile (HM)"**, 6 *section* per aktor. Komponen mobile di halaman "🧩 Komponen PV": `PV/M Status Bar`, `PV/M App Bar`, `PV/M Header Besar`, `PV/M Hero Sapaan`, `PV/M Quick Info`, `PV/M List Item`, `PV/M Doc Thumb`, `PV/M Nav Item`, `PV/M Bottom Nav`; ditambah komponen bersama web (Button, Badge, Field, Alert).
 
---
 
## Ringkasan
 
| Kelompok | Jumlah | ID |
|---|---|---|
| 1. Bersama (Admin SILAU & Admin Keuangan) | 7 | HM-01 – HM-07 |
| 2. Admin SILAU — verifikasi berkas | 9 | HM-08 – HM-16 |
| 3. Admin SILAU — Fast Verify per dokumen | 6 | HM-17 – HM-22 |
| 4. Admin Keuangan — verifikasi pembayaran | 6 | HM-23 – HM-28 |
| 5. Admin Keuangan — Fast Verify per tagihan | 4 | HM-29 – HM-32 |
| 6. Kondisi sistem | 5 | HM-33 – HM-37 |
| **Total** | **37** | |
 
---
 
## 1. Bersama
 
| ID | Layar | Isi utama | API v1 | FR / BR |
|---|---|---|---|---|
| HM-01 | Splash | Identitas aplikasi, versi; cek token → Dashboard atau Login | `/me` | — |
| HM-02 | Login | Email, kata sandi, tombol Masuk; lupa kata sandi diarahkan ke web | `POST /auth/login` | FR-03 |
| HM-03 | Login · kredensial salah | Banner galat umum, isian bertanda galat, info penguncian 15 menit | `POST /auth/login` | FR-03, FR-04, BR-03 |
| HM-04 | Verifikasi 2FA | 6 kotak kode TOTP, opsi *recovery code*, info sesi 12 jam | `POST /auth/two-factor` | FR-59, BR-59, BR-60 |
| HM-05 | Notifikasi | Daftar notifikasi *in-app* (baru/dibaca), tandai dibaca, tarik untuk memuat ulang | `/notifikasi` | FR-53 |
| HM-06 | Profil | Kartu identitas, status 2FA, perangkat, waktu sesi berakhir, info fitur web, Keluar | `/me` | BR-60 |
| HM-07 | Konfirmasi logout | Dialog: token perangkat dicabut | `POST /auth/logout` | FR-03 |
 
## 2. Admin SILAU — Verifikasi Berkas
 
| ID | Layar | Isi utama | API v1 | FR / BR |
|---|---|---|---|---|
| HM-08 | Dashboard Admin SILAU | Kartu sapaan, banner jumlah menunggu, 4 kartu ringkasan (total diajukan, menunggu, revisi masuk, Daftar Tunggu), tombol Fast Verify, sisa kuota terendah | `/dasbor/ringkasan` | FR-18, FR-19 |
| HM-09 | Antrean pendaftaran | Pencarian, filter Status & Peminatan, tombol Fast Verify, item: nama, nomor, peminatan, tanggal, badge Diajukan / Revisi masuk | `GET /verifikasi-berkas` | FR-18, BR-64 |
| HM-10 | Filter antrean | *Bottom sheet*: jenis antrean, periode, track, peminatan | `GET /verifikasi-berkas?…` | FR-18 |
| HM-11 | Detail verifikasi berkas | Identitas, status ganda (Berkas · Kuota peminatan), Data Formulir (NIK tersamar), miniatur dokumen + status, Riwayat Keputusan, tombol Selesaikan verifikasi | `GET /verifikasi-berkas/{id}` | FR-19, FR-20, BR-62 |
| HM-12 | Periksa dokumen | Penampil berkas (zoom, tanpa simpan), *checklist* kriteria, Tolak · Valid | `GET /berkas/{id}/tautan`, `PUT …/dokumen/{dokumen_id}` | FR-19, FR-20 |
| HM-13 | Form penolakan dokumen | *Bottom sheet*: kriteria tidak terpenuhi (otomatis tercentang) + catatan; Simpan Penolakan | `PUT …/dokumen/{dokumen_id}` | FR-20, BR-13 |
| HM-14 | Validasi alasan kosong | Galat "Pilih minimal satu alasan atau isi catatan" | — (validasi klien + 422) | BR-13 |
| HM-15 | Konfirmasi selesaikan | Dialog ringkasan per dokumen dan hasil (Perlu Revisi / Masuk Kuota / Daftar Tunggu) | `POST /verifikasi-berkas/{id}/selesai` | FR-21, FR-22, BR-17 |
| HM-16 | Hasil tersimpan | *Toast* sukses, status berkas baru, riwayat keputusan diperbarui | — | FR-21, FR-53 |
 
## 3. Admin SILAU — Fast Verify per Dokumen
 
| ID | Layar | Isi utama | FR / BR |
|---|---|---|---|
| HM-17 | Petunjuk Fast Verify | *Bottom sheet*: aturan geser (kanan = Valid bila semua kriteria dicentang, kiri = Ditolak + alasan, dokumen terakhir → ringkasan) | FR-69, BR-74 |
| HM-18 | Kartu dokumen | Progres "Pendaftar 1 dari 42 · Dokumen 3/4", kartu: pendaftar, jenis dokumen, pratinjau, *checklist*, petunjuk geser, tombol Tolak · Valid | FR-69 |
| HM-19 | Geser kanan · Valid | Kartu bertanda VALID (bingkai hijau) | FR-69, BR-74 |
| HM-20 | Geser kiri · Alasan penolakan | Kartu bertanda DITOLAK + *bottom sheet* alasan; "Batalkan geser" mengembalikan kartu | FR-69, BR-13 |
| HM-21 | Ringkasan pendaftar | Keputusan per dokumen + hasil; tombol **Selesaikan & lanjut** | FR-21, FR-69 |
| HM-22 | Antrean selesai | Rekap sesi (jumlah diselesaikan per hasil), kembali ke Dashboard | FR-69 |
 
## 4. Admin Keuangan — Verifikasi Pembayaran
 
| ID | Layar | Isi utama | API v1 | FR / BR |
|---|---|---|---|---|
| HM-23 | Dashboard Admin Keuangan | Kartu sapaan, banner jumlah menunggu, ringkasan (menunggu, siap diputuskan, lunas hari ini, menunggu berkas), Fast Verify, info refund/rekap di web | `/dasbor/ringkasan` | FR-31 |
| HM-24 | Antrean pembayaran | Pencarian, filter Jenis (formulir/semester) & Peminatan, Fast Verify, item dengan badge Siap diputuskan / Menunggu berkas | `GET /verifikasi-pembayaran` | FR-31, FR-33 |
| HM-25 | Detail verifikasi pembayaran | Identitas, status ganda (Berkas · Pembayaran), Data Tagihan (kode, nominal, rekening tujuan *snapshot*), Data Pengiriman + kecocokan nominal, bukti transfer, Riwayat Keputusan; Tolak · Tandai Lunas | `GET /verifikasi-pembayaran/{kode}` | FR-31, FR-33, BR-39, BR-71, BR-72 |
| HM-26 | Form penolakan pembayaran | *Bottom sheet*: alasan baku (mutasi tidak ditemukan, nominal tidak sesuai, bukti tidak terbaca) + catatan | `POST …/{kode}/tolak` | FR-32, BR-38 |
| HM-27 | Konfirmasi Tandai Lunas | Dialog: kode, nominal, dampak NPD terbit & tagihan Semester 1 | `POST …/{kode}/lunas` | FR-32, FR-34 |
| HM-28 | Lunas tersimpan · NPD terbit | *Toast* dengan NPD, status Pembayaran Lunas, riwayat diperbarui | — | FR-34 |
 
## 5. Admin Keuangan — Fast Verify per Tagihan
 
| ID | Layar | Isi utama | FR / BR |
|---|---|---|---|
| HM-29 | Kartu tagihan | Progres "1 dari 14 siap diputuskan", kartu: pemilik, kode, bukti, nominal vs ditransfer, pengirim, kecocokan; info tagihan "Menunggu berkas" dikecualikan | FR-69, BR-39, BR-74 |
| HM-30 | Geser kanan · Konfirmasi Lunas | Kartu bertanda LUNAS + dialog konfirmasi | FR-32, FR-69 |
| HM-31 | Geser kiri · Alasan penolakan | Kartu bertanda DITOLAK + *bottom sheet* alasan | FR-32, BR-38 |
| HM-32 | Antrean selesai | Rekap sesi (Lunas, NPD terbit, Ditolak) | FR-69 |
 
## 6. Kondisi Sistem
 
| ID | Layar | Kapan tampil | Ref |
|---|---|---|---|
| HM-33 | Konflik 409 | Data sudah diputuskan admin lain (web/mobile); keputusan tidak disimpan; Muat ulang data | FR-61, BR-61 |
| HM-34 | Sesi berakhir (401) | Token 12 jam kedaluwarsa/dicabut | BR-60 |
| HM-35 | Tanpa koneksi | Jaringan gagal; data tidak disimpan di perangkat | §7 Arsitektur |
| HM-36 | Akses ditolak (403) | Akun selain Admin SILAU / Admin Keuangan | FR-60, K-29 |
| HM-37 | Antrean kosong | Tidak ada data sesuai filter | — |
 
---
 
## Alur Utama
 
```text
Splash → Login (→ kredensial salah) → 2FA → Dashboard
Admin SILAU   : Dashboard → Pendaftaran (± Filter) → Detail → Periksa dokumen (Valid / Tolak + alasan) → … → Selesaikan → Hasil tersimpan
                Dashboard/Pendaftaran → Fast Verify → kartu dokumen (geser) → … → Ringkasan pendaftar → Selesaikan & lanjut → … → Antrean selesai
Admin Keuangan: Dashboard → Pembayaran → Detail → Tandai Lunas (konfirmasi) / Tolak (+ alasan)
                Dashboard/Pembayaran → Fast Verify → kartu tagihan (geser → konfirmasi/alasan) → … → Antrean selesai
Setiap simpan keputusan: 409 → dialog konflik → muat ulang
```
 
## Perbedaan dari Desain Tim (K-41)
 
| Desain tim | Versi dokumen | Alasan |
|---|---|---|
| Terima / Revisi / Tolak untuk seluruh pendaftaran | Valid / Ditolak per dokumen, lalu Selesaikan → sistem menetapkan Perlu Revisi / Masuk Kuota / Daftar Tunggu | FR-20, FR-21, BR-17 |
| Fast Verify per pendaftar | Per dokumen (berkas) dan per tagihan (pembayaran), dengan *checklist* & alasan wajib | BR-13, BR-74 |
| Refund, filter & ekspor di dashboard Keuangan | Hanya di web | K-30 |
| Ubah kata sandi di Profil | Di web | K-41 |
| Tanpa notifikasi | Lonceng notifikasi *in-app* di header | FR-53 |
 
## Catatan Terbuka
 
- **A-64** `[ASUMSI]` Alasan penolakan dibentuk dari kriteria/alasan baku + catatan; minimal salah satu wajib.
- Ikon masih memakai *placeholder* (kotak/glyph).
 