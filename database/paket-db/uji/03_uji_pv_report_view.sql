-- =====================================================================
-- Uji 03 — Pembatasan data melalui VIEW (akun pv_report)
-- Jalankan  : mysql -h 127.0.0.1 -u pv_report -p --force polibatam_verify < uji/03_uji_pv_report_view.sql
-- Checklist : UTS-09 · TST-10..TST-13 (TST-14 di uji/04 sebagai pv_dba)
-- =====================================================================

SET NAMES utf8mb4;
SELECT CURRENT_USER() AS akun_aktif;

-- TST-10 ALLOW: VIEW antrean (kolom aman saja)
SELECT * FROM v_antrean_verifikasi_berkas LIMIT 5;
SELECT * FROM v_data_pendaftaran LIMIT 5;           -- Draf (PV-2026-000043) tidak muncul
SELECT * FROM v_rekap_pembayaran LIMIT 5;
SELECT * FROM v_status_refund LIMIT 5;

-- TST-11 DENY: ERROR 1054 Unknown column 'nik_terenkripsi'
SELECT nik_terenkripsi FROM v_antrean_verifikasi_berkas;

-- TST-12 DENY: ERROR 1142 SELECT command denied ... table 'pendaftarans'
SELECT * FROM pendaftarans LIMIT 1;

-- TST-13 DENY: ERROR 1142 SELECT command denied ... table 'refunds'
SELECT no_rekening_terenkripsi FROM refunds;

-- Tambahan DENY: pv_report tidak bisa menulis
INSERT INTO tracks (kode, nama) VALUES ('X', 'X');  -- ERROR 1142 INSERT

-- Draf tidak pernah tampil di VIEW (BR-64) → 0
SELECT COUNT(*) AS draf_di_view FROM v_data_pendaftaran WHERE status = 'draf';
