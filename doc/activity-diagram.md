# Activity Diagram — Polibatam Verify
 
Disusun berdasarkan *Use Case Scenario* (UC-01 s.d. UC-34), *FR & BR* (FR-01 s.d. FR-66, BR-01 s.d. BR-69), *Class Diagram*, dan *Keputusan Klarifikasi Scope* (K-01 s.d. K-38).
 
**Konvensi:**
- Diagram ditulis dalam **PlantUML** dengan *swimlane* per aktor, sehingga terlihat jelas siapa melakukan apa.
- Jalur **Sistem** mewakili aplikasi Polibatam Verify (web dan REST API memakai Service layer yang sama).
- Blok `partition "Satu transaksi"` menandai langkah yang harus berhasil seluruhnya atau dibatalkan seluruhnya (NFR-27).
- Setiap diagram dilengkapi rujukan ke UC, FR, dan BR.
## Daftar Activity Diagram
 
| ID | Alur | Aktor | Use Case |
|---|---|---|---|
| AD-01 | Login dengan 2FA (web & mobile) | Pengguna, Sistem | UC-03 |
| AD-02 | Pengajuan formulir pendaftaran | Calon Peserta, Sistem | UC-05, UC-06 |
| AD-03 | Verifikasi berkas & penempatan kuota | Admin SILAU, Sistem | UC-12, UC-31 |
| AD-04 | Pembayaran & verifikasi pembayaran (formulir → NPD, semester → aktif) | Pendaftar/Peserta, Admin Keuangan, Sistem | UC-10, UC-13, UC-32 |
| AD-05 | Pembatalan otomatis 7 hari & promosi Daftar Tunggu | Penjadwal Sistem, Sistem | UC-28, UC-31 |
| AD-06 | Penutupan periode (termasuk Draf & arsip), evaluasi kuota & pembatalan peminatan | Penjadwal Sistem, Super Admin, Sistem | UC-29, UC-22 |
| AD-07 | Proses refund | Pendaftar, Admin Keuangan, Sistem | UC-11, UC-14 |
| AD-08 | *Rollover* semester & jatuh tempo manual | Penjadwal Sistem, Super Admin, Sistem | UC-21, UC-23, UC-30 |
| AD-09 | Melihat & mengekspor data pendaftaran | Admin, Sistem | UC-33 |
| AD-10 | Pembuatan & pengunduhan arsip periode | Sistem, Admin SILAU / Super Admin | UC-29, UC-34 |
 
---
 
## AD-01 — Login dengan 2FA (Web & Mobile)
 
**Rujukan:** UC-03 · FR-03, FR-04, FR-59, FR-60 · BR-03, BR-59, BR-60 · NFR-03, NFR-09, NFR-47
 
```plantuml
@startuml
title AD-01 Login dengan 2FA
 
|Pengguna|
start
:Masukkan email dan password\n(web, atau aplikasi mobile);
 
|Sistem|
if (Login dari mobile dan role bukan Admin SILAU atau Admin Keuangan?) then (ya)
  :Tolak (HTTP 403);
  stop
endif
if (Akun nonaktif atau sedang terkunci?) then (ya)
  :Tolak login;
  stop
endif
if (Kredensial benar?) then (tidak)
  :Tambah hitungan gagal login;
  if (Sudah 5 kali gagal berturut-turut?) then (ya)
    :Kunci akun 15 menit;
  endif
  :Tampilkan "Email atau password salah";
  stop
endif
 
if (Role admin?) then (ya)
  if (Authenticator sudah didaftarkan?) then (tidak)
    :Wajibkan pendaftaran authenticator;
    |Pengguna|
    :Pindai kode QR dan konfirmasi kode TOTP;
    |Sistem|
    :Simpan rahasia TOTP terenkripsi\ndan kode cadangan;
  endif
  |Pengguna|
  :Masukkan kode TOTP;
  |Sistem|
  if (Kode TOTP valid?) then (tidak)
    :Tolak, sesi atau token tidak diterbitkan;
    stop
  endif
endif
 
if (Kanal?) then (web)
  :Buat sesi\n(admin 30 menit, peserta 60 menit tidak aktif);
else (mobile)
  :Terbitkan token Sanctum per perangkat\n(berlaku 12 jam);
endif
:Arahkan ke dasbor sesuai role;
stop
@enduml
```
 
