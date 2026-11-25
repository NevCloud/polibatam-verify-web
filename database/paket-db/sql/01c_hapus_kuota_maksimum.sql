-- =====================================================================
-- Polibatam Verify — 01c. Hapus kuota maksimum & Daftar Tunggu (P04)
-- Dijalankan oleh : pv_migrator (lewat migration 2026_11_26_000005), SETELAH 01, 01b, 02
-- Keputusan       : K-42 (pemilik proyek 26-11-2026) — tidak ada kuota maksimum,
--                   Daftar Tunggu & Tidak Tertampung ditiadakan; penghapusan kolom
--                   disetujui 26-11-2026 (temuan T-23).
-- Setelah berkas ini, migration menjalankan ulang 02_view.sql (v_data_pendaftaran
-- tidak lagi memuat nomor_urut_tunggu). GRANT tidak berubah (tidak ada tabel baru).
-- =====================================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- Kuota per peminatan per periode tinggal kuota minimum (BR-15, BR-23).
ALTER TABLE peminatan_periodes
  DROP CHECK chk_kuota_slot,
  DROP CHECK chk_kuota_rentang,
  DROP COLUMN slot_terpakai,
  DROP COLUMN kuota_maks;

-- Nomor urut Daftar Tunggu tidak dipakai lagi (BR-18 dihapus).
ALTER TABLE pendaftarans
  DROP COLUMN nomor_urut_tunggu;

-- Refund hanya karena peminatan dibatalkan (BR-47).
ALTER TABLE refunds
  MODIFY sebab VARCHAR(40) NOT NULL COMMENT 'peminatan_dibatalkan (BR-47, K-42)';
