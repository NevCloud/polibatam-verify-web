-- =====================================================================
-- Polibatam Verify — 07. Basis data & akun TES OTOMATIS (KHUSUS LOKAL)
-- Dijalankan oleh : root   (mysql -h 127.0.0.1 -u root -p < sql/07_akun_tes_lokal.sql)
-- Tujuan          : Pest memakai skema MySQL asli (CHECK, JSON, VIEW, trigger)
--                   di basis data terpisah, tanpa menyentuh polibatam_verify.
-- Catatan         : pv_test TIDAK punya hak apa pun pada polibatam_verify.
--                   Akun terpisah dari pv_migrator karena pv_migrator dikunci
--                   di luar jendela deploy (05_grants_dan_kunci.sql).
--                   Jangan dibuat di staging/produksi.
-- Ganti <RAHASIA_TEST> dengan nilai DB_TEST_PASSWORD di .env.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS polibatam_verify_test
  CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

CREATE USER IF NOT EXISTS 'pv_test'@'127.0.0.1'
  IDENTIFIED WITH caching_sha2_password BY '<RAHASIA_TEST>';

-- DDL + DML + VIEW + TRIGGER, hanya pada basis data tes
GRANT ALL PRIVILEGES ON polibatam_verify_test.* TO 'pv_test'@'127.0.0.1';

SELECT user, host, plugin FROM mysql.user WHERE user = 'pv_test';
