-- =====================================================================
-- Polibatam Verify — 06. Data dummy KHUSUS LOKAL (Laragon)
-- Dijalankan oleh : pv_dba
-- JANGAN dijalankan di staging/produksi.
--
-- Tujuan: menyediakan data untuk skenario uji Dokumen Keamanan Basis
-- Data (TST-01..TST-42), termasuk tagihan TG-UJI-0001, pendaftaran 45,
-- NPD MC-TRPL-2026-0001, kuota id 3 = 29/30, dan akun uji1/uji2.
--
-- Semua akun memakai password lokal: Uji12345!
-- (hash bcrypt cost 12, salt berbeda per akun → bukti TST-16)
-- Akun admin belum punya TOTP; Fortify meminta pengaturan 2FA saat
-- login pertama (K-33).
-- NIK & no. rekening dibiarkan NULL karena ciphertext harus dibuat
-- aplikasi dengan APP_KEY (isi lewat aplikasi/tinker).
-- =====================================================================

SET NAMES utf8mb4;
USE polibatam_verify;
SET time_zone = '+00:00';
SET @now = UTC_TIMESTAMP();
SET @now6 = UTC_TIMESTAMP(6);
SET @model_user = 'App\\Models\\User';

-- 1. Akun aplikasi --------------------------------------------------------
INSERT INTO users (id, name, email, email_verified_at, password, is_active,
                   privacy_consented_at, created_at, updated_at) VALUES
  (1, 'Super Admin Lokal', 'superadmin@polibatam-verify.test', @now, '$2y$12$jZKtaD.zIEVGgaSUGmGKFOtV/9tp8zTGBOUnoWb86QtN9b/2MpX0a', TRUE, @now, @now, @now),
  (2, 'Admin SILAU Lokal', 'silau@polibatam-verify.test', @now, '$2y$12$/L3CGVkACdE/84NYpFiSVu2AujU1BD2QN7nI10WJoVdVMcxQJPUS6', TRUE, @now, @now, @now),
  (3, 'Admin Keuangan Lokal', 'keuangan@polibatam-verify.test', @now, '$2y$12$NvrtYyYJLjsOKB.vvFMmm.R/UbH6hfEfeOCpcXfcTRIqKD7Aczqye', TRUE, @now, @now, @now),
  (4, 'Admin Track Lokal', 'track@polibatam-verify.test', @now, '$2y$12$Gu.DxcSb.hcgIjYnt2xXyuV5LPlBVIWEQDGDs1Rrw2.Li8kOOdfki', TRUE, @now, @now, @now),
  (10, 'Akun Uji Salting 1', 'uji1@contoh.test', @now, '$2y$12$rlV/sN0B0XStVBzLlGgRROguuIjeTRZ4xpBI4rftej0/0iwxZ.6Y2', TRUE, @now, @now, @now),
  (11, 'Akun Uji Salting 2', 'uji2@contoh.test', @now, '$2y$12$sboHiXelYn.iA/idYrVybO8iZo9FPOqn18AWFfuid/dwycGCKQnOC', TRUE, @now, @now, @now),
  (12, 'Budi Santoso', 'peserta1@contoh.test', @now, '$2y$12$n1.ptATLtfEOApIhU6YpW.Z.nkBmdsJHZZxP72gUEYaKlMIV2ZdpG', TRUE, @now, @now, @now),
  (13, 'Siti Rahma', 'peserta2@contoh.test', @now, '$2y$12$M8ddFbJvP8czLvdkMfV5Q.PaRlD6ng5.KSPArM3BlyXraEVJRHKNi', TRUE, @now, @now, @now),
  (14, 'Andi Pratama', 'peserta3@contoh.test', @now, '$2y$12$4ryB9JKiubb9cvciuHFobOziM4obb9wcu7e.7n6dQKfC.U/vTOTlO', TRUE, @now, @now, @now),
  (15, 'Dewi Lestari', 'peserta4@contoh.test', @now, '$2y$12$mrs.fLppcItZ28VYGYqriORyiLyaOEjWOL4C60Pih/at8kK8eV9Xe', TRUE, @now, @now, @now);

INSERT INTO model_has_roles (role_id, model_type, model_id)
SELECT r.id, @model_user, m.uid
FROM (
            SELECT 1 AS uid, 'super_admin' AS role_name
  UNION ALL SELECT 2, 'admin_silau'
  UNION ALL SELECT 3, 'admin_keuangan'
  UNION ALL SELECT 4, 'admin_track'
  UNION ALL SELECT 10, 'peserta'
  UNION ALL SELECT 11, 'peserta'
  UNION ALL SELECT 12, 'peserta'
  UNION ALL SELECT 13, 'peserta'
  UNION ALL SELECT 14, 'peserta'
  UNION ALL SELECT 15, 'peserta'
) m
JOIN roles r ON r.name = m.role_name AND r.guard_name = 'web';

