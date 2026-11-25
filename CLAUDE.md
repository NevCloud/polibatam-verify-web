# Polibatam Verify — CLAUDE.md

Panduan konteks proyek untuk Claude Code CLI. Semua dokumen lengkap ada di Project claude.ai **"Polibatam Verify"**.

---

## Deskripsi Sistem

**Polibatam Verify** — sistem informasi terintegrasi untuk mengelola pendaftaran dan administrasi akademik program **Microcredential Politeknik Negeri Batam**.

Alur end-to-end:
1. Pengisian formulir pendaftaran berbayar
2. Verifikasi kelengkapan berkas (Admin SILAU)
3. Validasi pembayaran (Admin Keuangan)
4. Pengelolaan tagihan semester dinamis (berbeda per track & peminatan)
5. Transisi semester peserta

---

## Arsitektur & Stack Teknologi

### Pola Arsitektur
- **Monolit modular Laravel** — satu Service layer, dua pintu masuk: Web (Blade) + REST API (`/api/v1`)
- Semua Business Rules (BR) **hanya ditulis di Service/Action layer**, tidak di Controller

### Backend & Web
| Komponen | Teknologi |
|---|---|
| Bahasa | PHP 8.4 |
| Framework | Laravel 13.x |
| Web UI | Blade + Tailwind CSS + Alpine.js (Vite) |
| Auth Web | Session guard (Cookie HttpOnly, Secure, SameSite) |
| Auth API | Laravel Sanctum (personal access token) |
| 2FA Admin | Laravel Fortify (TOTP, **wajib**) |
| Role & Izin | `spatie/laravel-permission` |
| State Machine | `spatie/laravel-model-states` |
| Ekspor | `maatwebsite/excel` (.xlsx) + `barryvdh/laravel-dompdf` |
| Dokumentasi API | Scribe (OpenAPI + Postman collection) |
| Queue | Laravel Queue (driver: `database` lokal, `Redis` produksi) |
| Email | Laravel Mail → Mailpit (lokal) → Brevo/SMTP Polibatam |
| DB | MySQL 9.7 LTS (InnoDB, utf8mb4) |
| Web Server | Nginx + PHP-FPM |
| Dev Environment | Laragon (Windows) |

### Mobile
| Komponen | Teknologi |
|---|---|
| Framework | Flutter (Dart), stabil terbaru |
| HTTP | `dio` (interceptor token & error) |
| State | `flutter_riverpod` [ASUMSI] |
| Routing | `go_router` [ASUMSI] |
| Token storage | `flutter_secure_storage` |
| Model | `freezed` + `json_serializable` |

**Mobile HANYA untuk Admin SILAU & Admin Keuangan** (verifikasi berkas & pembayaran saja).

### Testing & Kualitas
| Alat | Target |
|---|---|
| Pest (PHPUnit) | Coverage ≥ 80% modul kritis |
| Postman + Newman | API testing |
| k6 | Load & concurrency testing |
| Laravel Pint | Code formatting |
| Larastan | Static analysis level ≥ 6 [ASUMSI] |
| `flutter_test` | Widget test alur verifikasi admin |

---

## Aktor & RBAC

| Aktor | Kanal | Tanggung Jawab |
|---|---|---|
| **Calon Peserta / Mahasiswa** | Web | Registrasi, upload berkas, bayar, pantau status |
| **Admin SILAU** | Web + Mobile | Verifikasi kelengkapan berkas |
| **Admin Keuangan** | Web + Mobile | Verifikasi pembayaran |
| **Admin Track** | Web | Kelola track, peminatan, tarif semester |
| **Super Admin** | Web | Full access: konfigurasi, periode, rollover, audit |

**API v1 TIDAK membuka endpoint untuk peserta** — hanya Admin SILAU & Admin Keuangan.

---

## Struktur Folder Laravel