---
 
## AD-02 — Pengajuan Formulir Pendaftaran
 
**Rujukan:** UC-05, UC-06 · FR-11–14, FR-26, FR-28 · BR-07–12, BR-29, BR-32, BR-62, BR-64, BR-65, BR-68
 
```plantuml
@startuml
title AD-02 Pengajuan Formulir Pendaftaran
 
|Calon Peserta|
start
:Login (email sudah terverifikasi);
 
|Sistem|
if (Periode pendaftaran sedang dibuka?) then (tidak)
  :Tampilkan jadwal periode berikutnya;
  stop
endif
if (Sudah ada pendaftaran aktif di periode ini?) then (ya)
  :Tolak pembuatan pendaftaran baru;
  stop
endif
 
|Calon Peserta|
:Pilih track dan peminatan;
 
|Sistem|
if (Kuota maksimum peminatan penuh?) then (ya)
  :Tampilkan info: pendaftaran akan masuk\nDaftar Tunggu setelah berkas valid;
endif
 
|Calon Peserta|
:Isi formulir: nama lengkap, NIK, tempat dan tanggal lahir,\njenis kelamin, no. HP, alamat, asal sekolah, tahun lulus;
 
|Sistem|
:Simpan sebagai Draf\n(NIK dienkripsi + nik_hash);
if (Penyimpanan pertama?) then (ya)
  :Bangkitkan nomor pendaftaran\nPV-{TAHUN}-{6 digit} (BR-68);
endif
note right
  Draf hanya dapat diakses pemiliknya dan tidak
  terlihat di halaman admin (BR-64). Jika periode
  ditutup sebelum diajukan, Draf otomatis
  Dibatalkan oleh AD-06 (BR-65).
end note
 
repeat
  |Calon Peserta|
  :Pilih slot dokumen dan unggah file;
  |Sistem|
  if (Tipe file PDF/JPG/PNG dan ukuran maks 2 MB?) then (ya)
    :Simpan versi dokumen\n(nama acak, disk private);
  else (tidak)
    :Tolak unggahan, tampilkan syarat file;
  endif
repeat while (Masih ada dokumen yang akan diunggah?) is (ya) not (tidak)
 
repeat
  |Calon Peserta|
  :Ajukan formulir;
  |Sistem|
  :Validasi field dan dokumen wajib;
  backward:Tandai bagian yang belum lengkap,\npeserta melengkapinya;
repeat while (Ada yang belum lengkap?) is (ya) not (tidak)
 
if (Tarif formulir peminatan sudah diatur?) then (tidak)
  :Tolak pengajuan dan kirim peringatan\nke Admin Track;
  stop
endif
 
partition "Satu transaksi" {
  :Status = Diajukan, catat waktu pengajuan;
  :Kunci pilihan track dan peminatan;
  :Terbitkan tagihan formulir\n(tarif formulir + kode unik acak 001-999);
}
:Kirim notifikasi instruksi pembayaran;
stop
@enduml
```
 
---
 
## AD-03 — Verifikasi Berkas & Penempatan Kuota
 
**Rujukan:** UC-12, UC-31 · FR-18–23, FR-60, FR-61 · BR-13, BR-16–19, BR-61, BR-64, BR-66 · NFR-27
 
