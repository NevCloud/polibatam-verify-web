-- =====================================================================
-- Polibatam Verify — 04. Data awal (wajib, semua lingkungan)
-- Dijalankan oleh : pv_migrator
-- Isi             : role & permission (spatie), pengaturan bisnis,
--                   kriteria checklist dokumen, contoh track/peminatan
-- Ref             : Arsitektur §8 "Data awal", ERD §3.19, Pedoman §6,
--                   K-05 (track/peminatan dapat diubah lewat sistem)
--
-- Akun pengguna aplikasi (Super Admin pertama, dsb.) TIDAK dibuat di
-- sini karena butuh hash password & TOTP dari Laravel. Untuk lokal,
-- lihat 06_seed_dummy_lokal.sql; untuk produksi, buat lewat
-- `php artisan` (lihat README).
-- =====================================================================

SET NAMES utf8mb4;
USE polibatam_verify;
SET time_zone = '+00:00';
SET @now = UTC_TIMESTAMP();

-- ---------------------------------------------------------------------
-- 1. Role (5 role, guard web) — keputusan pemilik proyek 25-11-2026 (temuan T-07):
--    satu role `peserta`; "Calon Peserta" vs "Peserta Didik" dibedakan dari
--    ada/tidaknya baris peserta_didiks (NPD), bukan dari role.
-- ---------------------------------------------------------------------
INSERT INTO roles (name, guard_name, created_at, updated_at) VALUES
  ('peserta',        'web', @now, @now),
  ('admin_silau',    'web', @now, @now),
  ('admin_keuangan', 'web', @now, @now),
  ('admin_track',    'web', @now, @now),
  ('super_admin',    'web', @now, @now);

-- ---------------------------------------------------------------------
-- 2. Permission — diturunkan dari Access Control Matrix (Dok. Keamanan
--    §4.1). Batasan "hanya data sendiri" tetap dijaga Policy, bukan di
--    sini. [ASUMSI] nama permission; boleh disesuaikan saat implementasi.
-- ---------------------------------------------------------------------
INSERT INTO permissions (name, guard_name, created_at, updated_at) VALUES
  ('pendaftaran.kelola-sendiri',       'web', @now, @now),  -- formulir, dokumen, bukti bayar milik sendiri
  ('tagihan.lihat-sendiri',            'web', @now, @now),
  ('refund.kelola-rekening-sendiri',   'web', @now, @now),
  ('data-pendaftaran.lihat',           'web', @now, @now),  -- FR-63 (semua admin)
  ('data-pendaftaran.ekspor',          'web', @now, @now),  -- FR-64 (SILAU, Super Admin)
  ('arsip-periode.unduh',              'web', @now, @now),  -- FR-66 (SILAU, Super Admin)
  ('arsip-periode.ulangi',             'web', @now, @now),  -- FR-65 (Super Admin)
  ('verifikasi-berkas.kelola',         'web', @now, @now),  -- FR-18..22
  ('pendaftaran.lihat-nik',            'web', @now, @now),  -- dekripsi NIK (SILAU, Super Admin)
  ('verifikasi-pembayaran.kelola',     'web', @now, @now),  -- FR-31..35
  ('tagihan.tetapkan-jatuh-tempo',     'web', @now, @now),  -- FR-57 (Super Admin)
  ('tagihan.koreksi-lunas',            'web', @now, @now),  -- Super Admin
  ('refund.proses',                    'web', @now, @now),  -- FR-42..44, FR-62
  ('refund.lihat-rekening-lengkap',    'web', @now, @now),  -- dicatat audit (BR-58)
  ('laporan-keuangan.ekspor',          'web', @now, @now),  -- FR-38
  ('master.kelola',                    'web', @now, @now),  -- track & peminatan (FR-45, FR-46)
  ('tarif.kelola',                     'web', @now, @now),  -- FR-47
  ('kuota.kelola',                     'web', @now, @now),  -- FR-48, FR-49
  ('peminatan.batalkan',               'web', @now, @now),  -- FR-41 (Super Admin)
  ('periode.kelola',                   'web', @now, @now),  -- FR-50, FR-51
  ('rollover.jalankan',                'web', @now, @now),  -- FR-52
  ('akun-admin.kelola',                'web', @now, @now),  -- FR-06
  ('audit-log.lihat',                  'web', @now, @now),  -- FR-56
  ('cms.kelola',                       'web', @now, @now),  -- FR-10
  ('pengaturan.kelola',                'web', @now, @now),  -- NFR-40
  ('api-mobile.akses',                 'web', @now, @now);  -- hanya SILAU & Keuangan (K-29)

