# Uji 07 — Akses bersamaan & locking (UTS-15, CC-01, CC-02)

Buka **dua terminal** dan login sebagai `pv_app` di keduanya:

```
mysql -h 127.0.0.1 -u pv_app -p polibatam_verify
```

Kondisi awal (data dummy): `pengaturans.kunci = 'urut_nomor_pendaftaran_2026'`, `nilai = 46`.

## CC-01 — Pessimistic locking penghitung nomor pendaftaran (TST-25, BR-68)

Kuota maksimum dan slot kuota dihapus (K-42), jadi bukti *pessimistic locking* memakai penghitung nomor pendaftaran yang dikunci `NomorPendaftaranGenerator` saat Draf pertama kali disimpan.

| Langkah | Sesi A | Sesi B |
|---|---|---|
| 1 | `START TRANSACTION;` | |
| 2 | `SELECT nilai FROM pengaturans WHERE kunci = 'urut_nomor_pendaftaran_2026' FOR UPDATE;` → 46 | |
| 3 | | `START TRANSACTION;` |
| 4 | | `SELECT nilai FROM pengaturans WHERE kunci = 'urut_nomor_pendaftaran_2026' FOR UPDATE;` → **menunggu** |
| 5 | `UPDATE pengaturans SET nilai = CAST('47' AS JSON) WHERE kunci = 'urut_nomor_pendaftaran_2026';` | (masih menunggu) |
| 6 | `COMMIT;` | Kunci lepas → membaca **47**, sehingga nomor berikutnya 48 (tidak ganda) |
| 7 | | `ROLLBACK;` |

Bukti tambahan: `pendaftarans.nomor_pendaftaran` ber-UNIQUE, sehingga nomor ganda ditolak `ERROR 1062` (pengaman terakhir).

Kembalikan data: `UPDATE pengaturans SET nilai = CAST('46' AS JSON) WHERE kunci = 'urut_nomor_pendaftaran_2026';`

## CC-02 — Optimistic locking verifikasi web vs mobile (TST-27)

Admin A (web) dan Admin B (mobile) membuka tagihan yang sama dan sama-sama melihat `updated_at` yang sama.

**Sesi A dan B:**
```sql
SELECT id, status, updated_at FROM tagihans WHERE kode_referensi = 'TG-UJI-0003';
-- catat updated_at, mis. 2026-11-22 06:59:12.123456
```

**Sesi A (lebih dulu):**
```sql
UPDATE tagihans SET status = 'menunggu_verifikasi', updated_at = UTC_TIMESTAMP(6)
WHERE kode_referensi = 'TG-UJI-0003' AND status = 'belum_bayar'
  AND updated_at = '2026-11-22 06:59:12.123456';   -- 1 row affected
```

**Sesi B (versi lama):**
```sql
UPDATE tagihans SET status = 'dibatalkan', updated_at = UTC_TIMESTAMP(6)
WHERE kode_referensi = 'TG-UJI-0003' AND status = 'belum_bayar'
  AND updated_at = '2026-11-22 06:59:12.123456';   -- 0 rows affected → aplikasi menjawab 409 SUDAH_DIVERIFIKASI
```

`0 rows affected` adalah bukti *lost update* tercegah. Presisi `TIMESTAMP(6)` + kondisi status mencegah konflik lolos walau terjadi pada detik yang sama (K-36).

## CC-05 — Serialisasi hash chain audit (TST-30)

```sql
-- Sesi A
SELECT GET_LOCK('audit_chain', 5);   -- 1
-- Sesi B
SELECT GET_LOCK('audit_chain', 5);   -- menunggu 5 detik → 0 (tidak dapat kunci)
-- Sesi A
SELECT RELEASE_LOCK('audit_chain');
```
