-- =====================================================================
-- Polibatam Verify — 08. GRANT tabel Telescope (KHUSUS LOKAL)
-- Dijalankan oleh : pv_dba, SETELAH 05 dan setelah migration lokal
--                   (database/migrations/lokal) dijalankan.
-- Ref             : TABEL-DI-LUAR-ERD.md (root proyek), PRD BE-0 (Telescope lokal saja)
--
-- Tabel Telescope bukan tabel domain dan tidak ada di ERD. Telescope
-- menulis lewat koneksi aplikasi (pv_app) dan memangkas entri lama
-- (telescope:prune / clear), sehingga pv_app butuh DELETE di sini —
-- sama seperti tabel infrastruktur cache/sessions/jobs di 05.
-- JANGAN dijalankan di staging/produksi: tabelnya tidak ada di sana.
-- =====================================================================

SET NAMES utf8mb4;

GRANT SELECT, INSERT, UPDATE, DELETE ON polibatam_verify.telescope_entries      TO 'pv_app'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE, DELETE ON polibatam_verify.telescope_entries_tags TO 'pv_app'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE, DELETE ON polibatam_verify.telescope_monitoring   TO 'pv_app'@'127.0.0.1';