```
app/
├── Actions/
│   ├── Pendaftaran/     # SimpanDraf, AjukanFormulir, VerifikasiDokumen, ...
│   ├── Tagihan/         # TarifResolver, TerbitkanTagihan, VerifikasiPembayaran, TerbitkanNpd, ...
│   ├── Kuota/           # TempatkanKuota, PromosikanDaftarTunggu, ...
│   ├── Periode/         # TutupPeriode
│   ├── Arsip/           # UnduhArsipPeriode, BersihkanEkspor
│   ├── Refund/
│   └── Rollover/
├── Queries/             # KatalogQuery, DataPendaftaranQuery, RekapQuery
├── Services/            # KodeUnikGenerator, NpdGenerator, TarifResolver, AuditLogger, NikHasher
├── States/              # state machine per entitas
├── Models/
├── Policies/            # TagihanPolicy, PendaftaranPolicy, ArsipPeriodePolicy, ...
├── Jobs/                # KirimNotifikasi, EksporLaporan, BuatArsipPeriode, JalankanRollover
├── Console/Commands/
├── Http/
│   ├── Controllers/
│   │   ├── Web/{Peserta,Silau,Keuangan,Track,SuperAdmin,Admin}/
│   │   └── Api/V1/
│   ├── Requests/
│   └── Resources/V1/
└── Notifications/
```

---

## Business Rules Kritis

- **Uang**: `BIGINT UNSIGNED` dalam Rupiah (integer, bukan desimal)
- **Waktu**: Simpan UTC, tampilkan WIB
- **NIK & nomor rekening**: `encrypted` cast (AES-256) + `nik_hash` (HMAC-SHA256 key terpisah)
- **Nomor pendaftaran**: format `PV-{TAHUN}-{6 digit}`, penghitung dikunci `FOR UPDATE`
- **Kuota**: `DB::transaction + lockForUpdate()` pada baris kuota peminatan
- **Optimistic locking**: field `updated_at TIMESTAMP(6)` + cek status di `UPDATE` → jawab `409` jika konflik
- **Draf tidak visible admin**: Policy + scope `untukAdmin()` + VIEW `v_data_pendaftaran`
- **Audit log**: append-only + hash chain, akun `pv_app` hanya INSERT & SELECT
- **Arsip periode**: write-once, trigger DB menolak perubahan setelah `tersedia`
- **File ekspor**: dihapus otomatis setelah 24 jam (disk `ekspor` terpisah dari disk `arsip`)

---

## Koneksi Database (Multi-akun)

| Akun MySQL | Koneksi Laravel | Fungsi |
|---|---|---|
| `pv_app` | `mysql` (default) | Web, API, queue, scheduler. Tanpa DDL, tanpa DELETE tabel domain |
| `pv_report` | `mysql_report` | SELECT-only pada VIEW untuk dasbor & ekspor |
| `pv_migrator` | `mysql_migrator` | Migration saja, dikunci di luar deploy |
| `pv_backup` | — | mysqldump terjadwal |

Env tambahan: `NIK_HMAC_KEY`, `DB_MIGRATOR_USERNAME/PASSWORD`, `DB_REPORT_USERNAME/PASSWORD`, `FILESYSTEM_ARSIP_*`

---

## REST API v1 — Konvensi

- Base URL: `https://{domain}/api/v1`
- Auth: `Authorization: Bearer {token}` (Sanctum, diterbitkan setelah TOTP)
- Token mobile berlaku **12 jam**
- Format waktu: ISO 8601 UTC
- Format uang: integer Rupiah (`1500123`)
- Naming: kebab-case plural (`/verifikasi-berkas`)
- Setiap PUT/POST verifikasi menyertakan `versi` (nilai `updated_at`) → `409` jika konflik

### Endpoint Utama

**Auth**
- `POST /auth/login` → mengembalikan `challenge_token`
- `POST /auth/two-factor` → mengembalikan Sanctum token
- `POST /auth/logout`

**Admin SILAU — Verifikasi Berkas**
- `GET /verifikasi-berkas` — antrean (tanpa Draf)
- `GET /verifikasi-berkas/{id}` — detail + checklist dokumen
- `PUT /verifikasi-berkas/{id}/dokumen/{dok_id}` — keputusan per dokumen
- `POST /verifikasi-berkas/{id}/selesai` → Perlu Revisi / Masuk Kuota / Daftar Tunggu

