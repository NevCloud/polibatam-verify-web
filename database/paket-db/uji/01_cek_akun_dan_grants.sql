-- =====================================================================
-- Uji 01 — Evidence akun, host restriction, GRANT & baseline
-- Dijalankan oleh : root   (butuh akses skema mysql.*)
-- Checklist       : UTS-03, UTS-05, UTS-07, UTS-08 · BL-01..BL-22
-- =====================================================================

-- [UTS-03/UTS-07] Daftar akun & host — tidak boleh ada host '%'
SET NAMES utf8mb4;
SELECT user, host, plugin, account_locked,
       User_attributes->>'$.Password_locking.failed_login_attempts' AS gagal_maks
FROM mysql.user
ORDER BY user, host;

-- [BL-04] Akun anonim → 0 baris
SELECT user, host FROM mysql.user WHERE user = '';

-- [BL-05] Akun host '%' → 0 baris
SELECT user, host FROM mysql.user WHERE host = '%';

-- [BL-03] root hanya dari localhost
SELECT user, host, plugin FROM mysql.user WHERE user = 'root';

-- Akun tanpa password → 0 baris
SELECT user, host FROM mysql.user
WHERE authentication_string = '' AND plugin NOT IN ('auth_socket');

-- [UTS-05] SHOW GRANTS per akun
SHOW GRANTS FOR 'pv_app'@'127.0.0.1';
SHOW GRANTS FOR 'pv_report'@'127.0.0.1';
SHOW GRANTS FOR 'pv_backup'@'127.0.0.1';
SHOW GRANTS FOR 'pv_migrator'@'127.0.0.1';
SHOW GRANTS FOR 'pv_dba'@'127.0.0.1';

-- [BL-13] Tidak ada hak global berbahaya untuk akun pv_*  → hanya USAGE,
-- plus CREATE USER/SHOW DATABASES/SET_*_DEFINER milik pv_dba
SELECT grantee, privilege_type
FROM information_schema.user_privileges
WHERE grantee LIKE '''pv\_%'
ORDER BY grantee, privilege_type;

-- Tabel yang belum punya hak untuk pv_app → seharusnya hanya 'migrations'
-- dan VIEW v_* (VIEW memang khusus pv_report)
SELECT t.table_name, t.table_type
FROM information_schema.tables t
LEFT JOIN (SELECT DISTINCT table_name FROM mysql.tables_priv
           WHERE Db = 'polibatam_verify' AND User = 'pv_app' AND Host = '127.0.0.1') p
       ON p.table_name = t.table_name
WHERE t.table_schema = 'polibatam_verify' AND p.table_name IS NULL
ORDER BY t.table_type, t.table_name;

-- [UTS-08] Baseline MySQL (bandingkan dengan tabel §6)
SELECT VERSION()                                AS bl01_versi,
       @@GLOBAL.bind_address                    AS bl06_bind_address,
       @@GLOBAL.local_infile                    AS bl11_local_infile,
       @@GLOBAL.secure_file_priv                AS bl12_secure_file_priv,
       @@GLOBAL.skip_name_resolve               AS bl14_skip_name_resolve,
       @@GLOBAL.sql_mode                        AS bl15_sql_mode,
       @@GLOBAL.default_storage_engine          AS bl16_engine,
       @@GLOBAL.require_secure_transport        AS bl17_tls_wajib,
       @@GLOBAL.log_error_verbosity             AS bl18_log_verbosity,
       @@GLOBAL.general_log                     AS bl19_general_log,
       @@GLOBAL.log_bin                         AS bl20_log_bin,
       @@GLOBAL.binlog_expire_logs_seconds      AS bl20_binlog_expire;

SHOW VARIABLES LIKE 'validate_password%';          -- BL-09
SHOW DATABASES;                                     -- BL-22: tidak ada 'test'
SHOW CREATE USER 'pv_dba'@'127.0.0.1';              -- BL-10
