# Objek Basis Data di Luar ERD — Polibatam Verify

Dokumen tambahan untuk `doc/erd.md` dan `doc/keamanan-basis-data.md`. Isinya objek basis data yang **tidak tercantum di ERD**, alasan keberadaannya, dan aturan pemakaiannya. Sumber skema tetap `database/paket-db/sql/` (PRD §2.1 #4). Dokumen ini hanya melengkapi dan tidak menggantikannya.

Disetujui pemilik proyek: 25 November 2026 (temuan T-03, A-71 di `doc/registry/temuan.md`).

| Objek | Lingkungan | Dibuat oleh | Hak akses | Status |
|---|---|---|---|---|
| `telescope_entries`, `telescope_entries_tags`, `telescope_monitoring` | **Lokal saja** | Migration `database/migrations/lokal/` lewat `pv_migrator` | `pv_app`: SELECT, INSERT, UPDATE, DELETE (`08_grants_telescope_lokal.sql`) | Aktif di lokal |
| Basis data `polibatam_verify_test` + akun `pv_test` | **Lokal saja** | `07_akun_tes_lokal.sql` oleh root | `pv_test`: ALL hanya pada `polibatam_verify_test` | Aktif di lokal |

---

## 1. Tabel Telescope (debug lokal)

### 1.1 Alasan

PRD P00 (BE-0) mewajibkan **Laravel Telescope, lokal saja**, untuk memeriksa request, query, job, email, dan exception selama pengembangan. Telescope menyimpan catatannya di tiga tabel miliknya sendiri. Tabel ini bukan data domain, sehingga tidak dimasukkan ke ERD maupun ke `01_skema_tabel.sql`.

### 1.2 Struktur (bawaan `laravel/telescope` 5.x)

| Tabel | Kolom utama | Fungsi |
|---|---|---|
| `telescope_entries` | `sequence` (PK, auto increment), `uuid` (UNIQUE), `batch_id`, `family_hash`, `should_display_on_index`, `type` (VARCHAR 20), `content` (LONGTEXT, JSON), `created_at` | Satu baris per kejadian yang direkam: request, query, job, mail, exception, dsb. |
| `telescope_entries_tags` | `entry_uuid` (FK → `telescope_entries.uuid`, ON DELETE CASCADE), `tag` | Label untuk penyaringan di UI Telescope |
| `telescope_monitoring` | `tag` (PK) | Daftar tag yang sedang dipantau |

Definisi persis: `database/migrations/lokal/2026_11_25_100000_create_telescope_entries_table.php`. Isinya salinan migration paket, dengan satu perubahan: `getConnection()` dihapus agar migration ikut `--database=mysql_migrator` (AE-13). Aslinya memakai koneksi aplikasi `pv_app`, yang tidak punya hak DDL.

### 1.3 Aturan

1. **Hanya lokal.**
   - `laravel/telescope` ada di `require-dev` dan tidak di-*auto-discover* (`composer.json` → `extra.laravel.dont-discover`).
   - `App\Providers\AppServiceProvider` mendaftarkan Telescope dan memuat folder `database/migrations/lokal` **hanya bila `APP_ENV=local`**.
   - Di staging/produksi (`composer install --no-dev`) kelasnya tidak ada, sehingga migration ini tidak pernah dijalankan dan tabelnya tidak pernah dibuat.
2. **Tidak masuk `05_grants_dan_kunci.sql`**, karena `05` dijalankan di semua lingkungan dan GRANT ke tabel yang tidak ada akan gagal. Hak `pv_app` diberikan oleh `database/paket-db/sql/08_grants_telescope_lokal.sql`.
   - Ini satu-satunya pengecualian aturan "setiap tabel baru wajib masuk `05`" (README paket-db §3.2).
   - Kueri pemeriksaan terakhir di `uji/01_cek_akun_dan_grants.sql` tetap lulus di lokal setelah `08` dijalankan.
3. **`pv_app` boleh DELETE** di tabel ini, karena Telescope memangkas entri lama (`php artisan telescope:prune`, tombol *Clear* di UI). Perlakuannya sama dengan tabel infrastruktur `cache`, `sessions`, dan `jobs`. Ini bukan data domain, jadi tidak melanggar AE-03.
4. **Data sensitif disamarkan** (AE-06, NFR-32). `AppServiceProvider` memanggil:
   - `Telescope::hideRequestParameters([...])` untuk `nik`, `nomor_rekening`, `password`, `password_confirmation`, `_token`, `two_factor_secret`, `code`, `recovery_code`;
   - `hideRequestHeaders([...])` untuk `cookie`, `x-csrf-token`, `x-xsrf-token`, `authorization`.

   Terverifikasi 25-11-2026: POST `nik=2171…` tersimpan sebagai `"nik":"********"`. **Setiap field sensitif baru (mis. di P03, P09) wajib ditambahkan ke daftar ini.**
5. **Tes otomatis tidak memakai Telescope** (`phpunit.xml`: `APP_ENV=testing`, `TELESCOPE_ENABLED=false`).

### 1.4 Cara memasang ulang (setelah reset DB lokal)

Jalankan dari folder proyek, setelah `05` dan `06` (lihat `doc/uji/P00.md`):

1. Sebagai `pv_dba`, buka kunci migrator:
   ```sql
   ALTER USER 'pv_migrator'@'127.0.0.1' ACCOUNT UNLOCK;
   ```
2. `php artisan migrate --database=mysql_migrator`. Pada `APP_ENV=local` ini ikut menjalankan `database/migrations/lokal`.
3. Sebagai `pv_dba`, jalankan `database/paket-db/sql/08_grants_telescope_lokal.sql`.
4. Sebagai `pv_dba`, kunci lagi migrator:
   ```sql
   ALTER USER 'pv_migrator'@'127.0.0.1' ACCOUNT LOCK;
   ```

Akses UI: `http://127.0.0.1:8000/telescope` (lokal saja; gerbang bawaan Telescope hanya mengizinkan `APP_ENV=local`).

---

## 2. Basis data tes `polibatam_verify_test` dan akun `pv_test`

### 2.1 Alasan

Skema memakai fitur khusus MySQL: CHECK, JSON, VIEW, trigger, dan `FOR UPDATE`. Karena itu tes Pest harus berjalan di MySQL dengan skema asli, bukan SQLite (keputusan A-71).

`pv_migrator` tidak dipakai untuk tes karena `05` menguncinya di luar jendela *deploy*. Sebagai gantinya ada akun khusus `pv_test` yang **tidak punya hak apa pun pada `polibatam_verify`**.

### 2.2 Aturan

1. Dibuat oleh root lewat `database/paket-db/sql/07_akun_tes_lokal.sql` (templat). Salinan berisi password, `07_terisi.sql`, diabaikan Git.
2. Koneksi Laravel `mysql_test` (`config/database.php`), dengan kredensial `DB_TEST_*` di `.env`.
3. `phpunit.xml` memaksa `DB_CONNECTION=mysql_test` (`force="true"` pada `<env>` dan `<server>`), agar variabel yang terbawa dari terminal/IDE tidak membelokkan tes (temuan T-13).
4. **Pengaman:** `tests/TestCase.php` menolak berjalan bila nama basis data tidak berakhiran `_test`.
5. Tabel di basis data ini dibuat ulang setiap tes (`RefreshDatabase` + migration `01–03` + seeder `04`). Isinya tidak perlu di-*backup*.
6. **Jangan dibuat** di staging/produksi.
