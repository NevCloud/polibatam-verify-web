# Basis Data Polibatam Verify — Paket Implementasi

Implementasi *ERD & Kamus Data* dan *Dokumen Keamanan Basis Data* menjadi skrip MySQL yang siap dijalankan di **Laragon (MySQL 9.7)**, lalu di-*deploy* ke Linux.

```text
polibatam-verify-db/
├── sql/                               ← dijalankan berurutan
│   ├── 00_provisioning_akun.sql       root        DB, akun, kebijakan password
│   ├── 01_skema_tabel.sql             pv_migrator 36 tabel (domain + framework)
│   ├── 02_view.sql                    pv_migrator 4 VIEW laporan
│   ├── 03_trigger.sql                 pv_migrator 6 trigger integritas
│   ├── 04_seed_data_awal.sql          pv_migrator role, permission, pengaturan, checklist, track/peminatan
│   ├── 05_grants_dan_kunci.sql        pv_dba      GRANT per tabel + kunci pv_migrator
│   └── 06_seed_dummy_lokal.sql        pv_dba      data uji (KHUSUS LOKAL)
├── uji/                               ← evidence UTS/UAS
│   ├── 01_cek_akun_dan_grants.sql     root        UTS-03/05/07/08
│   ├── 02_uji_pv_app_allow_deny.sql   pv_app      UTS-06 (TST-01..09)
│   ├── 03_uji_pv_report_view.sql      pv_report   UTS-09 (TST-10..13)
│   ├── 04_uji_integritas_dba.sql      pv_dba      TST-14..17, TST-35, CC-01/03/04
│   ├── 05_transaksi_commit_rollback.sql pv_app    UTS-13/14 (TST-23, TST-24)
│   ├── 06_verifikasi_restore.sql      pv_dba      UTS-18 (TST-37..39)
│   └── 07_locking_dua_sesi.md         pv_app ×2   UTS-15 (CC-01, CC-02, CC-05)
└── ops/
    ├── my.ini.baseline                security baseline BL-06..BL-20
    ├── dba.cnf.example, backup.cnf.example
    ├── env.database.example           potongan .env Laravel
    ├── backup-db.ps1, restore-db.ps1  Laragon
    └── backup-db.sh                   Linux (cron + GPG)
```

---

## 1. Urutan Eksekusi di Laragon

Jalankan dari **Terminal Laragon** di folder paket. `mysql` = `C:\laragon\bin\mysql\mysql-9.7.0-winx64\bin\mysql.exe`.

| # | Langkah | Perintah |
|---|---|---|
| 0 | Terapkan baseline: tempel `ops/my.ini.baseline` ke `my.ini` **tetapi baris `skip_name_resolve` dikomentari dulu**, buat folder `C:/laragon/data/mysql-secure`, restart MySQL | Menu Laragon → MySQL → my.ini |
| 1 | Ganti semua `<RAHASIA_...>` di `00_provisioning_akun.sql` dengan password acak ≥ 24 karakter (huruf besar, kecil, angka, simbol) | — |
| 2 | Provisioning sebagai root | `mysql -u root < sql/00_provisioning_akun.sql` |
| 3 | Skema, VIEW, trigger, seed sebagai migrator | `mysql -h 127.0.0.1 -u pv_migrator -p polibatam_verify < sql/01_skema_tabel.sql` lalu `02`, `03`, `04` |
| 4 | GRANT & kunci migrator sebagai DBA | `mysql -h 127.0.0.1 -u pv_dba -p < sql/05_grants_dan_kunci.sql` |
| 5 | Data dummy (lokal saja) | `mysql -h 127.0.0.1 -u pv_dba -p < sql/06_seed_dummy_lokal.sql` |
| 6 | Aktifkan `skip_name_resolve = ON`, restart MySQL (lihat Peringatan 1) | my.ini |
| 7 | Jalankan uji & ambil *evidence* | `mysql -h 127.0.0.1 -u pv_app -p --force polibatam_verify < uji/02_uji_pv_app_allow_deny.sql`, dst. |
| 8 | Hapus password dari salinan `00_provisioning_akun.sql` | — |

