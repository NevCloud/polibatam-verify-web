-- =====================================================================
-- Polibatam Verify — 01. Skema tabel
-- Dijalankan oleh : pv_migrator  (mysql -h 127.0.0.1 -u pv_migrator -p)
-- Ref             : ERD & Kamus Data §3–§4, NFR-29 (uang BIGINT),
--                   NFR-30 (UTC), K-36 (TIMESTAMP(6) untuk versi)
--
-- Konvensi:
--   - InnoDB, utf8mb4_0900_ai_ci (BL-16)
--   - Status = VARCHAR(30), dijaga state machine di aplikasi (NFR-28)
--   - Uang = BIGINT UNSIGNED Rupiah; waktu = TIMESTAMP (sesi UTC)
--   - FK default ON DELETE RESTRICT: data domain tidak pernah dihapus
-- =====================================================================

SET NAMES utf8mb4;
USE polibatam_verify;
SET time_zone = '+00:00';
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================================
-- A. TABEL FRAMEWORK & PACKAGE (Laravel 13, Sanctum, spatie/permission)
-- =====================================================================

CREATE TABLE migrations (
  id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  migration VARCHAR(255) NOT NULL,
  batch     INT          NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

-- 3.1 users -------------------------------------------------------------
CREATE TABLE users (
  id                        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name                      VARCHAR(150)    NOT NULL,
  email                     VARCHAR(191)    NOT NULL,
  email_verified_at         TIMESTAMP       NULL,
  password                  VARCHAR(255)    NOT NULL COMMENT 'Hash bcrypt cost 12 ($2y$12$...), NFR-02',
  is_active                 BOOLEAN         NOT NULL DEFAULT TRUE COMMENT 'FALSE = dinonaktifkan (FR-06)',
  two_factor_secret         TEXT            NULL COMMENT 'TOTP terenkripsi (Fortify); wajib untuk admin (K-33)',
  two_factor_recovery_codes TEXT            NULL,
  two_factor_confirmed_at   TIMESTAMP       NULL,
  privacy_consented_at      TIMESTAMP       NULL COMMENT 'NFR-11',
  remember_token            VARCHAR(100)    NULL,
  created_at                TIMESTAMP       NULL,
  updated_at                TIMESTAMP       NULL,
  PRIMARY KEY (id),
  UNIQUE KEY users_email_unique (email)                       -- BR-01
) ENGINE=InnoDB;

CREATE TABLE password_reset_tokens (
  email      VARCHAR(191) NOT NULL,
  token      VARCHAR(255) NOT NULL COMMENT 'Hash, bukan token asli',
  created_at TIMESTAMP    NULL,
  PRIMARY KEY (email)
) ENGINE=InnoDB;

CREATE TABLE sessions (
  id            VARCHAR(255)    NOT NULL,
  user_id       BIGINT UNSIGNED NULL,
  ip_address    VARCHAR(45)     NULL,
  user_agent    TEXT            NULL,
  payload       LONGTEXT        NOT NULL,
  last_activity INT             NOT NULL,
  PRIMARY KEY (id),
  KEY sessions_user_id_index (user_id),
  KEY sessions_last_activity_index (last_activity)
) ENGINE=InnoDB;

CREATE TABLE cache (
  `key`      VARCHAR(255) NOT NULL,
  value      MEDIUMTEXT   NOT NULL,
  expiration INT          NOT NULL,
  PRIMARY KEY (`key`),
  KEY cache_expiration_index (expiration)
) ENGINE=InnoDB;

CREATE TABLE cache_locks (
  `key`      VARCHAR(255) NOT NULL,
  owner      VARCHAR(255) NOT NULL,
  expiration INT          NOT NULL,
  PRIMARY KEY (`key`),
  KEY cache_locks_expiration_index (expiration)
) ENGINE=InnoDB;

CREATE TABLE jobs (
  id           BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  queue        VARCHAR(255)     NOT NULL,
  payload      LONGTEXT         NOT NULL,
  attempts     TINYINT UNSIGNED NOT NULL,
  reserved_at  INT UNSIGNED     NULL,
  available_at INT UNSIGNED     NOT NULL,
  created_at   INT UNSIGNED     NOT NULL,
  PRIMARY KEY (id),
  KEY jobs_queue_index (queue)
) ENGINE=InnoDB;

CREATE TABLE job_batches (
  id             VARCHAR(255) NOT NULL,
  name           VARCHAR(255) NOT NULL,
  total_jobs     INT          NOT NULL,
  pending_jobs   INT          NOT NULL,
  failed_jobs    INT          NOT NULL,
  failed_job_ids LONGTEXT     NOT NULL,
  options        MEDIUMTEXT   NULL,
  cancelled_at   INT          NULL,
  created_at     INT          NOT NULL,
  finished_at    INT          NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE failed_jobs (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  uuid       VARCHAR(255)    NOT NULL,
  connection TEXT            NOT NULL,
  queue      TEXT            NOT NULL,
  payload    LONGTEXT        NOT NULL,
  exception  LONGTEXT        NOT NULL,
  failed_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY failed_jobs_uuid_unique (uuid)
) ENGINE=InnoDB;

-- Sanctum: token mobile admin (12 jam, K-30). Kolom token = SHA-256.
CREATE TABLE personal_access_tokens (
  id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tokenable_type VARCHAR(255)    NOT NULL,
  tokenable_id   BIGINT UNSIGNED NOT NULL,
  name           TEXT            NOT NULL,
  token          VARCHAR(64)     NOT NULL,
  abilities      TEXT            NULL,
  last_used_at   TIMESTAMP       NULL,
  expires_at     TIMESTAMP       NULL,
  created_at     TIMESTAMP       NULL,
  updated_at     TIMESTAMP       NULL,
  PRIMARY KEY (id),
  UNIQUE KEY personal_access_tokens_token_unique (token),
  KEY personal_access_tokens_tokenable_index (tokenable_type, tokenable_id),
  KEY personal_access_tokens_expires_at_index (expires_at)
) ENGINE=InnoDB;

CREATE TABLE notifications (
  id              CHAR(36)        NOT NULL,
  type            VARCHAR(255)    NOT NULL,
  notifiable_type VARCHAR(255)    NOT NULL,
  notifiable_id   BIGINT UNSIGNED NOT NULL,
  data            TEXT            NOT NULL,
  read_at         TIMESTAMP       NULL,
  created_at      TIMESTAMP       NULL,
  updated_at      TIMESTAMP       NULL,
  PRIMARY KEY (id),
  KEY notifications_notifiable_index (notifiable_type, notifiable_id)
) ENGINE=InnoDB;

-- spatie/laravel-permission ------------------------------------------------
CREATE TABLE permissions (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name       VARCHAR(125)    NOT NULL,
  guard_name VARCHAR(125)    NOT NULL,
  created_at TIMESTAMP       NULL,
  updated_at TIMESTAMP       NULL,
  PRIMARY KEY (id),
  UNIQUE KEY permissions_name_guard_name_unique (name, guard_name)
) ENGINE=InnoDB;

CREATE TABLE roles (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name       VARCHAR(125)    NOT NULL,
  guard_name VARCHAR(125)    NOT NULL,
  created_at TIMESTAMP       NULL,
  updated_at TIMESTAMP       NULL,
  PRIMARY KEY (id),
  UNIQUE KEY roles_name_guard_name_unique (name, guard_name)
) ENGINE=InnoDB;

CREATE TABLE model_has_permissions (
  permission_id BIGINT UNSIGNED NOT NULL,
  model_type    VARCHAR(255)    NOT NULL,
  model_id      BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (permission_id, model_id, model_type),
  KEY model_has_permissions_model_id_model_type_index (model_id, model_type),
  CONSTRAINT model_has_permissions_permission_id_foreign
    FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE model_has_roles (
  role_id    BIGINT UNSIGNED NOT NULL,
  model_type VARCHAR(255)    NOT NULL,
  model_id   BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, model_id, model_type),
  KEY model_has_roles_model_id_model_type_index (model_id, model_type),
  CONSTRAINT model_has_roles_role_id_foreign
    FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE role_has_permissions (
  permission_id BIGINT UNSIGNED NOT NULL,
  role_id       BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (permission_id, role_id),
  CONSTRAINT role_has_permissions_permission_id_foreign
    FOREIGN KEY (permission_id) REFERENCES permissions (id) ON DELETE CASCADE,
  CONSTRAINT role_has_permissions_role_id_foreign
    FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- B. DATA MASTER
-- =====================================================================

-- 3.2 tracks
CREATE TABLE tracks (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  kode       VARCHAR(20)     NOT NULL,
  nama       VARCHAR(100)    NOT NULL,
  is_active  BOOLEAN         NOT NULL DEFAULT TRUE COMMENT 'Nonaktif, bukan dihapus (BR-53)',
  created_at TIMESTAMP       NULL,
  updated_at TIMESTAMP       NULL,
  PRIMARY KEY (id),
  UNIQUE KEY tracks_kode_unique (kode)                          -- BR-52
) ENGINE=InnoDB;

-- 3.3 peminatans
CREATE TABLE peminatans (
  id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  track_id        BIGINT UNSIGNED  NOT NULL,
  kode            VARCHAR(20)      NOT NULL COMMENT 'Dipakai di format NPD (BR-41)',
  nama            VARCHAR(150)     NOT NULL,
  durasi_semester TINYINT UNSIGNED NOT NULL,
  is_active       BOOLEAN          NOT NULL DEFAULT TRUE,
  created_at      TIMESTAMP        NULL,
  updated_at      TIMESTAMP        NULL,
  PRIMARY KEY (id),
  UNIQUE KEY peminatans_kode_unique (kode),
  KEY peminatans_track_id_index (track_id),
  CONSTRAINT peminatans_track_id_foreign
    FOREIGN KEY (track_id) REFERENCES tracks (id) ON DELETE RESTRICT,
  CONSTRAINT chk_peminatans_durasi CHECK (durasi_semester >= 1)  -- BR-52
) ENGINE=InnoDB;

-- 3.4 tarifs (riwayat append-only: perubahan = baris baru, BR-26)
CREATE TABLE tarifs (
  id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  peminatan_id    BIGINT UNSIGNED  NOT NULL,
  jenis           VARCHAR(20)      NOT NULL COMMENT 'formulir / semester (BR-25)',
  semester_ke     TINYINT UNSIGNED NULL COMMENT 'NULL untuk formulir; 1..D untuk semester',
  -- Kolom bantu agar UNIQUE juga berlaku untuk tarif formulir (semester_ke NULL)
  semester_kunci  TINYINT UNSIGNED GENERATED ALWAYS AS (IFNULL(semester_ke, 0)) STORED,
  nominal         BIGINT UNSIGNED  NOT NULL,
  tanggal_efektif DATE             NOT NULL COMMENT 'BR-26, BR-27',
  dibuat_oleh     BIGINT UNSIGNED  NOT NULL,
  created_at      TIMESTAMP        NULL,
  updated_at      TIMESTAMP        NULL,
  PRIMARY KEY (id),
  UNIQUE KEY tarifs_versi_unique (peminatan_id, jenis, semester_kunci, tanggal_efektif),
  KEY tarifs_dibuat_oleh_index (dibuat_oleh),
  CONSTRAINT tarifs_peminatan_id_foreign FOREIGN KEY (peminatan_id) REFERENCES peminatans (id),
  CONSTRAINT tarifs_dibuat_oleh_foreign  FOREIGN KEY (dibuat_oleh)  REFERENCES users (id),
  CONSTRAINT chk_tarifs_nominal CHECK (nominal > 0),
  CONSTRAINT chk_tarifs_jenis CHECK (
       (jenis = 'formulir' AND semester_ke IS NULL)
    OR (jenis = 'semester' AND semester_ke >= 1))
) ENGINE=InnoDB;

-- 3.11 kriteria_checklists
CREATE TABLE kriteria_checklists (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  jenis_dokumen VARCHAR(40)     NOT NULL,
  teks          VARCHAR(255)    NOT NULL,
  urutan        TINYINT         NOT NULL,
  is_active     BOOLEAN         NOT NULL DEFAULT TRUE,
  created_at    TIMESTAMP       NULL,
  updated_at    TIMESTAMP       NULL,
  PRIMARY KEY (id),
  KEY kriteria_checklists_jenis_index (jenis_dokumen, urutan)
) ENGINE=InnoDB;

-- =====================================================================
-- C. PERIODE & KUOTA
-- =====================================================================

-- 3.5 periode_pendaftarans
CREATE TABLE periode_pendaftarans (
  id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama            VARCHAR(100)    NOT NULL,
  tanggal_mulai   TIMESTAMP       NOT NULL,
  tanggal_selesai TIMESTAMP       NOT NULL,
  status          VARCHAR(30)     NOT NULL DEFAULT 'dijadwalkan' COMMENT 'dijadwalkan / dibuka / ditutup',
  ditutup_pada    TIMESTAMP       NULL COMMENT 'Diisi periode:tutup (FR-25)',
  created_at      TIMESTAMP       NULL,
  updated_at      TIMESTAMP       NULL,
  PRIMARY KEY (id),
  KEY periode_pendaftarans_rentang_index (tanggal_mulai, tanggal_selesai),
  CONSTRAINT chk_periode_pendaftarans_rentang CHECK (tanggal_selesai > tanggal_mulai)  -- BR-54
) ENGINE=InnoDB;

-- 3.6 peminatan_periodes (pusat kuota)
CREATE TABLE peminatan_periodes (
  id                     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  periode_pendaftaran_id BIGINT UNSIGNED NOT NULL,
  peminatan_id           BIGINT UNSIGNED NOT NULL,
  kuota_min              INT UNSIGNED    NOT NULL,
  kuota_maks             INT UNSIGNED    NOT NULL,
  slot_terpakai          INT UNSIGNED    NOT NULL DEFAULT 0 COMMENT 'Dikunci FOR UPDATE (NFR-27)',
  status                 VARCHAR(30)     NOT NULL DEFAULT 'berjalan' COMMENT 'berjalan / dibatalkan',
  dasar_pembatalan       VARCHAR(30)     NULL COMMENT 'kuota_minimum / kesepakatan (BR-24)',
  alasan_pembatalan      TEXT            NULL,
  dibatalkan_oleh        BIGINT UNSIGNED NULL,
  dibatalkan_pada        TIMESTAMP       NULL,
  created_at             TIMESTAMP       NULL,
  updated_at             TIMESTAMP       NULL,
  PRIMARY KEY (id),
  UNIQUE KEY peminatan_periodes_unique (periode_pendaftaran_id, peminatan_id),
  KEY peminatan_periodes_peminatan_index (peminatan_id),
  CONSTRAINT peminatan_periodes_periode_foreign   FOREIGN KEY (periode_pendaftaran_id) REFERENCES periode_pendaftarans (id),
  CONSTRAINT peminatan_periodes_peminatan_foreign FOREIGN KEY (peminatan_id)           REFERENCES peminatans (id),
  CONSTRAINT peminatan_periodes_dibatalkan_oleh_foreign FOREIGN KEY (dibatalkan_oleh)  REFERENCES users (id),
  CONSTRAINT chk_kuota_rentang CHECK (kuota_maks >= kuota_min),
  CONSTRAINT chk_kuota_slot    CHECK (slot_terpakai <= kuota_maks),   -- pengaman terakhir CC-01
  CONSTRAINT chk_pembatalan_kesepakatan CHECK (
       dasar_pembatalan IS NULL
    OR dasar_pembatalan = 'kuota_minimum'
    OR (dasar_pembatalan = 'kesepakatan' AND alasan_pembatalan IS NOT NULL))
) ENGINE=InnoDB;

-- 3.13 periode_akademiks
CREATE TABLE periode_akademiks (
  id                       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama                     VARCHAR(100)    NOT NULL,
  tanggal_mulai            DATE            NOT NULL COMMENT 'Jatuh tempo = tanggal_mulai - 7 hari (BR-35)',
  tanggal_selesai          DATE            NOT NULL,
  rollover_dijalankan_pada TIMESTAMP       NULL COMMENT 'Terisi = rollover tidak bisa diulang (BR-44)',
  rollover_dijalankan_oleh BIGINT UNSIGNED NULL,
  created_at               TIMESTAMP       NULL,
  updated_at               TIMESTAMP       NULL,
  PRIMARY KEY (id),
  CONSTRAINT periode_akademiks_rollover_oleh_foreign FOREIGN KEY (rollover_dijalankan_oleh) REFERENCES users (id),
  CONSTRAINT chk_periode_akademiks_rentang CHECK (tanggal_selesai > tanggal_mulai)
) ENGINE=InnoDB;

-- 3.21 arsip_periodes (permanen, write-once)
CREATE TABLE arsip_periodes (
  id                     BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  periode_pendaftaran_id BIGINT UNSIGNED  NOT NULL,
  status                 VARCHAR(20)      NOT NULL DEFAULT 'diproses' COMMENT 'diproses / tersedia / gagal',
  path                   VARCHAR(255)     NULL,
  nama_file              VARCHAR(150)     NULL,
  format                 VARCHAR(10)      NOT NULL DEFAULT 'xlsx',
  jumlah_baris           INT UNSIGNED     NULL,
  ringkasan              JSON             NULL,
  ukuran_byte            BIGINT UNSIGNED  NULL,
  sha256                 CHAR(64)         NULL,
  percobaan              TINYINT UNSIGNED NOT NULL DEFAULT 0,
  galat_terakhir         VARCHAR(255)     NULL,
  dibuat_pada            TIMESTAMP        NULL,
  diperiksa_pada         TIMESTAMP        NULL,
  created_at             TIMESTAMP        NULL,
  updated_at             TIMESTAMP        NULL,
  PRIMARY KEY (id),
  UNIQUE KEY arsip_periodes_periode_unique (periode_pendaftaran_id),   -- FR-65 idempoten
  CONSTRAINT arsip_periodes_periode_foreign FOREIGN KEY (periode_pendaftaran_id) REFERENCES periode_pendaftarans (id),
  CONSTRAINT chk_arsip_tersedia_lengkap CHECK (
       status <> 'tersedia'
    OR (path IS NOT NULL AND sha256 IS NOT NULL AND ukuran_byte IS NOT NULL
        AND jumlah_baris IS NOT NULL AND dibuat_pada IS NOT NULL))
) ENGINE=InnoDB;

-- =====================================================================
-- D. PENDAFTARAN & DOKUMEN
-- =====================================================================

-- 3.7 pendaftarans
CREATE TABLE pendaftarans (
  id                       BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  nomor_pendaftaran        VARCHAR(20)      NOT NULL COMMENT 'PV-{TAHUN}-{6 digit}, tidak berubah (BR-68)',
  user_id                  BIGINT UNSIGNED  NOT NULL,
  peminatan_periode_id     BIGINT UNSIGNED  NOT NULL COMMENT 'Terkunci setelah Diajukan (BR-09)',
  status                   VARCHAR(30)      NOT NULL DEFAULT 'draf',
  nama_lengkap             VARCHAR(150)     NULL,
  nik_terenkripsi          TEXT             NULL COMMENT 'AES-256 (encrypted cast, APP_KEY)',
  nik_hash                 CHAR(64)         NULL COMMENT 'HMAC-SHA256 dengan NIK_HMAC_KEY (K-36)',
  tempat_lahir             VARCHAR(100)     NULL,
  tanggal_lahir            DATE             NULL,
  jenis_kelamin            VARCHAR(10)      NULL,
  no_hp                    VARCHAR(20)      NULL,
  alamat                   TEXT             NULL,
  asal_sekolah             VARCHAR(150)     NULL,
  tahun_lulus              YEAR             NULL,
  waktu_pengajuan          TIMESTAMP        NULL COMMENT 'Dasar FIFO (BR-18); NULL selama Draf',
  waktu_masuk_kuota        TIMESTAMP        NULL COMMENT 'Dasar batas 7 hari (BR-21)',
  nomor_urut_tunggu        INT UNSIGNED     NULL,
  durasi_semester_snapshot TINYINT UNSIGNED NULL COMMENT 'BR-43',
  alasan_batal             VARCHAR(255)     NULL,
  created_at               TIMESTAMP        NULL,
  updated_at               TIMESTAMP        NULL,
  PRIMARY KEY (id),
  UNIQUE KEY pendaftarans_nomor_unique (nomor_pendaftaran),
  KEY pendaftarans_antrean_index (peminatan_periode_id, status, waktu_pengajuan),
  KEY pendaftarans_kedaluwarsa_index (status, waktu_masuk_kuota),
  KEY pendaftarans_user_index (user_id),
  KEY pendaftarans_nik_hash_index (nik_hash),
  CONSTRAINT pendaftarans_user_foreign FOREIGN KEY (user_id) REFERENCES users (id),
  CONSTRAINT pendaftarans_peminatan_periode_foreign FOREIGN KEY (peminatan_periode_id) REFERENCES peminatan_periodes (id),
  CONSTRAINT chk_pendaftarans_nomor CHECK (REGEXP_LIKE(nomor_pendaftaran, '^PV-[0-9]{4}-[0-9]{6}$', 'c'))
) ENGINE=InnoDB;

-- 3.8 dokumens (TIMESTAMP(6) = versi optimistic lock, K-36)
CREATE TABLE dokumens (
  id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  pendaftaran_id BIGINT UNSIGNED NOT NULL,
  jenis          VARCHAR(40)     NOT NULL COMMENT 'ijazah_skl_rapor / skhu_nilai / ktp / bukti_pendaftaran_polibatam',
  wajib          BOOLEAN         NOT NULL DEFAULT TRUE,
  status         VARCHAR(30)     NOT NULL DEFAULT 'menunggu_verifikasi',
  versi_aktif_id BIGINT UNSIGNED NULL,
  created_at     TIMESTAMP(6)    NULL,
  updated_at     TIMESTAMP(6)    NULL,
  PRIMARY KEY (id),
  UNIQUE KEY dokumens_pendaftaran_jenis_unique (pendaftaran_id, jenis),
  KEY dokumens_status_index (status),
  CONSTRAINT dokumens_pendaftaran_foreign FOREIGN KEY (pendaftaran_id) REFERENCES pendaftarans (id)
) ENGINE=InnoDB;

-- 3.9 dokumen_versis (riwayat append-only)
CREATE TABLE dokumen_versis (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  dokumen_id    BIGINT UNSIGNED NOT NULL,
  path          VARCHAR(255)    NOT NULL COMMENT 'Disk private, nama acak (NFR-07)',
  nama_asli     VARCHAR(255)    NOT NULL,
  mime          VARCHAR(50)     NOT NULL,
  ukuran        INT UNSIGNED    NOT NULL,
  sha256        CHAR(64)        NOT NULL,
  diunggah_pada TIMESTAMP       NOT NULL,
  created_at    TIMESTAMP       NULL,
  updated_at    TIMESTAMP       NULL,
  PRIMARY KEY (id),
  KEY dokumen_versis_dokumen_index (dokumen_id),
  CONSTRAINT dokumen_versis_dokumen_foreign FOREIGN KEY (dokumen_id) REFERENCES dokumens (id),
  CONSTRAINT chk_dokumen_versis_ukuran CHECK (ukuran BETWEEN 1 AND 2097152)   -- ≤ 2 MB (BR-12)
) ENGINE=InnoDB;

-- FK melingkar dokumens.versi_aktif_id → dokumen_versis
ALTER TABLE dokumens
  ADD CONSTRAINT dokumens_versi_aktif_foreign FOREIGN KEY (versi_aktif_id) REFERENCES dokumen_versis (id);

-- 3.10 verifikasi_dokumens (riwayat append-only)
CREATE TABLE verifikasi_dokumens (
  id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  dokumen_versi_id  BIGINT UNSIGNED NOT NULL,
  keputusan         VARCHAR(20)     NOT NULL COMMENT 'valid / ditolak',
  hasil_checklist   JSON            NOT NULL COMMENT '[{kriteria_id, terpenuhi}] (FR-20)',
  alasan            TEXT            NULL,
  diverifikasi_oleh BIGINT UNSIGNED NOT NULL,
  kanal             VARCHAR(10)     NOT NULL DEFAULT 'web' COMMENT 'web / mobile (K-29)',
  created_at        TIMESTAMP       NULL,
  updated_at        TIMESTAMP       NULL,
  PRIMARY KEY (id),
  KEY verifikasi_dokumens_versi_index (dokumen_versi_id),
  CONSTRAINT verifikasi_dokumens_versi_foreign FOREIGN KEY (dokumen_versi_id)  REFERENCES dokumen_versis (id),
  CONSTRAINT verifikasi_dokumens_oleh_foreign  FOREIGN KEY (diverifikasi_oleh) REFERENCES users (id),
  CONSTRAINT chk_verifikasi_alasan CHECK (keputusan <> 'ditolak' OR (alasan IS NOT NULL AND alasan <> ''))  -- BR-13
) ENGINE=InnoDB;

-- 3.12 peserta_didiks
CREATE TABLE peserta_didiks (
  id                BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  user_id           BIGINT UNSIGNED  NOT NULL,
  pendaftaran_id    BIGINT UNSIGNED  NOT NULL,
  npd               VARCHAR(30)      NOT NULL COMMENT 'Tidak dapat diubah (BR-41)',
  semester_berjalan TINYINT UNSIGNED NOT NULL DEFAULT 1,
  durasi_semester   TINYINT UNSIGNED NOT NULL,
  status            VARCHAR(30)      NOT NULL DEFAULT 'belum_aktif',
  npd_terbit_pada   TIMESTAMP        NOT NULL,
  created_at        TIMESTAMP        NULL,
  updated_at        TIMESTAMP        NULL,
  PRIMARY KEY (id),
  UNIQUE KEY peserta_didiks_npd_unique (npd),
  UNIQUE KEY peserta_didiks_pendaftaran_unique (pendaftaran_id),
  KEY peserta_didiks_user_index (user_id),
  CONSTRAINT peserta_didiks_user_foreign        FOREIGN KEY (user_id)        REFERENCES users (id),
  CONSTRAINT peserta_didiks_pendaftaran_foreign FOREIGN KEY (pendaftaran_id) REFERENCES pendaftarans (id),
  CONSTRAINT chk_peserta_semester CHECK (semester_berjalan BETWEEN 1 AND durasi_semester)
) ENGINE=InnoDB;

-- =====================================================================
-- E. TAGIHAN, PEMBAYARAN & REFUND
-- =====================================================================

-- 3.14 tagihans
CREATE TABLE tagihans (
  id                  BIGINT UNSIGNED   NOT NULL AUTO_INCREMENT,
  kode_referensi      VARCHAR(30)       NOT NULL COMMENT 'BR-31',
  user_id             BIGINT UNSIGNED   NOT NULL,
  pendaftaran_id      BIGINT UNSIGNED   NOT NULL,
  peserta_didik_id    BIGINT UNSIGNED   NULL COMMENT 'NULL untuk tagihan formulir',
  periode_akademik_id BIGINT UNSIGNED   NULL,
  tarif_id            BIGINT UNSIGNED   NOT NULL,
  jenis               VARCHAR(20)       NOT NULL COMMENT 'formulir / semester',
  semester_ke         TINYINT UNSIGNED  NULL,
  nominal_dasar       BIGINT UNSIGNED   NOT NULL COMMENT 'Snapshot tarif (BR-28)',
  kode_unik           SMALLINT UNSIGNED NOT NULL COMMENT '1–999 (BR-29)',
  nominal_total       BIGINT UNSIGNED   NOT NULL,
  jatuh_tempo         DATE              NULL COMMENT 'NULL = menunggu penetapan Super Admin (BR-35)',
  jatuh_tempo_manual  BOOLEAN           NOT NULL DEFAULT FALSE,
  status              VARCHAR(30)       NOT NULL DEFAULT 'belum_bayar',
  lunas_pada          TIMESTAMP         NULL,
  -- Kunci kode unik hanya untuk tagihan TERBUKA (BR-29)
  kunci_kode_unik     VARCHAR(40) GENERATED ALWAYS AS (
                        CASE WHEN status IN ('belum_bayar', 'menunggu_verifikasi', 'ditolak')
                             THEN CONCAT(nominal_dasar, '-', kode_unik) END) STORED,
  created_at          TIMESTAMP(6)      NULL,
  updated_at          TIMESTAMP(6)      NULL COMMENT 'Versi optimistic lock (K-36)',
  PRIMARY KEY (id),
  UNIQUE KEY tagihans_kode_referensi_unique (kode_referensi),
  UNIQUE KEY tagihans_kunci_kode_unik_unique (kunci_kode_unik),
  UNIQUE KEY tagihans_peserta_semester_unique (peserta_didik_id, semester_ke),   -- BR-33
  KEY tagihans_antrean_index (status, jenis),
  KEY tagihans_jatuh_tempo_index (jatuh_tempo, status),
  KEY tagihans_user_index (user_id),
  KEY tagihans_pendaftaran_index (pendaftaran_id),
  CONSTRAINT tagihans_user_foreign        FOREIGN KEY (user_id)             REFERENCES users (id),
  CONSTRAINT tagihans_pendaftaran_foreign FOREIGN KEY (pendaftaran_id)      REFERENCES pendaftarans (id),
  CONSTRAINT tagihans_peserta_foreign     FOREIGN KEY (peserta_didik_id)    REFERENCES peserta_didiks (id),
  CONSTRAINT tagihans_periode_foreign     FOREIGN KEY (periode_akademik_id) REFERENCES periode_akademiks (id),
  CONSTRAINT tagihans_tarif_foreign       FOREIGN KEY (tarif_id)            REFERENCES tarifs (id),
  CONSTRAINT chk_tagihans_kode_unik CHECK (kode_unik BETWEEN 1 AND 999),
  CONSTRAINT chk_tagihans_total     CHECK (nominal_total = nominal_dasar + kode_unik),
  CONSTRAINT chk_tagihans_jenis CHECK (
       (jenis = 'formulir' AND semester_ke IS NULL)
    OR (jenis = 'semester' AND semester_ke >= 1))
) ENGINE=InnoDB;

-- 3.15 bukti_pembayarans
CREATE TABLE bukti_pembayarans (
  id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  tagihan_id        BIGINT UNSIGNED NOT NULL,
  path              VARCHAR(255)    NOT NULL,
  mime              VARCHAR(50)     NOT NULL,
  ukuran            INT UNSIGNED    NOT NULL,
  sha256            CHAR(64)        NOT NULL,
  diunggah_pada     TIMESTAMP       NOT NULL,
  keputusan         VARCHAR(20)     NOT NULL DEFAULT 'menunggu' COMMENT 'menunggu / diterima / ditolak',
  alasan            TEXT            NULL,
  diverifikasi_oleh BIGINT UNSIGNED NULL,
  diverifikasi_pada TIMESTAMP       NULL,
  kanal             VARCHAR(10)     NULL,
  created_at        TIMESTAMP(6)    NULL,
  updated_at        TIMESTAMP(6)    NULL,
  PRIMARY KEY (id),
  UNIQUE KEY bukti_pembayarans_sha256_unique (sha256),          -- BR-37
  KEY bukti_pembayarans_tagihan_index (tagihan_id),
  CONSTRAINT bukti_pembayarans_tagihan_foreign FOREIGN KEY (tagihan_id)        REFERENCES tagihans (id),
  CONSTRAINT bukti_pembayarans_oleh_foreign    FOREIGN KEY (diverifikasi_oleh) REFERENCES users (id),
  CONSTRAINT chk_bukti_alasan CHECK (keputusan <> 'ditolak' OR (alasan IS NOT NULL AND alasan <> ''))  -- BR-38
) ENGINE=InnoDB;

-- 3.16 refunds
CREATE TABLE refunds (
  id                      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  pendaftaran_id          BIGINT UNSIGNED NOT NULL,
  user_id                 BIGINT UNSIGNED NOT NULL,
  sebab                   VARCHAR(40)     NOT NULL COMMENT 'peminatan_dibatalkan / tidak_tertampung (BR-47)',
  nominal                 BIGINT UNSIGNED NOT NULL DEFAULT 0,
  nama_bank               VARCHAR(100)    NULL,
  no_rekening_terenkripsi TEXT            NULL COMMENT 'AES-256 (BR-50)',
  nama_pemilik_rekening   VARCHAR(150)    NULL,
  status                  VARCHAR(30)     NOT NULL DEFAULT 'menunggu_data_rekening',
  alasan_ditolak          TEXT            NULL,
  bukti_transfer_path     VARCHAR(255)    NULL,
  diproses_oleh           BIGINT UNSIGNED NULL,
  selesai_pada            TIMESTAMP       NULL,
  created_at              TIMESTAMP       NULL,
  updated_at              TIMESTAMP       NULL,
  PRIMARY KEY (id),
  KEY refunds_pendaftaran_index (pendaftaran_id),
  KEY refunds_status_index (status),
  CONSTRAINT refunds_pendaftaran_foreign FOREIGN KEY (pendaftaran_id) REFERENCES pendaftarans (id),
  CONSTRAINT refunds_user_foreign        FOREIGN KEY (user_id)        REFERENCES users (id),
  CONSTRAINT refunds_diproses_foreign    FOREIGN KEY (diproses_oleh)  REFERENCES users (id),
  CONSTRAINT chk_refunds_ditolak CHECK (status <> 'ditolak' OR alasan_ditolak IS NOT NULL),       -- K-34
  CONSTRAINT chk_refunds_selesai CHECK (status <> 'selesai' OR bukti_transfer_path IS NOT NULL)
) ENGINE=InnoDB;

-- 3.17 refund_items
CREATE TABLE refund_items (
  id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  refund_id           BIGINT UNSIGNED NOT NULL,
  tagihan_id          BIGINT UNSIGNED NOT NULL,
  nominal_dasar       BIGINT UNSIGNED NOT NULL COMMENT 'Tanpa kode unik (BR-48)',
  disertakan          BOOLEAN         NOT NULL DEFAULT TRUE,
  alasan_dikecualikan VARCHAR(255)    NULL,
  dicocokkan_oleh     BIGINT UNSIGNED NULL,
  created_at          TIMESTAMP       NULL,
  updated_at          TIMESTAMP       NULL,
  PRIMARY KEY (id),
  UNIQUE KEY refund_items_tagihan_unique (tagihan_id),          -- CC-04: tidak ada refund ganda
  KEY refund_items_refund_index (refund_id),
  CONSTRAINT refund_items_refund_foreign  FOREIGN KEY (refund_id)       REFERENCES refunds (id),
  CONSTRAINT refund_items_tagihan_foreign FOREIGN KEY (tagihan_id)      REFERENCES tagihans (id),
  CONSTRAINT refund_items_oleh_foreign    FOREIGN KEY (dicocokkan_oleh) REFERENCES users (id)
) ENGINE=InnoDB;

-- =====================================================================
-- F. AUDIT, PENGATURAN & CMS
-- =====================================================================

-- 3.18 audit_logs (append-only, hash chain; tanpa updated_at)
CREATE TABLE audit_logs (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id       BIGINT UNSIGNED NULL COMMENT 'NULL = aksi sistem',
  role          VARCHAR(30)     NULL,
  aksi          VARCHAR(60)     NOT NULL,
  hasil         VARCHAR(10)     NOT NULL DEFAULT 'berhasil' COMMENT 'berhasil / gagal / ditolak (K-36)',
  entitas_type  VARCHAR(60)     NOT NULL,
  entitas_id    BIGINT UNSIGNED NULL,
  nilai_sebelum JSON            NULL,
  nilai_sesudah JSON            NULL,
  alasan        TEXT            NULL,
  ip_address    VARCHAR(45)     NULL,
  user_agent    VARCHAR(255)    NULL,
  kanal         VARCHAR(10)     NULL COMMENT 'web / mobile / sistem',
  prev_hash     CHAR(64)        NOT NULL,
  hash          CHAR(64)        NOT NULL,
  created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY audit_logs_hash_unique (hash),
  KEY audit_logs_aksi_hasil_index (aksi, hasil, created_at),
  KEY audit_logs_entitas_index (entitas_type, entitas_id),
  KEY audit_logs_user_index (user_id),
  CONSTRAINT audit_logs_user_foreign FOREIGN KEY (user_id) REFERENCES users (id),
  CONSTRAINT chk_audit_hasil CHECK (hasil IN ('berhasil', 'gagal', 'ditolak'))
) ENGINE=InnoDB;

-- 3.19 pengaturans
CREATE TABLE pengaturans (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  kunci       VARCHAR(80)     NOT NULL,
  nilai       JSON            NOT NULL,
  keterangan  VARCHAR(255)    NULL,
  diubah_oleh BIGINT UNSIGNED NULL,
  created_at  TIMESTAMP       NULL,
  updated_at  TIMESTAMP       NULL,
  PRIMARY KEY (id),
  UNIQUE KEY pengaturans_kunci_unique (kunci),
  CONSTRAINT pengaturans_diubah_oleh_foreign FOREIGN KEY (diubah_oleh) REFERENCES users (id)
) ENGINE=InnoDB;

-- 3.20 konten_cms
CREATE TABLE konten_cms (
  id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  bagian           VARCHAR(40)     NOT NULL COMMENT 'jadwal / pengumuman / alur / kontak',
  judul            VARCHAR(200)    NOT NULL,
  isi              LONGTEXT        NOT NULL,
  status           VARCHAR(20)     NOT NULL DEFAULT 'draf' COMMENT 'draf / terbit (FR-10)',
  diterbitkan_pada TIMESTAMP       NULL,
  diubah_oleh      BIGINT UNSIGNED NOT NULL,
  created_at       TIMESTAMP       NULL,
  updated_at       TIMESTAMP       NULL,
  PRIMARY KEY (id),
  KEY konten_cms_bagian_status_index (bagian, status),
  CONSTRAINT konten_cms_diubah_oleh_foreign FOREIGN KEY (diubah_oleh) REFERENCES users (id)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- Evidence BL-16: semua tabel InnoDB
SELECT table_name, engine, table_collation
FROM information_schema.tables
WHERE table_schema = 'polibatam_verify' AND table_type = 'BASE TABLE'
ORDER BY table_name;