```plantuml
@startuml
title AD-03 Verifikasi Berkas dan Penempatan Kuota
 
|Admin SILAU|
start
:Login (web atau mobile) dengan TOTP;
:Buka antrean verifikasi berkas\n(urut pengajuan terlama, tanpa Draf);
:Pilih satu pendaftaran;
note right
  Tidak ada aksi verifikasi massal (BR-66).
  Setiap pendaftaran diputuskan satu per satu.
end note
 
|Sistem|
:Tampilkan data pendaftar, dokumen (tautan 15 menit),\nchecklist per jenis dokumen, sisa kuota;
 
|Admin SILAU|
repeat
  :Periksa dokumen dengan checklist;
  if (Dokumen memenuhi kriteria?) then (ya)
    :Tandai Valid;
  else (tidak)
    :Tandai Ditolak dan isi alasan (wajib);
  endif
repeat while (Masih ada dokumen yang belum diputuskan?) is (ya) not (tidak)
:Selesaikan verifikasi;
 
|Sistem|
if (Data sudah diputuskan admin lain sejak dibuka?) then (ya)
  :Tolak (HTTP 409 SUDAH_DIVERIFIKASI),\nmuat ulang data terbaru;
  stop
endif
:Catat keputusan ke audit log\n(verifikator, waktu, kanal, alasan);
 
if (Ada dokumen Ditolak?) then (ya)
  :Status = Perlu Revisi;
  :Kirim alasan per dokumen ke pendaftar;
  note right
    Pendaftar mengunggah ulang dokumen yang ditolak,
    status kembali Diajukan dan masuk antrean lagi (UC-07).
  end note
else (tidak)
  partition "Satu transaksi + kunci baris kuota" {
    :Kunci baris kuota peminatan-periode;
    if (Slot terpakai kurang dari kuota maksimum?) then (ya)
      :Status = Masuk Kuota;
      :Catat waktu masuk kuota (awal hitungan 7 hari);
      :Slot terpakai + 1;
    else (tidak)
      :Status = Daftar Tunggu;
      :Beri nomor urut berdasarkan waktu pengajuan (FIFO);
    endif
  }
  :Kirim notifikasi status ke pendaftar;
endif
stop
@enduml
```
 
---
 
## AD-04 — Pembayaran & Verifikasi Pembayaran
 
**Rujukan:** UC-10, UC-13, UC-32 · FR-29–35, FR-60, FR-61 · BR-36–42, BR-46, BR-61, BR-66
 
```plantuml
@startuml
title AD-04 Pembayaran dan Verifikasi Pembayaran
 
|Pendaftar / Peserta|
start
:Lihat tagihan\n(kode referensi, nominal + kode unik);
:Transfer sesuai nominal total\n(di luar sistem);
:Pilih tagihan dan unggah bukti transfer;
 
|Sistem|
if (Status tagihan Belum Bayar atau Ditolak?) then (tidak)
  :Opsi unggah tidak tersedia;
  stop
endif
if (Hash file bukti sudah pernah tercatat?) then (ya)
  :Tolak: "Bukti sudah pernah digunakan";
  stop
endif
:Simpan bukti, status tagihan = Menunggu Verifikasi;
 
|Admin Keuangan|
:Buka antrean verifikasi pembayaran (web atau mobile);
:Pilih satu tagihan (tanpa aksi massal);
:Cocokkan bukti dengan mutasi rekening\n(kode referensi dan nominal);
 
if (Pembayaran sesuai?) then (tidak)
  :Tolak dan isi alasan (wajib);
  |Sistem|
  if (Data sudah diputuskan admin lain?) then (ya)
    :Tolak (HTTP 409), muat ulang data;
    stop
  endif
  :Status tagihan = Ditolak, unggah ulang dibuka;
  :Catat audit log, kirim alasan ke pemilik;
  stop
endif
 
|Admin Keuangan|
:Tandai Lunas;
 
|Sistem|
if (Data sudah diputuskan admin lain?) then (ya)
  :Tolak (HTTP 409), muat ulang data;
  stop
endif
 
if (Jenis tagihan?) then (formulir)
  if (Pendaftaran sudah Masuk Kuota?) then (tidak)
    :Tolak: "Menunggu verifikasi berkas"\n(gerbang ganda);
    stop
  endif
  partition "Satu transaksi (UC-32)" {
    :Status tagihan = Lunas;
    :Bangkitkan NPD unik\nMC-{TAHUN}-{KODE_PEMINATAN}-{NO_URUT};
    :Buat Peserta Didik, status Belum Aktif Semester 1;
    :Status pendaftaran = NPD Terbit,\nrole akun = Peserta Didik;
    :Terbitkan tagihan Semester 1;
  }
  note right
    Jika tagihan Semester 1 terbit pada atau setelah H-7
    sebelum semester dimulai, jatuh tempo dikosongkan dan
    ditetapkan manual oleh Super Admin (AD-08).
  end note
else (semester N)
  :Status tagihan = Lunas;
  :Status peserta = Aktif Semester N;
  note right
    Berlaku juga untuk peserta Tertahan (Tunggakan).
    Peserta ikut rollover berikutnya.
  end note
endif
 
:Catat audit log (kanal web atau mobile);
:Kirim notifikasi ke pemilik tagihan;
stop
@enduml
```
 
---
 