**Reset data lokal:** `DROP DATABASE polibatam_verify;` (sebagai root), buat ulang dengan `CREATE DATABASE polibatam_verify CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;`, buka kunci migrator (`ALTER USER 'pv_migrator'@'127.0.0.1' ACCOUNT UNLOCK;`), lalu ulangi langkah 3–5.

---

## 2. Akun Basis Data

| Akun | Host (lokal) | Hak | Dipakai oleh | Status normal |
|---|---|---|---|---|
| `root` | `localhost` | Penuh | Darurat (*break-glass*) saja | Berpassword |
| `pv_dba` | `127.0.0.1` | ALL pada `polibatam_verify` & `_restore` + GRANT OPTION; global `CREATE USER`, `SHOW DATABASES`, `SET_ANY_DEFINER` | Pengembang/DBA: GRANT, *restore* | Terbuka; terkunci 1 hari setelah 5 gagal login |
| `pv_migrator` | `127.0.0.1` | ALL pada `polibatam_verify`, tanpa GRANT OPTION | `php artisan migrate --database=mysql_migrator` | **Terkunci** di luar jendela *deploy* |
| `pv_app` | `127.0.0.1` | Per tabel: SELECT/INSERT/UPDATE domain, SELECT/INSERT riwayat & audit, DELETE hanya tabel infrastruktur | Laravel: web, API, *queue*, *scheduler* | Terbuka |
| `pv_report` | `127.0.0.1` | SELECT pada 4 VIEW saja | Koneksi `mysql_report`: dasbor, ekspor, arsip | Terbuka |
| `pv_backup` | `127.0.0.1` | SELECT, SHOW VIEW, TRIGGER, EVENT | `ops/backup-db.ps1` | Terbuka |

Produksi Linux: `pv_dba`/`pv_backup` → host `localhost`; `pv_app`/`pv_report`/`pv_migrator` → IP privat server aplikasi (A-59).

### Akun aplikasi dummy (06, lokal saja)

Semua berpassword `Uji12345!`.

| ID | Email | Role |
|---|---|---|
| 1 | superadmin@polibatam-verify.test | super_admin |
| 2 | silau@polibatam-verify.test | admin_silau |
| 3 | keuangan@polibatam-verify.test | admin_keuangan |
| 4 | track@polibatam-verify.test | admin_track |
| 10, 11 | uji1@contoh.test, uji2@contoh.test | calon_peserta (uji *salting*) |
| 12 | peserta1@contoh.test | calon_peserta — pendaftaran 45, TG-UJI-0001 |
| 13 | peserta2@contoh.test | peserta_didik — NPD MC-TRPL-2026-0001 |
| 14 | peserta3@contoh.test | calon_peserta — Draf PV-2026-000043 |
| 15 | peserta4@contoh.test | calon_peserta — Diajukan PV-2026-000044 |

Akun admin belum punya TOTP; Fortify meminta pengaturan 2FA saat login pertama (K-33). NIK dan nomor rekening sengaja `NULL` karena *ciphertext*-nya harus dibuat aplikasi dengan `APP_KEY`.

Produksi: Super Admin pertama dibuat lewat *seeder*/perintah Laravel dengan password dari `.env` sekali pakai, bukan dari skrip SQL.

---

## 3. Ketentuan Basis Data

### 3.1 Kredensial
1. Password akun MySQL ≥ 24 karakter acak dari *password manager*; komponen `validate_password` (MEDIUM, min. 12) menolak password lemah.
2. Password hanya ada di `.env` (aplikasi) dan file `*.cnf` (operasional). Keduanya tidak masuk Git; `.cnf` hanya dapat dibaca akun pengembang.
3. Di file `.cnf` dan `.env`, password **wajib diberi tanda kutip**.
4. Skrip operasional memakai `--defaults-extra-file`, tidak pernah `-p<password>`.
5. Rotasi bila ada indikasi bocor atau anggota tim keluar. `pv_dba` tidak dapat memakai ulang 5 password terakhir.
6. `APP_KEY` dan `NIK_HMAC_KEY` disimpan di *password manager* tim, **terpisah** dari file *backup*.