-- Helper: pasangkan role ↔ permission berdasarkan nama
INSERT INTO role_has_permissions (permission_id, role_id)
SELECT p.id, r.id
FROM (
            SELECT 'peserta'        AS role_name, 'pendaftaran.kelola-sendiri'     AS perm
  UNION ALL SELECT 'peserta',        'tagihan.lihat-sendiri'
  UNION ALL SELECT 'peserta',        'refund.kelola-rekening-sendiri'

  UNION ALL SELECT 'admin_silau',    'data-pendaftaran.lihat'
  UNION ALL SELECT 'admin_silau',    'data-pendaftaran.ekspor'
  UNION ALL SELECT 'admin_silau',    'arsip-periode.unduh'
  UNION ALL SELECT 'admin_silau',    'verifikasi-berkas.kelola'
  UNION ALL SELECT 'admin_silau',    'pendaftaran.lihat-nik'
  UNION ALL SELECT 'admin_silau',    'api-mobile.akses'

  UNION ALL SELECT 'admin_keuangan', 'data-pendaftaran.lihat'
  UNION ALL SELECT 'admin_keuangan', 'verifikasi-pembayaran.kelola'
  UNION ALL SELECT 'admin_keuangan', 'refund.proses'
  UNION ALL SELECT 'admin_keuangan', 'refund.lihat-rekening-lengkap'
  UNION ALL SELECT 'admin_keuangan', 'laporan-keuangan.ekspor'
  UNION ALL SELECT 'admin_keuangan', 'api-mobile.akses'

  UNION ALL SELECT 'admin_track',    'data-pendaftaran.lihat'
  UNION ALL SELECT 'admin_track',    'master.kelola'
  UNION ALL SELECT 'admin_track',    'tarif.kelola'
  UNION ALL SELECT 'admin_track',    'kuota.kelola'
) m
JOIN roles r       ON r.name = m.role_name AND r.guard_name = 'web'
JOIN permissions p ON p.name = m.perm      AND p.guard_name = 'web';

-- Super Admin: semua permission admin, kecuali yang khusus peserta & API mobile
INSERT INTO role_has_permissions (permission_id, role_id)
SELECT p.id, r.id
FROM permissions p
JOIN roles r ON r.name = 'super_admin'
WHERE p.name NOT IN ('pendaftaran.kelola-sendiri', 'tagihan.lihat-sendiri',
                     'refund.kelola-rekening-sendiri', 'api-mobile.akses');

-- ---------------------------------------------------------------------
-- 3. Pengaturan bisnis (NFR-40) — diubah Super Admin lewat aplikasi
-- ---------------------------------------------------------------------
INSERT INTO pengaturans (kunci, nilai, keterangan, diubah_oleh, created_at, updated_at) VALUES
  ('batas_pelunasan_formulir_hari', CAST('7' AS JSON),
     'Batas pelunasan formulir sejak Masuk Kuota (BR-21)', NULL, @now, @now),
  ('jatuh_tempo_semester_hari_sebelum', CAST('7' AS JSON),
     'Jatuh tempo tagihan semester = tanggal mulai periode akademik - N hari (BR-35)', NULL, @now, @now),
  ('format_npd', JSON_QUOTE('MC-{KODE_PEMINATAN}-{TAHUN}-{URUT4}'),
     '[ASUMSI] Pola NPD (BR-41), contoh MC-TRPL-2026-0001', NULL, @now, @now),
  ('masa_berlaku_ekspor_jam', CAST('24' AS JSON),
     'Masa berlaku file & tautan ekspor (BR-67)', NULL, @now, @now),
  ('ukuran_maks_unggah_kb', CAST('2048' AS JSON),
     'Ukuran maksimum dokumen & bukti bayar (BR-12)', NULL, @now, @now),
  ('kode_unik_rentang', CAST('{"min": 1, "maks": 999}' AS JSON),
     'Rentang kode unik pembayaran (BR-29)', NULL, @now, @now),
  ('rollover_peringatan_hari', CAST('14' AS JSON),
     'Peringatan rollover H-N (BR-44)', NULL, @now, @now),
  ('login_batas_gagal', CAST('5' AS JSON),
     'Gagal login berturut-turut sebelum akun dikunci (BR-03)', NULL, @now, @now),
  ('login_kunci_menit', CAST('15' AS JSON),
     'Lama akun dikunci setelah batas gagal login (BR-03)', NULL, @now, @now),
  ('sesi_admin_menit', CAST('30' AS JSON),
     'Sesi admin berakhir setelah N menit tidak aktif (NFR-03)', NULL, @now, @now),
  ('sesi_peserta_menit', CAST('60' AS JSON),
     'Sesi peserta berakhir setelah N menit tidak aktif (NFR-03)', NULL, @now, @now),
  (CONCAT('urut_nomor_pendaftaran_', YEAR(@now)), CAST('0' AS JSON),
     'Penghitung nomor pendaftaran tahun berjalan; dikunci FOR UPDATE (BR-68)', NULL, @now, @now),
  (CONCAT('urut_npd_', YEAR(@now)), CAST('0' AS JSON),
     'Penghitung nomor urut NPD tahun berjalan; dikunci FOR UPDATE (CC-07)', NULL, @now, @now);