## AD-05 — Pembatalan Otomatis 7 Hari & Promosi Daftar Tunggu
 
**Rujukan:** UC-28, UC-31 · FR-23, FR-24 · BR-18, BR-19, BR-21 · NFR-25, NFR-27
 
```plantuml
@startuml
title AD-05 Pembatalan Otomatis dan Promosi Daftar Tunggu
 
|Penjadwal Sistem|
start
:Jalankan pemeriksaan setiap jam;
 
|Sistem|
:Cari pendaftaran Masuk Kuota lebih dari 7 hari\ndengan tagihan formulir Belum Bayar atau Ditolak\n(kecuali Menunggu Verifikasi);
 
while (Masih ada pendaftaran kedaluwarsa?) is (ya)
  partition "Satu transaksi + kunci baris kuota" {
    :Status pendaftaran = Dibatalkan;
    :Status tagihan formulir = Dibatalkan;
    :Slot terpakai - 1;
    if (Ada pendaftar Daftar Tunggu di peminatan-periode ini?) then (ya)
      :Naikkan pendaftar dengan waktu pengajuan\npaling awal menjadi Masuk Kuota;
      :Slot terpakai + 1, catat waktu masuk kuota;
    endif
  }
  :Notifikasi pembatalan ke pendaftar yang dibatalkan;
  :Notifikasi ke pendaftar yang naik (jika ada);
endwhile (tidak)
 
:Catat hasil eksekusi;
note right
  Jika proses gagal, dicoba ulang otomatis.
  Alert ke tim teknis setelah 3 kegagalan berturut-turut.
  Proses bersifat idempoten.
end note
stop
@enduml
```
 
Promosi Daftar Tunggu yang sama juga dipicu saat kuota maksimum ditambah (UC-18). Pada kasus itu, sistem menaikkan pendaftar sebanyak slot yang bertambah.
 
---
 
## AD-06 — Penutupan Periode, Evaluasi Kuota & Pembatalan Peminatan
 
**Rujukan:** UC-29, UC-22 · FR-25, FR-39–42, FR-62, FR-65 · BR-22–24, BR-47, BR-48, BR-63, BR-65, BR-69
 
```plantuml
@startuml
title AD-06 Penutupan Periode dan Pembatalan Peminatan
 
|Penjadwal Sistem|
start
:Jalankan pemeriksaan setiap 15 menit;
 
|Sistem|
if (Ada periode yang melewati tanggal selesai?) then (tidak)
  stop
endif
partition "Satu transaksi" {
  :Status periode = Ditutup;
  :Seluruh Daftar Tunggu = Tidak Tertampung;
  :Buat refund bagi Tidak Tertampung\nyang sudah membayar formulir;
  :Seluruh Draf pada periode ini = Dibatalkan\n(alasan: "Periode pendaftaran ditutup\nsebelum formulir diajukan"), kunci formulir;
}
note right
  Draf belum memiliki tagihan (tagihan formulir
  terbit saat diajukan), sehingga tidak ada
  tagihan yang dibatalkan maupun refund (BR-65).
  Untuk volume besar, transaksi dipecah per batch
  dan proses bersifat idempoten.
end note
:Catat ringkasan eksekusi ke audit log;
:Notifikasi pendaftar Tidak Tertampung\ndan pemilik Draf yang dibatalkan;
:Jadwalkan pembuatan arsip periode\n(job latar belakang, lihat AD-10);
:Buka evaluasi kuota minimum untuk Super Admin;
 
|Super Admin|
:Tinjau jumlah NPD Terbit dibanding kuota minimum\nper peminatan;
if (Ada peminatan yang akan dibatalkan?) then (tidak)
  stop
endif
:Pilih peminatan dan dasar pembatalan\n(kuota minimum atau kesepakatan terbaru);
 
|Sistem|
if (Dasar kuota minimum, tetapi peminatan memenuhi kuota?) then (ya)
  :Tolak pembatalan;
  stop
endif
if (Dasar kesepakatan, tetapi alasan kosong?) then (ya)
  :Minta alasan diisi;
  stop
endif
 
partition "Satu transaksi" {
  :Status peminatan-periode = Dibatalkan;
  :Seluruh pendaftaran terkait = Dibatalkan\n(termasuk yang sudah NPD Terbit);
  :Peserta Didik terkait = Dibatalkan;
  :Tagihan yang belum Lunas = Dibatalkan;
  :Buat refund dari tagihan yang sudah dibayar\ndan belum pernah di-refund;
}
:Catat audit log (dasar, alasan, pelaksana);
:Kirim notifikasi pembatalan dan instruksi refund;
note right
  Arsip periode tidak berubah: isinya tetap
  status per waktu penutupan (BR-69).
end note
stop
@enduml
```
 
