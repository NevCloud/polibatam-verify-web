-- =====================================================================
-- Polibatam Verify — 02. VIEW laporan (dibaca akun pv_report saja)
-- Dijalankan oleh : pv_migrator  → DEFINER = pv_migrator
-- Ref             : Dokumen Keamanan Basis Data §7 (UTS-09, DBC-07),
--                   ERD §5 butir 7 & 9 (BR-64: Draf tidak pernah tampil)
--
-- Aturan: VIEW TIDAK BOLEH memuat nik_terenkripsi, nik_hash, alamat,
-- tanggal/tempat lahir, no. rekening, nama pemilik rekening, password,
-- token, maupun path file.
-- =====================================================================

SET NAMES utf8mb4;
USE polibatam_verify;

-- V1. Antrean verifikasi berkas (hanya status 'diajukan')
CREATE OR REPLACE
  SQL SECURITY DEFINER
  VIEW v_antrean_verifikasi_berkas AS
SELECT  p.id                AS pendaftaran_id,
        p.nomor_pendaftaran,
        p.nama_lengkap,
        p.asal_sekolah,
        p.status,
        p.waktu_pengajuan,
        pm.kode             AS kode_peminatan,
        pm.nama             AS peminatan,
        pr.nama             AS periode,
        (SELECT COUNT(*) FROM dokumens d
          WHERE d.pendaftaran_id = p.id
            AND d.status = 'menunggu_verifikasi') AS dokumen_menunggu
FROM pendaftarans p
JOIN peminatan_periodes   pp ON pp.id = p.peminatan_periode_id
JOIN peminatans           pm ON pm.id = pp.peminatan_id
JOIN periode_pendaftarans pr ON pr.id = pp.periode_pendaftaran_id
WHERE p.status = 'diajukan';

-- V2. Rekap pembayaran (tanpa identitas & path bukti)
CREATE OR REPLACE
  SQL SECURITY DEFINER
  VIEW v_rekap_pembayaran AS
SELECT  t.kode_referensi,
        t.jenis,
        t.semester_ke,
        t.nominal_dasar,
        t.kode_unik,
        t.nominal_total,
        t.status,
        t.jatuh_tempo,
        t.lunas_pada,
        pm.kode AS kode_peminatan,
        pr.nama AS periode
FROM tagihans t
JOIN pendaftarans         p  ON p.id  = t.pendaftaran_id
JOIN peminatan_periodes   pp ON pp.id = p.peminatan_periode_id
JOIN peminatans           pm ON pm.id = pp.peminatan_id
JOIN periode_pendaftarans pr ON pr.id = pp.periode_pendaftaran_id;

-- V3. Status refund (tanpa no. rekening, nama pemilik, bukti transfer)
CREATE OR REPLACE
  SQL SECURITY DEFINER
  VIEW v_status_refund AS
SELECT  r.id AS refund_id,
        r.pendaftaran_id,
        r.sebab,
        r.nominal,
        r.nama_bank,
        r.status,
        r.selesai_pada
FROM refunds r;

-- V4. Data pendaftaran untuk ekspor FR-64 & arsip periode FR-65
--     Draf disaring di sini (BR-64). NIK tidak ada di VIEW; NIK tersamar
--     (mis. 3201********0001) dibentuk aplikasi bila memang dibutuhkan.
CREATE OR REPLACE
  SQL SECURITY DEFINER
  VIEW v_data_pendaftaran AS
SELECT  p.id                 AS pendaftaran_id,
        p.nomor_pendaftaran,
        p.nama_lengkap,
        p.jenis_kelamin,
        p.asal_sekolah,
        p.tahun_lulus,
        p.status,
        p.waktu_pengajuan,
        p.waktu_masuk_kuota,
        p.alasan_batal,
        t.kode               AS kode_track,
        t.nama               AS track,
        pm.kode              AS kode_peminatan,
        pm.nama              AS peminatan,
        pr.id                AS periode_pendaftaran_id,
        pr.nama              AS periode,
        pd.npd,
        pd.status            AS status_peserta
FROM pendaftarans p
JOIN peminatan_periodes   pp ON pp.id = p.peminatan_periode_id
JOIN peminatans           pm ON pm.id = pp.peminatan_id
JOIN tracks               t  ON t.id  = pm.track_id
JOIN periode_pendaftarans pr ON pr.id = pp.periode_pendaftaran_id
LEFT JOIN peserta_didiks  pd ON pd.pendaftaran_id = p.id
WHERE p.status <> 'draf';

SHOW FULL TABLES WHERE Table_type = 'VIEW';
