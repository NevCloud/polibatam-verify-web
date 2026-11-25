# Introduction

REST API v1 untuk aplikasi mobile Admin SILAU & Admin Keuangan (P06, FR-60). Tidak ada endpoint peserta maupun keputusan massal.

<aside>
    <strong>Base URL</strong>: <code>http://127.0.0.1:8000</code>
</aside>

    Format respons: `{success, message, data, meta}`; galat: `{success: false, message, code, errors}`. Waktu ISO 8601 UTC, uang integer Rupiah.
    Login dua tahap: `POST /auth/login` → `challenge_token` → `POST /auth/two-factor` → token Bearer 12 jam per perangkat.
    Setiap keputusan verifikasi membawa `versi`; bila data sudah diputuskan admin lain → 409 `SUDAH_DIVERIFIKASI`.