---
 
## AD-07 — Proses Refund
 
**Rujukan:** UC-11, UC-14 · FR-42–44, FR-62 · BR-48–51, BR-63 · NFR-06
 
```plantuml
@startuml
title AD-07 Proses Refund
 
|Pendaftar|
start
:Terima notifikasi refund\n(sebab, rincian, nominal);
:Isi nama bank, nomor rekening,\nnama pemilik rekening;
 
|Sistem|
if (Data rekening valid?) then (tidak)
  :Tolak, minta perbaikan;
  stop
endif
:Enkripsi nomor rekening (AES-256);
:Status refund = Diproses;
 
|Admin Keuangan|
:Buka modul refund (web);
:Buka nomor rekening lengkap;
 
|Sistem|
:Catat akses nomor rekening di audit log;
 
|Admin Keuangan|
while (Ada rincian dari tagihan yang belum terverifikasi?) is (ya)
  :Cocokkan dengan mutasi rekening;
  if (Pembayaran ditemukan di mutasi?) then (ya)
    :Sertakan rincian;
  else (tidak)
    :Kecualikan rincian dan isi alasan;
    |Sistem|
    :Hitung ulang nominal refund;
    |Admin Keuangan|
  endif
endwhile (tidak)
 
|Sistem|
if (Seluruh rincian dikecualikan (nominal Rp0)?) then (ya)
  :Status refund = Ditolak, simpan alasan;
  :Catat audit log, kirim notifikasi ke penerima;
  stop
endif
 
|Admin Keuangan|
:Transfer dana ke rekening penerima\n(di luar sistem);
:Tandai Selesai dan unggah bukti transfer;
 
|Sistem|
if (Bukti transfer diunggah?) then (tidak)
  :Tolak perubahan status;
  stop
endif
:Status refund = Selesai;
:Catat audit log, kirim notifikasi ke penerima;
stop
@enduml
```
 
---
 
## AD-08 — Rollover Semester & Jatuh Tempo Manual
 
**Rujukan:** UC-21, UC-23, UC-30 · FR-27, FR-51, FR-52, FR-57, FR-58 · BR-34, BR-35, BR-44, BR-45 · NFR-18
 
```plantuml
@startuml
title AD-08 Rollover Semester dan Jatuh Tempo Manual
 
|Penjadwal Sistem|
start
:Pemeriksaan harian 08.00 WIB;
if (H-14 sebelum semester dimulai dan rollover belum dijalankan?) then (ya)
  :Kirim peringatan ke Super Admin\n(diulang setiap hari);
endif
 
|Super Admin|
:Buka menu rollover, pilih periode akademik;
 
|Sistem|
if (Rollover periode ini sudah pernah dijalankan?) then (ya)
  :Tolak eksekusi ulang;
  stop
endif
:Tampilkan pratinjau: naik semester,\nSelesai Masa Program, Tertahan;
 
|Super Admin|
if (Konfirmasi eksekusi?) then (tidak)
  stop
endif
 
|Sistem|
partition "Satu transaksi (gagal = rollback seluruhnya)" {
  while (Masih ada peserta Aktif atau Belum Aktif yang belum diproses?) is (ya)
    if (Tagihan semester N sudah Lunas?) then (tidak)
      :Status = Tertahan (Tunggakan),\ntanpa tagihan baru;
    elseif (N sama dengan durasi peminatan?) then (ya)
      :Status = Selesai Masa Program;
    else (tidak)
      :Semester berjalan = N+1,\nstatus Belum Aktif;
      if (Tarif semester N+1 sudah diatur?) then (ya)
        :Terbitkan tagihan semester N+1;
        if (Tagihan terbit sebelum H-7 semester dimulai?) then (ya)
          :Jatuh tempo = H-7 sebelum semester dimulai;
        else (tidak)
          :Jatuh tempo dikosongkan,\nmenunggu Super Admin;
        endif
      else (tidak)
        :Tagihan tidak diterbitkan,\ncatat peringatan untuk Admin Track dan Super Admin;
      endif
    endif
  endwhile (tidak)
  :Tandai periode akademik sudah di-rollover;
}
:Catat ringkasan ke audit log;
:Kirim notifikasi ke peserta;
 
if (Ada tagihan tanpa jatuh tempo?) then (ya)
  :Kirim notifikasi ke Super Admin;
  |Super Admin|
  :Pilih tagihan dan isi tanggal jatuh tempo\n(boleh lebih awal dari hari ini);
  |Sistem|
  :Simpan jatuh tempo manual, catat audit log;
  note right
    Jatuh tempo yang sudah lewat langsung dihitung
    sebagai tunggakan dan tidak memicu pengingat H-7/H-1.
  end note
endif
stop
@enduml
```
 