-- ---------------------------------------------------------------------
-- 4. Kriteria checklist verifikasi dokumen (FR-20) — [ASUMSI] teks awal
-- ---------------------------------------------------------------------
INSERT INTO kriteria_checklists (jenis_dokumen, teks, urutan, is_active, created_at, updated_at) VALUES
  ('ijazah_skl_rapor', 'Dokumen terbaca jelas dan tidak terpotong',             1, TRUE, @now, @now),
  ('ijazah_skl_rapor', 'Nama sesuai dengan data formulir',                      2, TRUE, @now, @now),
  ('ijazah_skl_rapor', 'Terdapat cap/tanda tangan sekolah',                     3, TRUE, @now, @now),
  ('skhu_nilai',       'Dokumen terbaca jelas dan tidak terpotong',             1, TRUE, @now, @now),
  ('skhu_nilai',       'Nama sesuai dengan data formulir',                      2, TRUE, @now, @now),
  ('ktp',              'Foto KTP terbaca jelas',                                1, TRUE, @now, @now),
  ('ktp',              'NIK sesuai dengan NIK di formulir',                     2, TRUE, @now, @now),
  ('ktp',              'Nama dan tanggal lahir sesuai dengan data formulir',    3, TRUE, @now, @now),
  ('bukti_pendaftaran_polibatam', 'Dokumen terbaca jelas',                      1, TRUE, @now, @now),
  ('bukti_pendaftaran_polibatam', 'Nama sesuai dengan data formulir',           2, TRUE, @now, @now);

-- ---------------------------------------------------------------------
-- 5. Contoh track & peminatan dari Pedoman 2026 §6 (K-05: bukan data
--    tetap, dapat diubah Admin Track). Kode = [ASUMSI].
--    Durasi RPL bervariasi per prodi tujuan; diambil durasi
--    microcredential terpanjang di tiap peminatan [ASUMSI].
-- ---------------------------------------------------------------------
INSERT INTO tracks (kode, nama, is_active, created_at, updated_at) VALUES
  ('MB',  'Manajemen & Bisnis', TRUE, @now, @now),
  ('TE',  'Teknik Elektro',     TRUE, @now, @now),
  ('TI',  'Teknik Informatika', TRUE, @now, @now),   -- keputusan 25-11-2026 (T-08), sama dengan design-memory §9.1
  ('TM',  'Teknik Mesin',       TRUE, @now, @now);

INSERT INTO peminatans (track_id, kode, nama, durasi_semester, is_active, created_at, updated_at)
SELECT t.id, x.kode, x.nama, x.durasi, TRUE, @now, @now
FROM (
            SELECT 'MB' AS track, 'ADB'  AS kode, 'Administrasi Bisnis'      AS nama, 4 AS durasi
  UNION ALL SELECT 'MB',  'AKT',  'Akuntansi',                4
  UNION ALL SELECT 'TE',  'ELU',  'Umum (Sesuai Skema)',      4
  UNION ALL SELECT 'TI',  'TRMM', 'Rekayasa Multimedia',      5
  UNION ALL SELECT 'TI',  'TRPL', 'Rekayasa Perangkat Lunak', 5
  UNION ALL SELECT 'TM',  'TMS',  'Teknik Mesin',             4
  UNION ALL SELECT 'TM',  'TRPF', 'Rekayasa Pengelasan',      7
  UNION ALL SELECT 'TM',  'AMTO', 'AMTO Basic License',       4
) x
JOIN tracks t ON t.kode = x.track;

-- Ringkasan
SELECT (SELECT COUNT(*) FROM roles)                AS roles,
       (SELECT COUNT(*) FROM permissions)          AS permissions,
       (SELECT COUNT(*) FROM role_has_permissions) AS role_perm,
       (SELECT COUNT(*) FROM pengaturans)          AS pengaturans,
       (SELECT COUNT(*) FROM kriteria_checklists)  AS kriteria,
       (SELECT COUNT(*) FROM tracks)               AS tracks,
       (SELECT COUNT(*) FROM peminatans)           AS peminatans;
