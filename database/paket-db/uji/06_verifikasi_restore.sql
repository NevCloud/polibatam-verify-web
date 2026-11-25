-- =====================================================================
-- Uji 06 — Verifikasi hasil restore (polibatam_verify vs _restore)
-- Jalankan  : mysql -h 127.0.0.1 -u pv_dba -p < uji/06_verifikasi_restore.sql
-- Checklist : UTS-18, UAS-18 · TST-37..TST-39 (Dok. Keamanan §13.4)
-- Prasyarat : queue:work & schedule:work dihentikan sejak backup dibuat.
-- =====================================================================

-- TST-37: jumlah baris tabel kunci (asal = pulih)
SET NAMES utf8mb4;
SELECT 'users' AS tabel,
       (SELECT COUNT(*) FROM polibatam_verify.users)         AS asal,
       (SELECT COUNT(*) FROM polibatam_verify_restore.users) AS pulih
UNION ALL SELECT 'pendaftarans',
       (SELECT COUNT(*) FROM polibatam_verify.pendaftarans),
       (SELECT COUNT(*) FROM polibatam_verify_restore.pendaftarans)
UNION ALL SELECT 'tagihans',
       (SELECT COUNT(*) FROM polibatam_verify.tagihans),
       (SELECT COUNT(*) FROM polibatam_verify_restore.tagihans)
UNION ALL SELECT 'peserta_didiks',
       (SELECT COUNT(*) FROM polibatam_verify.peserta_didiks),
       (SELECT COUNT(*) FROM polibatam_verify_restore.peserta_didiks)
UNION ALL SELECT 'audit_logs',
       (SELECT COUNT(*) FROM polibatam_verify.audit_logs),
       (SELECT COUNT(*) FROM polibatam_verify_restore.audit_logs);

-- TST-38: checksum isi tabel harus sama
CHECKSUM TABLE polibatam_verify.tagihans,     polibatam_verify_restore.tagihans;
CHECKSUM TABLE polibatam_verify.pendaftarans, polibatam_verify_restore.pendaftarans;
CHECKSUM TABLE polibatam_verify.audit_logs,   polibatam_verify_restore.audit_logs;
CHECKSUM TABLE polibatam_verify.users,        polibatam_verify_restore.users;

-- TST-39: objek keamanan ikut terpulihkan (6 trigger, 4 VIEW)
SELECT COUNT(*) AS jumlah_trigger FROM information_schema.triggers
WHERE trigger_schema = 'polibatam_verify_restore';
SELECT COUNT(*) AS jumlah_view FROM information_schema.views
WHERE table_schema = 'polibatam_verify_restore';

-- VIEW & trigger di DB pulih berjalan dengan hak DEFINER pv_migrator, yang
-- hanya berhak atas polibatam_verify. Beri hak sementara (Dok. §13.3):
GRANT SELECT, TRIGGER ON polibatam_verify_restore.* TO 'pv_migrator'@'127.0.0.1';

-- Trigger aktif di DB pulih → ERROR 1644 audit_logs bersifat append-only
UPDATE polibatam_verify_restore.audit_logs SET aksi = 'x' ORDER BY id LIMIT 1;
-- VIEW di DB pulih terbaca
SELECT COUNT(*) AS view_pulih FROM polibatam_verify_restore.v_data_pendaftaran;

-- Cabut lagi setelah verifikasi
REVOKE SELECT, TRIGGER ON polibatam_verify_restore.* FROM 'pv_migrator'@'127.0.0.1';

-- Setelah selesai, kosongkan DB uji:
-- DROP DATABASE polibatam_verify_restore;
-- CREATE DATABASE polibatam_verify_restore CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
