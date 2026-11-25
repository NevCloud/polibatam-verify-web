-- =====================================================================
-- Polibatam Verify — 01d. Snapshot rekening tujuan & data pengiriman (P05)
-- Dijalankan oleh : pv_migrator (lewat migration 2026_11_26_000006), SETELAH 01b
-- Keputusan       : T-16 (25-11-2026) — kolom ini dikerjakan di P05; A-62 (26-11-2026).
-- Ref             : BR-71 (snapshot rekening saat tagihan terbit), BR-72 (data pengiriman wajib)
-- Kolom NULL untuk baris lama (data dummy sebelum P05); aplikasi mewajibkannya untuk data baru.
-- GRANT tidak berubah (tidak ada tabel baru).
-- =====================================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';

ALTER TABLE tagihans
  ADD COLUMN rekening_institusi_id BIGINT UNSIGNED NULL AFTER tarif_id,
  ADD COLUMN rekening_bank         VARCHAR(100)    NULL COMMENT 'Snapshot BR-71' AFTER rekening_institusi_id,
  ADD COLUMN rekening_nomor        VARCHAR(30)     NULL COMMENT 'Snapshot BR-71 (tidak dienkripsi, A-62)' AFTER rekening_bank,
  ADD COLUMN rekening_atas_nama    VARCHAR(150)    NULL COMMENT 'Snapshot BR-71' AFTER rekening_nomor,
  ADD CONSTRAINT tagihans_rekening_foreign FOREIGN KEY (rekening_institusi_id) REFERENCES rekening_institusis (id);

ALTER TABLE bukti_pembayarans
  ADD COLUMN tanggal_transfer DATE            NULL COMMENT 'BR-72, tidak boleh melebihi hari ini' AFTER diunggah_pada,
  ADD COLUMN nominal_transfer BIGINT UNSIGNED NULL COMMENT 'BR-72, Rupiah' AFTER tanggal_transfer,
  ADD COLUMN bank_pengirim    VARCHAR(100)    NULL COMMENT 'BR-72' AFTER nominal_transfer,
  ADD COLUMN nama_pengirim    VARCHAR(150)    NULL COMMENT 'BR-72' AFTER bank_pengirim;