### 3.2 Perubahan Skema
1. Perubahan skema hanya lewat *migration* oleh `pv_migrator`; buka kunci → `migrate` → kunci lagi.
2. Setiap tabel atau VIEW baru **wajib** ditambahkan ke `05_grants_dan_kunci.sql`, lalu skrip dijalankan ulang oleh `pv_dba` (idempoten). Cek dengan kueri terakhir di `uji/01`: hanya `migrations` dan `v_*` yang boleh tanpa hak `pv_app`.
3. Tabel riwayat (`tarifs`, `dokumen_versis`, `verifikasi_dokumens`, `audit_logs`) tidak boleh diberi UPDATE untuk `pv_app`.
4. Kolom status tetap `VARCHAR(30)`; nilai baru cukup didaftarkan di *state machine*.
5. VIEW tidak boleh memuat NIK, `nik_hash`, alamat, tanggal lahir, rekening, password, token, atau *path* file.

### 3.3 Data
1. Data domain tidak pernah dihapus: nonaktifkan (`is_active`) atau ubah status. `pv_app` memang tidak punya DELETE.
2. Uang `BIGINT UNSIGNED` Rupiah; waktu disimpan UTC (`default_time_zone = '+00:00'`).
3. NIK & rekening hanya disimpan terenkripsi; pencarian NIK lewat `nik_hash`.
4. `audit_logs` *append-only*; penghapusan entri lewat masa retensi butuh berita acara DBA (Dok. §12.4).
5. Nomor pendaftaran dan NPD tidak dapat diubah setelah terbit (dijaga *trigger*).

### 3.4 Backup & Restore
1. Harian 01.00 WIB dengan `pv_backup`; retensi 7 harian + 4 mingguan (A-57).
2. Uji *restore* setiap rilis besar dan minimal sekali per semester, **selalu** ke `polibatam_verify_restore`.
3. Saat verifikasi *restore*, beri hak sementara `pv_migrator` pada DB uji lalu cabut lagi (sudah ada di `uji/06`).

---

## 4. Peringatan Teknis

1. **`skip_name_resolve` di Windows.** Setelah aktif, `root@localhost` tidak lagi cocok untuk koneksi TCP `127.0.0.1` (HeidiSQL, terminal). Karena itu aktifkan **setelah** akun `pv_*` dibuat dan `pv_dba` terbukti bisa login. Untuk administrasi harian gunakan `pv_dba`.
2. **Penguncian akun.** 5 kali gagal login mengunci `pv_dba`/`pv_migrator` selama 1 hari. Buka sebagai root: `ALTER USER 'pv_dba'@'127.0.0.1' ACCOUNT UNLOCK;`. Penyebab paling umum: password berisi `#`/`;` tanpa tanda kutip di `.cnf`.
3. **`SET_ANY_DEFINER`** ada di MySQL 8.2+ (termasuk 9.7). Di MySQL 8.0/8.1 ganti dengan `SET_USER_ID`.
4. **`log_bin_trust_function_creators = ON`** diperlukan karena binlog aktif (BL-20) dan `pv_migrator` tidak punya SUPER. Tanpa ini `03_trigger.sql` gagal dengan `ERROR 1419`.
5. **`03_trigger.sql` memakai `DELIMITER`.** Jalankan lewat klien `mysql`/HeidiSQL. Di *migration* Laravel, tulis tiap trigger sebagai satu `DB::unprepared()` tanpa `DELIMITER`.

---

## 5. Penyesuaian terhadap Dokumen Desain

