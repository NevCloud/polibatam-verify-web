-- =====================================================================
-- Polibatam Verify — 00. Provisioning basis data & akun MySQL
-- Dijalankan SEKALI oleh: root
-- Lingkungan : Laragon (Windows) — semua host akun = 127.0.0.1
-- Ref        : Dokumen Keamanan Basis Data §3.1–§3.3, §6 (BL-03, BL-04,
--              BL-05, BL-08, BL-09, BL-10, BL-22), DBC-01, DBC-03
--
-- SEBELUM MENJALANKAN:
--   Ganti setiap <RAHASIA_...> dengan password acak ≥ 24 karakter dari
--   password manager. Jangan menyimpan file yang sudah berisi password
--   ke repositori.
--
-- CATATAN LINUX (produksi):
--   - pv_dba & pv_backup  : ganti '127.0.0.1' menjadi 'localhost' (socket)
--   - pv_app, pv_report, pv_migrator : ganti '127.0.0.1' menjadi IP privat
--     server aplikasi (A-59), mis. '10.10.1.20'
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1. Kebijakan password (BL-09) — dipasang PALING AWAL agar semua
--    password akun di bawah langsung diuji kekuatannya.
--    Jika komponen sudah terpasang, baris INSTALL akan error 3529;
--    abaikan dan lanjutkan.
-- ---------------------------------------------------------------------
SET NAMES utf8mb4;
INSTALL COMPONENT 'file://component_validate_password';
SET PERSIST validate_password.policy               = 'MEDIUM';
SET PERSIST validate_password.length               = 12;
SET PERSIST validate_password.mixed_case_count     = 1;
SET PERSIST validate_password.number_count         = 1;
SET PERSIST validate_password.special_char_count   = 1;
SET PERSIST validate_password.check_user_name      = ON;

-- Binary log aktif (BL-20), sehingga akun tanpa SUPER (pv_migrator)
-- ditolak saat CREATE TRIGGER (ERROR 1419). Seluruh trigger proyek ini
-- deterministik (hanya SIGNAL), sehingga aman untuk replikasi/PITR.
SET PERSIST log_bin_trust_function_creators = ON;

-- ---------------------------------------------------------------------
-- 2. Amankan root (BL-03). Bawaan Laragon: root tanpa password.
-- ---------------------------------------------------------------------
ALTER USER 'root'@'localhost' IDENTIFIED BY '<RAHASIA_ROOT>';

-- Hapus akun anonim & basis data test bila ada (BL-04, BL-22)
DROP USER IF EXISTS ''@'localhost';
DROP USER IF EXISTS ''@'%';
DROP DATABASE IF EXISTS test;

-- ---------------------------------------------------------------------
-- 3. Basis data utama & basis data uji restore
-- ---------------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS polibatam_verify
  CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

CREATE DATABASE IF NOT EXISTS polibatam_verify_restore
  CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

-- ---------------------------------------------------------------------
-- 4. Akun
--    Plugin caching_sha2_password adalah bawaan MySQL 8+/9 (BL-08),
--    ditulis eksplisit sebagai evidence.
-- ---------------------------------------------------------------------

-- 4.1 DBA: administrasi skema, akun, restore (hanya dari mesin DB)
CREATE USER 'pv_dba'@'127.0.0.1'
  IDENTIFIED WITH caching_sha2_password BY '<RAHASIA_DBA>'
  PASSWORD HISTORY 5
  FAILED_LOGIN_ATTEMPTS 5 PASSWORD_LOCK_TIME 1;
GRANT ALL PRIVILEGES ON polibatam_verify.*         TO 'pv_dba'@'127.0.0.1' WITH GRANT OPTION;
GRANT ALL PRIVILEGES ON polibatam_verify_restore.* TO 'pv_dba'@'127.0.0.1' WITH GRANT OPTION;
-- SET_ANY_DEFINER: MySQL 8.2+ / 9.x (dibutuhkan saat restore VIEW & trigger
-- milik pv_migrator, §13.3). Pada MySQL 8.0/8.1 ganti dengan SET_USER_ID.
GRANT CREATE USER, SHOW DATABASES, SET_ANY_DEFINER ON *.* TO 'pv_dba'@'127.0.0.1';

-- 4.2 Migrator: DDL + DML pada skema aplikasi saja, tanpa GRANT OPTION
CREATE USER 'pv_migrator'@'127.0.0.1'
  IDENTIFIED WITH caching_sha2_password BY '<RAHASIA_MIGRATOR>'
  FAILED_LOGIN_ATTEMPTS 5 PASSWORD_LOCK_TIME 1;
GRANT ALL PRIVILEGES ON polibatam_verify.* TO 'pv_migrator'@'127.0.0.1';

-- 4.3 Aplikasi (Laravel web, API, queue, scheduler)
--     Hak per tabel diberikan di 04_grants.sql setelah tabel dibuat.
CREATE USER 'pv_app'@'127.0.0.1'
  IDENTIFIED WITH caching_sha2_password BY '<RAHASIA_APP>';

-- 4.4 Laporan: hanya SELECT pada VIEW (04_grants.sql)
CREATE USER 'pv_report'@'127.0.0.1'
  IDENTIFIED WITH caching_sha2_password BY '<RAHASIA_REPORT>';

-- 4.5 Backup: hanya-baca + objek yang perlu di-dump
CREATE USER 'pv_backup'@'127.0.0.1'
  IDENTIFIED WITH caching_sha2_password BY '<RAHASIA_BACKUP>';
GRANT SELECT, SHOW VIEW, TRIGGER, EVENT ON polibatam_verify.* TO 'pv_backup'@'127.0.0.1';

-- ---------------------------------------------------------------------
-- 5. Migrator TIDAK dikunci di sini karena 01–03 dijalankan olehnya.
--    Dikunci oleh pv_dba di akhir 05_grants_dan_kunci.sql
--    (ALTER USER 'pv_migrator'@'127.0.0.1' ACCOUNT LOCK;)
-- ---------------------------------------------------------------------

-- 6. Evidence singkat (UTS-03, UTS-07)
SELECT user, host, plugin, account_locked
FROM mysql.user
WHERE user LIKE 'pv\_%' OR user = 'root'
ORDER BY user;