**Admin Keuangan — Verifikasi Pembayaran**
- `GET /verifikasi-pembayaran` — antrean tagihan
- `GET /verifikasi-pembayaran/{kode_referensi}` — detail
- `POST /verifikasi-pembayaran/{kode_referensi}/lunas`
- `POST /verifikasi-pembayaran/{kode_referensi}/tolak`

---

## Scheduler Penting

| Perintah | Jadwal | Fungsi |
|---|---|---|
| `pendaftaran:batalkan-kedaluwarsa` | Setiap jam | Batalkan Masuk Kuota > 7 hari tanpa pelunasan |
| `periode:tutup` | Setiap 15 menit | Tutup periode, Daftar Tunggu → Tidak Tertampung, Draf → Dibatalkan |
| `tagihan:kirim-pengingat` | Harian 07.00 WIB | Pengingat H-7 & H-1 |
| `ekspor:bersihkan` | Setiap jam | Hapus file ekspor > 24 jam |
| `audit:verifikasi-hash` | Harian 02.00 WIB | Cek hash chain audit log |

---

## Lingkungan

| Lingkungan | Stack |
|---|---|
| **Local** | Laragon (Nginx, PHP 8.4, MySQL 9.7), Mailpit, queue driver `database` |
| **Staging** | Linux, Brevo, Redis |
| **Production** | Linux, SMTP Polibatam, Redis, HTTPS wajib |

Jalankan di lokal:
```bash
php artisan queue:work
php artisan schedule:work
npm run dev
```

---

## Cara Kerja (WAJIB dibaca di awal sesi)

Baca **`doc/PRD-CLAUDE-CODE.md`**: aturan emas, struktur folder, kontrak penyambungan FE–BE, alur kerja, dan pembagian paket (P00–P12, PM1–PM3, PN). Cek juga `doc/registry/progres.md` untuk status terakhir.

Prioritas bila dokumen bertentangan: Keputusan Klarifikasi Scope > SRS (FR/BR/NFR) > User Stories > ERD + paket SQL > Arsitektur & Keamanan DB > design-memory/screenshot (visual saja). Bila bingung atau ada konflik: catat di `doc/registry/temuan.md` dan tanya.

## Dokumen Lengkap (semua ada di folder `doc/`)

| Dokumen | Path |
|---|---|
| **PRD & Pembagian Tugas** | `doc/PRD-CLAUDE-CODE.md` |
| Deskripsi Singkat | `doc/deskripsi-singkat-projek.md` |
| Pedoman Microcredential | `doc/pedoman_program_microcredential_non_degree_2026.md` |
| Keputusan Klarifikasi Scope (K-01–K-41) | `doc/keputusan-klarifikasi-scope.md` |
| Pain Points | `doc/pain-points.md` |
| User Personas | `doc/user-personas.md` |
| User Stories | `doc/user-stories.md` |
| FR & BR (SRS) | `doc/srs-fr-br.md` |
| NFR (SRS) | `doc/srs-nfr.md` |
| ERD | `doc/erd.md` |
| Class Diagram | `doc/class-diagram.md` |
| Use Case | `doc/use-case.md` |
| Activity Diagram | `doc/activity-diagram.md` |
| Sequence Diagram | `doc/sequence-diagram.md` |
| Arsitektur Teknologi | `doc/arsitektur-teknologi.md` |
| Implementasi Basis Data | `doc/implementasi-basis-data.md` |
| Keamanan Basis Data | `doc/keamanan-basis-data.md` |
| Daftar Halaman Web (HW-01–56) | `doc/daftar-halaman-web.md` |
| Daftar Halaman Mobile (HM-01–37) | `doc/daftar-halaman-mobile.md` |
| Design System & UI | `doc/design-memory.md` |
| Review Desain Figma | `doc/review-desain-figma.md` |
| Gantt Chart | `doc/gantt-chart.md` |
| Paket SQL basis data | `database/paket-db/` |
