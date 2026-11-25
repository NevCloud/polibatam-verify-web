-- =====================================================================
-- Uji 05 — COMMIT & ROLLBACK (TRX-02: verifikasi pembayaran + terbit NPD)
-- Jalankan  : mysql -h 127.0.0.1 -u pv_app -p --force polibatam_verify < uji/05_transaksi_commit_rollback.sql
-- Checklist : UTS-13, UTS-14 · TST-23, TST-24 (Dok. Keamanan §10.2)
-- Data awal : TG-UJI-0001 menunggu_verifikasi, pendaftaran 45 masuk_kuota,
--             NPD MC-TRPL-2026-0001 sudah dipakai pendaftaran 46.
-- PERHATIAN : bagian B melakukan COMMIT (mengubah data dummy). Untuk
--             mengulang, jalankan ulang 06_seed_dummy_lokal.sql pada DB
--             yang baru (lihat README "Reset data lokal").
-- =====================================================================

-- Kondisi awal
SET NAMES utf8mb4;
SELECT kode_referensi, status FROM tagihans WHERE kode_referensi = 'TG-UJI-0001';
SELECT id, status FROM pendaftarans WHERE id = 45;
SELECT COUNT(*) AS peserta_45 FROM peserta_didiks WHERE pendaftaran_id = 45;

-- ---------------------------------------------------------------------
-- A. TST-24 — Jalur gagal → ROLLBACK
-- ---------------------------------------------------------------------
START TRANSACTION;
UPDATE tagihans SET status = 'lunas', lunas_pada = UTC_TIMESTAMP(), updated_at = UTC_TIMESTAMP(6)
 WHERE kode_referensi = 'TG-UJI-0001' AND status = 'menunggu_verifikasi';      -- 1 row affected
UPDATE pendaftarans SET status = 'npd_terbit', updated_at = UTC_TIMESTAMP() WHERE id = 45;
INSERT INTO peserta_didiks (user_id, pendaftaran_id, npd, semester_berjalan, durasi_semester, status, npd_terbit_pada, created_at, updated_at)
VALUES (12, 45, 'MC-TRPL-2026-0001', 1, 5, 'belum_aktif', UTC_TIMESTAMP(), UTC_TIMESTAMP(), UTC_TIMESTAMP());
-- ERROR 1062 (23000): Duplicate entry 'MC-TRPL-2026-0001' for key 'peserta_didiks.peserta_didiks_npd_unique'
ROLLBACK;

-- Sesudah ROLLBACK: semua kembali
SELECT kode_referensi, status FROM tagihans WHERE kode_referensi = 'TG-UJI-0001';   -- menunggu_verifikasi
SELECT id, status FROM pendaftarans WHERE id = 45;                                   -- masuk_kuota
SELECT COUNT(*) AS peserta_45 FROM peserta_didiks WHERE pendaftaran_id = 45;         -- 0

-- ---------------------------------------------------------------------
-- B. TST-23 — Jalur sukses → COMMIT (NPD belum dipakai)
-- ---------------------------------------------------------------------
START TRANSACTION;
SELECT nilai FROM pengaturans WHERE kunci = 'urut_npd_2026' FOR UPDATE;          -- kunci penghitung (CC-07)
UPDATE pengaturans SET nilai = CAST('2' AS JSON), updated_at = UTC_TIMESTAMP() WHERE kunci = 'urut_npd_2026';
UPDATE tagihans SET status = 'lunas', lunas_pada = UTC_TIMESTAMP(), updated_at = UTC_TIMESTAMP(6)
 WHERE kode_referensi = 'TG-UJI-0001' AND status = 'menunggu_verifikasi';
UPDATE bukti_pembayarans SET keputusan = 'diterima', diverifikasi_oleh = 3, diverifikasi_pada = UTC_TIMESTAMP(), kanal = 'web'
 WHERE tagihan_id = (SELECT id FROM (SELECT id FROM tagihans WHERE kode_referensi = 'TG-UJI-0001') x)
   AND keputusan = 'menunggu';
UPDATE pendaftarans SET status = 'npd_terbit', updated_at = UTC_TIMESTAMP() WHERE id = 45;
INSERT INTO peserta_didiks (user_id, pendaftaran_id, npd, semester_berjalan, durasi_semester, status, npd_terbit_pada, created_at, updated_at)
VALUES (12, 45, 'MC-TRPL-2026-0002', 1, 5, 'belum_aktif', UTC_TIMESTAMP(), UTC_TIMESTAMP(), UTC_TIMESTAMP());
-- Role tetap `peserta` (5 role, T-07): status Peserta Didik = baris peserta_didiks di atas.
INSERT INTO audit_logs (user_id, role, aksi, hasil, entitas_type, entitas_id, kanal, prev_hash, hash)
VALUES (3, 'admin_keuangan', 'verifikasi_pembayaran', 'berhasil', 'tagihan', 1, 'web',
        REPEAT('0', 64), SHA2(CONCAT('verifikasi_pembayaran-1-', UUID()), 256));
COMMIT;

SELECT kode_referensi, status FROM tagihans WHERE kode_referensi = 'TG-UJI-0001';   -- lunas
SELECT id, status FROM pendaftarans WHERE id = 45;                                   -- npd_terbit
SELECT npd FROM peserta_didiks WHERE pendaftaran_id = 45;                            -- MC-TRPL-2026-0002
