-- =====================================================================
-- Polibatam Verify — 01b. Tabel rekening institusi (tambahan P01)
-- Dijalankan oleh : pv_migrator (lewat migration 2026_11_25_000004), SETELAH 01
-- Ref             : SRS §4.1, §4.7, BR-70, BR-71, FR-67, A-62, HW-56
-- Keputusan       : pemilik proyek 25-11-2026 (temuan T-16) — tabel ini belum ada
--                   di ERD/01 padahal dibutuhkan BR-70–72; dibuat di P01.
--
-- Satu baris = satu rekening untuk SATU jenis tagihan. Rekening yang sama
-- boleh aktif untuk formulir dan semester sekaligus (A-62) → dua baris.
-- Nomor rekening institusi bukan data pribadi → tidak dienkripsi (A-62).
-- Kaitan ke tagihans (FK + snapshot rekening, BR-71) dibuat di P05.
-- =====================================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE rekening_institusis (
  id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  jenis_tagihan  VARCHAR(20)     NOT NULL COMMENT 'formulir / semester',
  nama_bank      VARCHAR(100)    NOT NULL,
  nomor_rekening VARCHAR(30)     NOT NULL COMMENT 'Tidak dienkripsi (A-62)',
  atas_nama      VARCHAR(150)    NOT NULL,
  status         VARCHAR(20)     NOT NULL DEFAULT 'nonaktif' COMMENT 'nonaktif / aktif (SRS §4.7)',
  -- BR-70: hanya satu rekening Aktif per jenis tagihan (NULL untuk baris nonaktif tidak bentrok)
  kunci_aktif    VARCHAR(20) GENERATED ALWAYS AS (CASE WHEN status = 'aktif' THEN jenis_tagihan END) STORED,
  dibuat_oleh    BIGINT UNSIGNED NOT NULL,
  diubah_oleh    BIGINT UNSIGNED NULL,
  created_at     TIMESTAMP       NULL,
  updated_at     TIMESTAMP       NULL,
  PRIMARY KEY (id),
  UNIQUE KEY rekening_institusis_kunci_aktif_unique (kunci_aktif),
  KEY rekening_institusis_jenis_status_index (jenis_tagihan, status),
  CONSTRAINT rekening_institusis_dibuat_oleh_foreign FOREIGN KEY (dibuat_oleh) REFERENCES users (id),
  CONSTRAINT rekening_institusis_diubah_oleh_foreign FOREIGN KEY (diubah_oleh) REFERENCES users (id),
  CONSTRAINT chk_rekening_jenis  CHECK (jenis_tagihan IN ('formulir', 'semester')),
  CONSTRAINT chk_rekening_status CHECK (status IN ('nonaktif', 'aktif')),
  CONSTRAINT chk_rekening_nomor  CHECK (nomor_rekening REGEXP '^[0-9]{5,30}$')
) ENGINE=InnoDB;