-- 2. Tarif (berversi; dibuat oleh Admin Track id 4) ---------------------------
INSERT INTO tarifs (peminatan_id, jenis, semester_ke, nominal, tanggal_efektif, dibuat_oleh, created_at, updated_at)
SELECT pm.id, x.jenis, x.smt, x.nominal, '2026-01-01', 4, @now, @now
FROM (
            SELECT 'ADB'  AS kode, 'formulir' AS jenis, NULL AS smt, 300000 AS nominal
  UNION ALL SELECT 'ADB',  'semester', 1, 5500000 UNION ALL SELECT 'ADB',  'semester', 2, 5500000
  UNION ALL SELECT 'ADB',  'semester', 3, 5500000 UNION ALL SELECT 'ADB',  'semester', 4, 5500000
  UNION ALL SELECT 'TRMM', 'formulir', NULL, 300000
  UNION ALL SELECT 'TRMM', 'semester', 1, 6500000 UNION ALL SELECT 'TRMM', 'semester', 2, 6500000
  UNION ALL SELECT 'TRMM', 'semester', 3, 6500000 UNION ALL SELECT 'TRMM', 'semester', 4, 6500000
  UNION ALL SELECT 'TRMM', 'semester', 5, 6500000
  UNION ALL SELECT 'TRPL', 'formulir', NULL, 300000
  UNION ALL SELECT 'TRPL', 'semester', 1, 6500000 UNION ALL SELECT 'TRPL', 'semester', 2, 6500000
  UNION ALL SELECT 'TRPL', 'semester', 3, 6500000 UNION ALL SELECT 'TRPL', 'semester', 4, 6500000
  UNION ALL SELECT 'TRPL', 'semester', 5, 6500000
) x
JOIN peminatans pm ON pm.kode = x.kode;

-- 3. Periode -------------------------------------------------------------------
INSERT INTO periode_pendaftarans (id, nama, tanggal_mulai, tanggal_selesai, status, created_at, updated_at)
VALUES (1, 'Periode Ganjil 2027', '2026-11-01 00:00:00', '2027-01-31 16:59:59', 'dibuka', @now, @now);

INSERT INTO periode_akademiks (id, nama, tanggal_mulai, tanggal_selesai, created_at, updated_at)
VALUES (1, 'Semester Ganjil 2027/2028', '2027-02-15', '2027-07-31', @now, @now);

-- Kuota minimum per peminatan (tanpa kuota maksimum, K-42)
INSERT INTO peminatan_periodes (id, periode_pendaftaran_id, peminatan_id, kuota_min, status, created_at, updated_at)
SELECT x.id, 1, pm.id, x.kmin, 'berjalan', @now, @now
FROM (
            SELECT 1 AS id, 'ADB'  AS kode, 10 AS kmin
  UNION ALL SELECT 2,       'TRMM',         10
  UNION ALL SELECT 3,       'TRPL',         10
) x
JOIN peminatans pm ON pm.kode = x.kode;

-- 4. Pendaftaran -------------------------------------------------------------
INSERT INTO pendaftarans (id, nomor_pendaftaran, user_id, peminatan_periode_id, status,
    nama_lengkap, tempat_lahir, tanggal_lahir, jenis_kelamin, no_hp, alamat, asal_sekolah, tahun_lulus,
    waktu_pengajuan, waktu_masuk_kuota, durasi_semester_snapshot, created_at, updated_at) VALUES
  (43, 'PV-2026-000043', 14, 3, 'draf',
      'Andi Pratama', 'Batam', '2005-03-10', 'L', '081200000043', 'Jl. Contoh No. 43, Batam', 'SMKN 1 Batam', 2023,
      NULL, NULL, NULL, @now, @now),
  (44, 'PV-2026-000044', 15, 3, 'diajukan',
      'Dewi Lestari', 'Batam', '2004-07-21', 'P', '081200000044', 'Jl. Contoh No. 44, Batam', 'SMAN 3 Batam', 2022,
      @now - INTERVAL 2 DAY, NULL, 5, @now, @now),
  (45, 'PV-2026-000045', 12, 3, 'masuk_kuota',
      'Budi Santoso', 'Tanjungpinang', '2004-01-05', 'L', '081200000045', 'Jl. Contoh No. 45, Batam', 'SMKN 2 Batam', 2022,
      @now - INTERVAL 5 DAY, @now - INTERVAL 1 DAY, 5, @now, @now),
  (46, 'PV-2026-000046', 13, 3, 'npd_terbit',
      'Siti Rahma', 'Batam', '2003-09-14', 'P', '081200000046', 'Jl. Contoh No. 46, Batam', 'SMAN 1 Batam', 2021,
      @now - INTERVAL 9 DAY, @now - INTERVAL 7 DAY, 5, @now, @now);

-- 5. Dokumen pendaftaran 44 (menunggu verifikasi) ---------------------------
INSERT INTO dokumens (id, pendaftaran_id, jenis, wajib, status, created_at, updated_at) VALUES
  (1, 44, 'ijazah_skl_rapor', TRUE, 'menunggu_verifikasi', @now6, @now6),
  (2, 44, 'skhu_nilai',       TRUE, 'menunggu_verifikasi', @now6, @now6),
  (3, 44, 'ktp',              TRUE, 'menunggu_verifikasi', @now6, @now6);