---
 
## AD-09 — Melihat & Mengekspor Data Pendaftaran
 
**Rujukan:** UC-33 · FR-63, FR-64 · BR-04, BR-58, BR-62, BR-64, BR-66, BR-67, BR-68 · K-37, K-38
 
```plantuml
@startuml
title AD-09 Melihat dan Mengekspor Data Pendaftaran
 
|Admin|
start
:Login dengan TOTP;
:Buka menu Data Pendaftaran;
 
|Sistem|
:Ambil pendaftaran periode aktif\ndengan status selain Draf;
:Hitung jumlah Draf (angka saja);
:Tampilkan tabel (No., nama, track, peminatan,\nwaktu pengajuan, status), total data,\ndan jumlah Draf;
 
|Admin|
:Isi filter atau cari nama / No. pendaftaran / NIK;
 
|Sistem|
if (Pencarian NIK?) then (ya)
  :Hitung HMAC NIK, cocokkan nik_hash;
endif
:Tampilkan hasil (NIK tersamar);
 
|Admin|
if (Aksi?) then (lihat detail)
  |Sistem|
  if (Pendaftaran berstatus Draf?) then (ya)
    :Tolak (HTTP 403/404);
    stop
  endif
  :Tampilkan detail dan dokumen baca-saja\n(tanpa aksi verifikasi);
  stop
else (ekspor)
  |Sistem|
  if (Role Admin SILAU atau Super Admin?) then (tidak)
    :Tolak (HTTP 403);
    stop
  endif
  |Admin|
  :Pilih cakupan (seluruh hasil filter\natau baris terpilih) dan format Excel/PDF;
  |Sistem|
  if (Jumlah baris > 10.000?) then (ya)
    :Jalankan job ekspor di latar belakang;
    :Kirim tautan unduhan lewat notifikasi;
  else (tidak)
    :Bentuk file (NIK tersamar, tanpa Draf,\ntanpa nomor rekening dan file dokumen);
    :Kirim file ke admin;
  endif
  :Catat audit log ekspor_data_pendaftaran\n(pelaku, filter, jumlah baris, format);
  note right
    File ekspor dan tautannya berlaku 24 jam,
    lalu dihapus otomatis oleh ekspor:bersihkan
    (BR-67). Arsip periode tidak terpengaruh.
  end note
endif
stop
@enduml
```
 
---
 
## AD-10 — Pembuatan & Pengunduhan Arsip Periode
 
**Rujukan:** UC-29, UC-34 · FR-65, FR-66 · BR-58, BR-64, BR-69 · NFR-24, NFR-25, NFR-49 · K-38
 