| # | Perubahan | Alasan | Dokumen terdampak |
|---|---|---|---|
| P-01 | `pv_app` diberi `SELECT, INSERT, UPDATE` pada `arsip_periodes` | Tabel belum tercantum di §5.2 padahal ditulis *job* `BuatArsipPeriode`; tetap tanpa DELETE | Keamanan §4.2, §5.2 |
| P-02 | Definisi VIEW `v_data_pendaftaran` + GRANT ke `pv_report` | Disebut di ERD/Arsitektur tetapi belum didefinisikan | Keamanan §7.2 |
| P-03 | `pv_dba` & `pv_backup` memakai host `127.0.0.1` | Sesuai catatan Laragon §3.1 | — |
| P-04 | Kolom bantu `tarifs.semester_kunci` (= `IFNULL(semester_ke,0)`) di indeks UNIQUE | UNIQUE dengan `semester_ke NULL` tidak mencegah dua tarif formulir pada tanggal efektif sama | ERD §3.4 |
| P-05 | Trigger tambahan: arsip tidak dapat dihapus, NPD & nomor pendaftaran tidak dapat diubah | Menegakkan BR-41, BR-68, BR-69 di tingkat DB | ERD §3.7, §3.12, §3.21 |
| P-06 | CHECK constraint (kuota, kode unik, total tagihan, alasan tolak, format nomor, ukuran file, dll.) | Pengaman terakhir bila aplikasi *bug* | ERD §3 |
| P-07 | `SHOW GRANTS` dipindah ke `uji/01` (dijalankan root) | `pv_dba` sengaja tanpa akses `mysql.*`, sehingga tidak bisa `SHOW GRANTS FOR` akun lain | Keamanan §5.4 |
| P-08 | Penguncian `pv_migrator` dilakukan `pv_dba` di akhir langkah 05 | Migrator harus terbuka selama 01–04 | Keamanan §3.2 |
| P-09 | 6 *role* spatie (`calon_peserta` dan `peserta_didik` terpisah) + 26 *permission* `[ASUMSI]` | Sesuai enum `Role` di Class Diagram dan BR-06 | — |
| P-10 | Penghitung `urut_npd_{tahun}` di `pengaturans` | Dibutuhkan CC-07 | ERD §3.19 |
| P-11 | `pv_backup` diberi `EVENT` | `mysqldump` memeriksa *event*; mencegah peringatan | Keamanan §3.2 |

---

## 6. Hasil Uji Pra-Rilis

Diuji di MySQL 8.0.46 (Linux, `SET_ANY_DEFINER` diganti `SET_USER_ID`) pada 22 November 2026. Semua skrip `sql/00–06` berjalan tanpa *error*.

| Uji | Hasil |
|---|---|
| TST-01, TST-02 (ALLOW) | Lulus |
| TST-03..06, TST-08, TST-09 (DENY) | Lulus: `ERROR 1142`/`1227` sesuai harapan; ALTER, `INTO OUTFILE`, DELETE arsip juga ditolak |
| TST-10..13 (VIEW) | Lulus: kolom sensitif `ERROR 1054`, tabel dasar `ERROR 1142`; Draf tidak muncul |
| TST-14..17 (hash & salting) | Lulus: `$2y$12$`, panjang 60, `hash_identik = 0`, `bukan_bcrypt = 0` |
| TST-23, TST-24 (COMMIT/ROLLBACK) | Lulus: semua tabel kembali setelah `ROLLBACK`; berubah setelah `COMMIT` |
| TST-35 & trigger lain | Lulus: `ERROR 1644` untuk UPDATE/DELETE audit, ubah NPD, ubah nomor, ubah/hapus arsip tersedia |
| CC-01/03/04 (pengaman DB) | Lulus: `chk_kuota_slot`, UNIQUE kode unik terbuka (kode lama boleh dipakai lagi setelah lunas), UNIQUE `refund_items.tagihan_id` |
| Backup `pv_backup` → restore `pv_dba` (TST-37..39) | Lulus: jumlah baris & `CHECKSUM TABLE` sama, 6 trigger & 4 VIEW terpulihkan dan aktif |

Butir baseline yang bergantung `my.ini` (BL-06, BL-11, BL-14, BL-20, dll.) harus dicek ulang di Laragon Anda dengan `uji/01`.
