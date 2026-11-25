-- =====================================================================
-- Polibatam Verify — 05. GRANT least privilege & penguncian migrator
-- Dijalankan oleh : pv_dba  (setelah 01–04, dan SETIAP kali migration
--                   menambah tabel/VIEW baru)
-- Ref             : Dokumen Keamanan Basis Data §4.2, §5.2, §7.2
--                   (UTS-05, UAS-05, DBC-02)
--
-- Prinsip:
--   - Hak per tabel, bukan polibatam_verify.*
--   - pv_app TANPA DELETE pada tabel domain, TANPA UPDATE pada riwayat
--   - pv_app TANPA DDL, FILE, PROCESS, SUPER, GRANT OPTION
--   - pv_report hanya SELECT pada VIEW
-- =====================================================================

-- Mulai dari nol agar skrip ini idempoten (aman dijalankan ulang).
-- Pada eksekusi pertama keduanya mungkin belum punya hak: abaikan
-- peringatan "There is no such grant".
SET NAMES utf8mb4;
REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'pv_app'@'127.0.0.1';
REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'pv_report'@'127.0.0.1';

-- ---------------------------------------------------------------------
-- pv_app
-- ---------------------------------------------------------------------

-- A. Tabel domain baca-tulis, tanpa DELETE (17 tabel)
GRANT SELECT, INSERT, UPDATE ON polibatam_verify.users                TO 'pv_app'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE ON polibatam_verify.tracks               TO 'pv_app'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE ON polibatam_verify.peminatans           TO 'pv_app'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE ON polibatam_verify.periode_pendaftarans TO 'pv_app'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE ON polibatam_verify.peminatan_periodes   TO 'pv_app'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE ON polibatam_verify.pendaftarans         TO 'pv_app'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE ON polibatam_verify.dokumens             TO 'pv_app'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE ON polibatam_verify.peserta_didiks       TO 'pv_app'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE ON polibatam_verify.periode_akademiks    TO 'pv_app'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE ON polibatam_verify.tagihans             TO 'pv_app'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE ON polibatam_verify.bukti_pembayarans    TO 'pv_app'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE ON polibatam_verify.refunds              TO 'pv_app'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE ON polibatam_verify.refund_items         TO 'pv_app'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE ON polibatam_verify.pengaturans          TO 'pv_app'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE ON polibatam_verify.konten_cms           TO 'pv_app'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE ON polibatam_verify.notifications        TO 'pv_app'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE ON polibatam_verify.arsip_periodes       TO 'pv_app'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE ON polibatam_verify.rekening_institusis  TO 'pv_app'@'127.0.0.1';  -- 01b (P01, T-16); tanpa DELETE (BR-70)

-- B. Riwayat append-only
GRANT SELECT, INSERT ON polibatam_verify.tarifs              TO 'pv_app'@'127.0.0.1';
GRANT SELECT, INSERT ON polibatam_verify.dokumen_versis      TO 'pv_app'@'127.0.0.1';
GRANT SELECT, INSERT ON polibatam_verify.verifikasi_dokumens TO 'pv_app'@'127.0.0.1';
GRANT SELECT, INSERT ON polibatam_verify.audit_logs          TO 'pv_app'@'127.0.0.1';

-- C. Referensi (diisi seeder oleh pv_migrator)
GRANT SELECT ON polibatam_verify.kriteria_checklists   TO 'pv_app'@'127.0.0.1';
GRANT SELECT ON polibatam_verify.roles                 TO 'pv_app'@'127.0.0.1';
GRANT SELECT ON polibatam_verify.permissions           TO 'pv_app'@'127.0.0.1';
GRANT SELECT ON polibatam_verify.role_has_permissions  TO 'pv_app'@'127.0.0.1';
GRANT SELECT ON polibatam_verify.model_has_permissions TO 'pv_app'@'127.0.0.1';

-- D. Perlu DELETE karena perilaku package
GRANT SELECT, INSERT, DELETE ON polibatam_verify.model_has_roles       TO 'pv_app'@'127.0.0.1';  -- syncRoles() (BR-06)
GRANT SELECT, INSERT, DELETE ON polibatam_verify.password_reset_tokens TO 'pv_app'@'127.0.0.1';  -- FR-05

-- E. Infrastruktur (session, cache, antrean, token)
GRANT SELECT, INSERT, UPDATE, DELETE ON polibatam_verify.sessions               TO 'pv_app'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE, DELETE ON polibatam_verify.cache                  TO 'pv_app'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE, DELETE ON polibatam_verify.cache_locks            TO 'pv_app'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE, DELETE ON polibatam_verify.jobs                   TO 'pv_app'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE, DELETE ON polibatam_verify.job_batches            TO 'pv_app'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE, DELETE ON polibatam_verify.failed_jobs            TO 'pv_app'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE, DELETE ON polibatam_verify.personal_access_tokens TO 'pv_app'@'127.0.0.1';

-- ---------------------------------------------------------------------
-- pv_report: hanya VIEW
-- ---------------------------------------------------------------------
GRANT SELECT ON polibatam_verify.v_antrean_verifikasi_berkas TO 'pv_report'@'127.0.0.1';
GRANT SELECT ON polibatam_verify.v_rekap_pembayaran          TO 'pv_report'@'127.0.0.1';
GRANT SELECT ON polibatam_verify.v_status_refund             TO 'pv_report'@'127.0.0.1';
GRANT SELECT ON polibatam_verify.v_data_pendaftaran          TO 'pv_report'@'127.0.0.1';

-- ---------------------------------------------------------------------
-- Kunci pv_migrator di luar jendela deploy (Dok. Keamanan §3.1).
-- Buka lagi saat deploy:  ALTER USER 'pv_migrator'@'127.0.0.1' ACCOUNT UNLOCK;
-- VIEW SQL SECURITY DEFINER tetap berjalan walau definer-nya dikunci.
-- ---------------------------------------------------------------------
ALTER USER 'pv_migrator'@'127.0.0.1' ACCOUNT LOCK;

-- Evidence SHOW GRANTS & daftar akun: jalankan uji/01_cek_akun_dan_grants.sql
-- sebagai root (pv_dba sengaja tidak punya akses ke skema mysql.*).