INSERT INTO dokumen_versis (id, dokumen_id, path, nama_asli, mime, ukuran, sha256, diunggah_pada, created_at, updated_at) VALUES
  (1, 1, 'pendaftaran/44/9f1c2a7e.pdf', 'ijazah.pdf', 'application/pdf', 512000, SHA2('dummy-ijazah-44', 256), @now, @now, @now),
  (2, 2, 'pendaftaran/44/4be81d03.pdf', 'skhu.pdf',   'application/pdf', 410000, SHA2('dummy-skhu-44', 256),   @now, @now, @now),
  (3, 3, 'pendaftaran/44/c73a90f5.jpg', 'ktp.jpg',    'image/jpeg',      230000, SHA2('dummy-ktp-44', 256),    @now, @now, @now);

UPDATE dokumens SET versi_aktif_id = id WHERE id IN (1, 2, 3);

-- 6. Peserta didik (pendaftaran 46) -------------------------------------------
INSERT INTO peserta_didiks (id, user_id, pendaftaran_id, npd, semester_berjalan, durasi_semester, status, npd_terbit_pada, created_at, updated_at)
VALUES (1, 13, 46, 'MC-TRPL-2026-0001', 1, 5, 'belum_aktif', @now - INTERVAL 6 DAY, @now, @now);

-- 7. Tagihan -------------------------------------------------------------------
SET @tarif_form_trpl = (SELECT t.id FROM tarifs t JOIN peminatans p ON p.id = t.peminatan_id
                        WHERE p.kode = 'TRPL' AND t.jenis = 'formulir' LIMIT 1);
SET @tarif_smt1_trpl = (SELECT t.id FROM tarifs t JOIN peminatans p ON p.id = t.peminatan_id
                        WHERE p.kode = 'TRPL' AND t.jenis = 'semester' AND t.semester_ke = 1 LIMIT 1);

INSERT INTO tagihans (id, kode_referensi, user_id, pendaftaran_id, peserta_didik_id, periode_akademik_id, tarif_id,
    jenis, semester_ke, nominal_dasar, kode_unik, nominal_total, jatuh_tempo, status, lunas_pada, created_at, updated_at) VALUES
  (1, 'TG-UJI-0001', 12, 45, NULL, NULL, @tarif_form_trpl, 'formulir', NULL, 300000, 123, 300123,
      DATE(@now + INTERVAL 6 DAY), 'menunggu_verifikasi', NULL, @now6, @now6),
  (2, 'TG-UJI-0002', 13, 46, NULL, NULL, @tarif_form_trpl, 'formulir', NULL, 300000, 45, 300045,
      DATE(@now - INTERVAL 1 DAY), 'lunas', @now - INTERVAL 6 DAY, @now6, @now6),
  (3, 'TG-UJI-0003', 13, 46, 1, 1, @tarif_smt1_trpl, 'semester', 1, 6500000, 77, 6500077,
      '2027-02-08', 'belum_bayar', NULL, @now6, @now6);

INSERT INTO bukti_pembayarans (tagihan_id, path, mime, ukuran, sha256, diunggah_pada, keputusan, kanal, created_at, updated_at) VALUES
  (1, 'bukti/1/e02b77aa.jpg', 'image/jpeg', 180000, SHA2('dummy-bukti-TG-UJI-0001', 256), @now, 'menunggu', 'web', @now6, @now6),
  (2, 'bukti/2/5d61c0b9.jpg', 'image/jpeg', 175000, SHA2('dummy-bukti-TG-UJI-0002', 256), @now - INTERVAL 7 DAY, 'diterima', 'web', @now6, @now6);
UPDATE bukti_pembayarans SET diverifikasi_oleh = 3, diverifikasi_pada = @now - INTERVAL 6 DAY WHERE tagihan_id = 2;

-- 8. Penghitung disesuaikan dengan data dummy ---------------------------------
UPDATE pengaturans SET nilai = CAST('46' AS JSON), updated_at = @now WHERE kunci = 'urut_nomor_pendaftaran_2026';
UPDATE pengaturans SET nilai = CAST('1'  AS JSON), updated_at = @now WHERE kunci = 'urut_npd_2026';

-- 9. Konten CMS contoh ---------------------------------------------------------
INSERT INTO konten_cms (bagian, judul, isi, status, diterbitkan_pada, diubah_oleh, created_at, updated_at) VALUES
  ('pengumuman', 'Pendaftaran Periode Ganjil 2027 Dibuka', 'Pendaftaran dibuka 1 November 2026 s.d. 31 Januari 2027.', 'terbit', @now, 1, @now, @now);

-- Ringkasan
SELECT 'users' t, COUNT(*) n FROM users
UNION ALL SELECT 'pendaftarans', COUNT(*) FROM pendaftarans
UNION ALL SELECT 'tagihans', COUNT(*) FROM tagihans
UNION ALL SELECT 'tarifs', COUNT(*) FROM tarifs;
