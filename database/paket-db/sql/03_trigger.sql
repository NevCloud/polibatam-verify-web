-- =====================================================================
-- Polibatam Verify — 03. Trigger integritas
-- Dijalankan oleh : pv_migrator
-- Ref             : Dokumen Keamanan Basis Data §12.4 (append-only),
--                   ERD §3.21 (arsip write-once), BR-41, BR-68
--
-- Catatan klien: file ini memakai DELIMITER, jadi jalankan dengan klien
-- `mysql` atau HeidiSQL (bukan DB::unprepared satu string).
-- =====================================================================

SET NAMES utf8mb4;
USE polibatam_verify;

DROP TRIGGER IF EXISTS trg_audit_logs_tolak_update;
DROP TRIGGER IF EXISTS trg_audit_logs_tolak_delete;
DROP TRIGGER IF EXISTS trg_arsip_periodes_kunci;
DROP TRIGGER IF EXISTS trg_arsip_periodes_tolak_delete;
DROP TRIGGER IF EXISTS trg_peserta_didiks_npd_tetap;
DROP TRIGGER IF EXISTS trg_pendaftarans_nomor_tetap;

DELIMITER $$

-- T1–T2. audit_logs append-only untuk SEMUA akun, termasuk pv_dba (TST-35)
CREATE TRIGGER trg_audit_logs_tolak_update
BEFORE UPDATE ON audit_logs
FOR EACH ROW
BEGIN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'audit_logs bersifat append-only';
END$$

CREATE TRIGGER trg_audit_logs_tolak_delete
BEFORE DELETE ON audit_logs
FOR EACH ROW
BEGIN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'audit_logs bersifat append-only';
END$$

-- T3. Arsip periode write-once: setelah 'tersedia', kolom file & status
--     tidak boleh berubah. Kolom diperiksa_pada & updated_at tetap boleh
--     diubah (pemeriksaan integritas bulanan, NFR-49).
CREATE TRIGGER trg_arsip_periodes_kunci
BEFORE UPDATE ON arsip_periodes
FOR EACH ROW
BEGIN
  IF OLD.status = 'tersedia' AND (
       NOT (NEW.status       <=> OLD.status)
    OR NOT (NEW.path         <=> OLD.path)
    OR NOT (NEW.nama_file    <=> OLD.nama_file)
    OR NOT (NEW.sha256       <=> OLD.sha256)
    OR NOT (NEW.ukuran_byte  <=> OLD.ukuran_byte)
    OR NOT (NEW.jumlah_baris <=> OLD.jumlah_baris)
    OR NOT (NEW.ringkasan    <=> OLD.ringkasan)
    OR NOT (NEW.dibuat_pada  <=> OLD.dibuat_pada)
    OR NOT (NEW.periode_pendaftaran_id <=> OLD.periode_pendaftaran_id)
  ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'arsip_periodes berstatus tersedia tidak dapat diubah';
  END IF;
END$$

-- T4. Arsip yang sudah tersedia tidak dapat dihapus (lapis kedua setelah
--     pv_app tidak punya DELETE)
CREATE TRIGGER trg_arsip_periodes_tolak_delete
BEFORE DELETE ON arsip_periodes
FOR EACH ROW
BEGIN
  IF OLD.status = 'tersedia' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'arsip_periodes berstatus tersedia tidak dapat dihapus';
  END IF;
END$$

-- T5. NPD tidak dapat diubah setelah terbit (BR-41)
CREATE TRIGGER trg_peserta_didiks_npd_tetap
BEFORE UPDATE ON peserta_didiks
FOR EACH ROW
BEGIN
  IF NOT (NEW.npd <=> OLD.npd) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'NPD tidak dapat diubah (BR-41)';
  END IF;
END$$

-- T6. Nomor pendaftaran tidak berubah setelah dibangkitkan (BR-68)
CREATE TRIGGER trg_pendaftarans_nomor_tetap
BEFORE UPDATE ON pendaftarans
FOR EACH ROW
BEGIN
  IF NOT (NEW.nomor_pendaftaran <=> OLD.nomor_pendaftaran) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Nomor pendaftaran tidak dapat diubah (BR-68)';
  END IF;
END$$

DELIMITER ;

SHOW TRIGGERS FROM polibatam_verify;
