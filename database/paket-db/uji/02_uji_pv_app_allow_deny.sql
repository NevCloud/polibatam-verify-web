-- =====================================================================
-- Uji 02 — Allowed vs Forbidden untuk akun aplikasi pv_app
-- Jalankan    : mysql -h 127.0.0.1 -u pv_app -p --force polibatam_verify < uji/02_uji_pv_app_allow_deny.sql
--               (--force agar lanjut setelah ERROR yang memang diharapkan)
-- Checklist   : UTS-06, UAS-05 · TST-01..TST-09 (Dok. Keamanan §5.4)
-- =====================================================================

SET NAMES utf8mb4;
SELECT CURRENT_USER() AS akun_aktif;                       -- pv_app@127.0.0.1 (TST-48)

-- TST-01 ALLOW: baca tagihan
SELECT COUNT(*) AS tst01_jumlah_tagihan FROM tagihans;

-- TST-02 ALLOW: tulis audit log (append)
INSERT INTO audit_logs (user_id, role, aksi, hasil, entitas_type, entitas_id, kanal, prev_hash, hash)
VALUES (NULL, NULL, 'uji_akses_pv_app', 'berhasil', 'uji', NULL, 'sistem',
        REPEAT('0', 64), SHA2(CONCAT('uji_akses_pv_app', UUID()), 256));
SELECT ROW_COUNT() AS tst02_baris_masuk;

-- TST-03 DENY: ERROR 1142 UPDATE command denied ... table 'audit_logs'
UPDATE audit_logs SET aksi = 'x' WHERE id = 1;

-- TST-04 DENY: ERROR 1142 DELETE command denied ... table 'pendaftarans'
DELETE FROM pendaftarans WHERE id = 1;

-- TST-05 DENY: ERROR 1142 DROP command denied ... table 'tagihans'
DROP TABLE tagihans;

-- TST-06 DENY: ERROR 1142 SELECT command denied ... table 'user'
SELECT user, authentication_string FROM mysql.user;

-- TST-08 DENY: ERROR 1142 UPDATE command denied ... table 'tarifs'
UPDATE tarifs SET nominal = 1 WHERE id = 1;

-- TST-09 DENY: ERROR 1227 Access denied; you need ... CREATE USER
CREATE USER 'x'@'%' IDENTIFIED BY 'Xx-123456789!';

-- Tambahan DENY: DDL & file server
ALTER TABLE users ADD COLUMN coba INT;                            -- ERROR 1142 ALTER
SELECT * FROM tagihans INTO OUTFILE '/tmp/bocor.csv';             -- ERROR 1045/1227 (tanpa FILE)
DELETE FROM arsip_periodes WHERE id = 1;                          -- ERROR 1142 DELETE

-- Tambahan ALLOW: kunci penghitung nomor pendaftaran (butuh SELECT + UPDATE) untuk CC-01 (BR-68; kuota maks dihapus K-42)
START TRANSACTION;
SELECT nilai FROM pengaturans WHERE kunci = 'urut_nomor_pendaftaran_2026' FOR UPDATE;
ROLLBACK;

-- TST-07 (host restriction) dijalankan dari MESIN LAIN:
--   mysql -h <ip_db> -u pv_app -p
--   → ERROR 1130 Host '...' is not allowed to connect  /  timeout (firewall)