```plantuml
@startuml
title AD-10 Pembuatan dan Pengunduhan Arsip Periode
 
|Sistem|
start
:Job BuatArsipPeriode dijalankan\nsetelah penutupan periode (AD-06);
if (Arsip periode ini sudah Tersedia?) then (ya)
  :Lewati (idempoten, tidak menimpa);
  stop
endif
:Status arsip = Diproses;
:Ambil data dari v_data_pendaftaran\n(tanpa Draf, status per waktu penutupan);
:Bentuk file .xlsx: lembar data (NIK tersamar)\ndan lembar ringkasan per peminatan dan status;
if (Pembuatan berhasil?) then (tidak)
  :Tambah hitungan percobaan, catat galat;
  if (Percobaan masih tersisa?) then (ya)
    :Jadwalkan percobaan ulang;
    stop
  endif
  :Status arsip = Gagal;
  :Notifikasi Super Admin;
  stop
endif
:Simpan file ke disk arsip (write-once);
:Hitung dan simpan checksum SHA-256;
:Status arsip = Tersedia;
:Catat audit log buat_arsip_periode (aksi sistem);
 
|Admin SILAU / Super Admin|
:Buka menu Arsip Periode;
:Pilih Unduh pada arsip Tersedia;
 
|Sistem|
if (Role Admin SILAU atau Super Admin?) then (tidak)
  :Tolak (HTTP 403);
  stop
endif
:Hitung ulang SHA-256 file;
if (Checksum cocok?) then (tidak)
  :Batalkan unduhan, tampilkan pesan\n"Arsip tidak dapat diverifikasi";
  :Catat audit log arsip_checksum_tidak_cocok;
  :Peringatan ke Super Admin;
  stop
endif
:Kirim file;
:Catat audit log unduh_arsip_periode;
note right
  Tidak ada aksi ubah, ganti, atau hapus arsip.
  Arsip disimpan permanen dan ikut backup
  di lokasi terpisah (NFR-24, NFR-49).
end note
stop
@enduml
```
 
---
 
## Keterlacakan
 
| AD | Use Case | FR | BR |
|---|---|---|---|
| AD-01 | UC-03 | FR-03, FR-04, FR-59, FR-60 | BR-03, BR-59, BR-60 |
| AD-02 | UC-05, UC-06 | FR-11–14, FR-26, FR-28 | BR-07–12, BR-29, BR-32, BR-62, BR-64, BR-65, BR-68 |
| AD-03 | UC-12, UC-31 | FR-18–23, FR-60, FR-61 | BR-13, BR-16–19, BR-61, BR-64, BR-66 |
| AD-04 | UC-10, UC-13, UC-32 | FR-29–35, FR-60, FR-61 | BR-36–42, BR-46, BR-61, BR-66 |
| AD-05 | UC-28, UC-31 | FR-23, FR-24 | BR-18, BR-19, BR-21 |
| AD-06 | UC-29, UC-22 | FR-25, FR-39–42, FR-62, FR-65 | BR-22–24, BR-47, BR-48, BR-63, BR-65, BR-69 |
| AD-07 | UC-11, UC-14 | FR-42–44, FR-62 | BR-48–51, BR-63 · K-34 |
| AD-08 | UC-21, UC-23, UC-30 | FR-27, FR-51, FR-52, FR-57, FR-58 | BR-34, BR-35, BR-44, BR-45 |
| AD-09 | UC-33 | FR-63, FR-64 | BR-04, BR-58, BR-62, BR-64, BR-66, BR-67, BR-68 |
| AD-10 | UC-29, UC-34 | FR-65, FR-66 | BR-58, BR-64, BR-69 |
 
Use case yang tidak digambar sebagai activity diagram (katalog, registrasi, CRUD data master, CMS, *audit log*) memiliki alur linear tanpa percabangan penting, sehingga cukup dijelaskan oleh *use case scenario*.
 
---
 
## Status Asumsi
 
| # | Asumsi | Bagian | Status |
|---|---|---|---|
| A-48 | Saat peminatan dibatalkan, Peserta Didik yang sudah memiliki NPD pada peminatan itu ikut berstatus Dibatalkan. | AD-06 | Disetujui (K-34) |
| A-49 | Jika seluruh rincian sebuah refund dikecualikan, refund berstatus Ditolak dengan alasan, tanpa bukti transfer. | AD-07 | Disetujui (K-34) |
| A-57 | Nomor pendaftaran `PV-{TAHUN}-{6 digit}` dibangkitkan saat Draf pertama kali disimpan (BR-68). | AD-02 | Disetujui (K-37) |
| A-58 | Ekspor data pendaftaran hanya untuk Admin SILAU dan Super Admin (BR-67). | AD-09 | Disetujui (K-37) |
| A-59 | File ekspor dan tautannya berlaku 24 jam (BR-67). | AD-09 | Disetujui (K-38) |
| A-60 | Arsip periode dibuat otomatis dan disimpan permanen (BR-69). | AD-06, AD-10 | Disetujui (K-38) |
| A-61 | `[ASUMSI]` Arsip berformat `.xlsx` saja dengan status per waktu penutupan. | AD-10 | Menunggu konfirmasi |