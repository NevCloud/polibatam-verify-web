-- =====================================================================
-- Uji 04 — Integritas data, trigger & hashing (akun pv_dba)
-- Jalankan  : mysql -h 127.0.0.1 -u pv_dba -p --force polibatam_verify < uji/04_uji_integritas_dba.sql
-- Checklist : UTS-09 (TST-14), UTS-10/11 (TST-15..17), UTS-16 (TST-35),
--             UTS-15 (CC-01, CC-03, CC-04 pengaman tingkat DB)
-- Semua perubahan dibungkus transaksi lalu ROLLBACK agar data dummy utuh.
-- =====================================================================

-- TST-14: jumlah baris VIEW = jumlah baris tabel dengan filter yang sama
SET NAMES utf8mb4;
SELECT (SELECT COUNT(*) FROM v_antrean_verifikasi_berkas)                AS view_antrean,
       (SELECT COUNT(*) FROM pendaftarans WHERE status = 'diajukan')      AS tabel_diajukan,
       (SELECT COUNT(*) FROM v_data_pendaftaran)                         AS view_data,
       (SELECT COUNT(*) FROM pendaftarans WHERE status <> 'draf')         AS tabel_non_draf;

-- TST-15: bentuk hash tersanitasi ($2y$12$, panjang 60)
SELECT id,
       CONCAT(LEFT(email, 3), '***') AS email_tersamar,
       LEFT(password, 7)             AS algoritma_dan_cost,
       CHAR_LENGTH(password)         AS panjang
FROM users LIMIT 5;

-- TST-16: dua akun dengan password SAMA → hash berbeda (salting) → 0
SELECT a.email, b.email, a.password = b.password AS hash_identik
FROM users a JOIN users b
  ON a.email = 'uji1@contoh.test' AND b.email = 'uji2@contoh.test';

-- TST-17: tidak ada password non-bcrypt → 0
SELECT COUNT(*) AS bukan_bcrypt FROM users
WHERE password NOT LIKE '$2y$%' OR CHAR_LENGTH(password) <> 60;

-- TST-35: audit_logs append-only, bahkan untuk pv_dba
--   → ERROR 1644 (45000): audit_logs bersifat append-only
UPDATE audit_logs SET aksi = 'x' ORDER BY id LIMIT 1;
DELETE FROM audit_logs ORDER BY id LIMIT 1;

-- BR-41: NPD tidak dapat diubah → ERROR 1644
UPDATE peserta_didiks SET npd = 'MC-TRPL-2026-9999' WHERE id = 1;

-- BR-68: nomor pendaftaran tidak dapat diubah → ERROR 1644
UPDATE pendaftarans SET nomor_pendaftaran = 'PV-2026-999999' WHERE id = 44;

-- CHECK pengaman terakhir: tarif harus > 0 → ERROR 3819 (chk_tarifs_nominal; chk_kuota_slot dihapus K-42)
UPDATE tarifs SET nominal = 0 WHERE id = 1;

-- CC-03 / BR-29: kode unik kembar pada tagihan terbuka → ERROR 1062
START TRANSACTION;
INSERT INTO tagihans (kode_referensi, user_id, pendaftaran_id, tarif_id, jenis, nominal_dasar, kode_unik, nominal_total, status, created_at, updated_at)
SELECT 'TG-UJI-DUP', 15, 44, tarif_id, 'formulir', 300000, 123, 300123, 'belum_bayar', UTC_TIMESTAMP(6), UTC_TIMESTAMP(6)
FROM tagihans WHERE kode_referensi = 'TG-UJI-0001';
ROLLBACK;

-- CC-03 sisi lain: kode unik sama BOLEH dipakai lagi bila tagihan lama sudah lunas → berhasil
START TRANSACTION;
INSERT INTO tagihans (kode_referensi, user_id, pendaftaran_id, tarif_id, jenis, nominal_dasar, kode_unik, nominal_total, status, created_at, updated_at)
SELECT 'TG-UJI-REUSE', 15, 44, tarif_id, 'formulir', 300000, 45, 300045, 'belum_bayar', UTC_TIMESTAMP(6), UTC_TIMESTAMP(6)
FROM tagihans WHERE kode_referensi = 'TG-UJI-0002';
SELECT ROW_COUNT() AS kode_unik_reuse_ok;
ROLLBACK;

-- CHECK nominal_total = nominal_dasar + kode_unik → ERROR 3819
START TRANSACTION;
INSERT INTO tagihans (kode_referensi, user_id, pendaftaran_id, tarif_id, jenis, nominal_dasar, kode_unik, nominal_total, status)
SELECT 'TG-UJI-SALAH', 15, 44, tarif_id, 'formulir', 300000, 500, 1, 'belum_bayar'
FROM tagihans WHERE kode_referensi = 'TG-UJI-0001';
ROLLBACK;

-- CC-04: tagihan yang sama tidak bisa masuk dua refund → ERROR 1062
START TRANSACTION;
INSERT INTO refunds (id, pendaftaran_id, user_id, sebab, created_at, updated_at)
VALUES (900, 46, 13, 'peminatan_dibatalkan', UTC_TIMESTAMP(), UTC_TIMESTAMP()),
       (901, 46, 13, 'peminatan_dibatalkan', UTC_TIMESTAMP(), UTC_TIMESTAMP());
INSERT INTO refund_items (refund_id, tagihan_id, nominal_dasar) VALUES (900, 2, 300000);
INSERT INTO refund_items (refund_id, tagihan_id, nominal_dasar) VALUES (901, 2, 300000);
ROLLBACK;

-- BR-13: verifikasi Ditolak tanpa alasan → ERROR 3819
START TRANSACTION;
INSERT INTO verifikasi_dokumens (dokumen_versi_id, keputusan, hasil_checklist, alasan, diverifikasi_oleh)
VALUES (3, 'ditolak', JSON_ARRAY(), NULL, 2);
ROLLBACK;

-- Arsip write-once: setelah 'tersedia', path tidak boleh berubah → ERROR 1644
START TRANSACTION;
INSERT INTO arsip_periodes (id, periode_pendaftaran_id, status, path, nama_file, jumlah_baris, ukuran_byte, sha256, dibuat_pada)
VALUES (900, 1, 'tersedia', 'arsip/periode-1.xlsx', 'arsip-periode-1.xlsx', 3, 10240, SHA2('arsip', 256), UTC_TIMESTAMP());
UPDATE arsip_periodes SET diperiksa_pada = UTC_TIMESTAMP() WHERE id = 900;   -- boleh
SELECT ROW_COUNT() AS pemeriksaan_integritas_boleh;
UPDATE arsip_periodes SET path = 'arsip/palsu.xlsx' WHERE id = 900;           -- ERROR 1644
DELETE FROM arsip_periodes WHERE id = 900;                                     -- ERROR 1644
ROLLBACK;

-- Ringkasan objek keamanan (TST-39 di DB asli)
SHOW TRIGGERS;
SHOW FULL TABLES WHERE Table_type = 'VIEW';
