# =====================================================================
# Polibatam Verify — Restore ke basis data UJI (Laragon / Windows)
# Akun  : pv_dba                     Ref: Dok. Keamanan §13.3, UTS-18
# Pakai : powershell -ExecutionPolicy Bypass -File ops\restore-db.ps1 `
#           -File C:\backup\polibatam-verify\polibatam_verify_20261122T010000Z.sql
# Tidak pernah menimpa polibatam_verify; target selalu polibatam_verify_restore.
# =====================================================================
param([Parameter(Mandatory = $true)][string]$File)
$ErrorActionPreference = "Stop"

$bin = "C:\laragon\bin\mysql\mysql-9.7.0-winx64\bin"
$cnf = "C:\laragon\etc\pv\dba.cnf"

# 1. Verifikasi integritas file
$expected = (Get-Content "$File.sha256").Split(" ")[0]
$actual   = (Get-FileHash $File -Algorithm SHA256).Hash.ToLower()
if ($expected -ne $actual) { throw "Checksum TIDAK cocok — file backup rusak/diubah." }
Write-Host "Checksum cocok: $actual"

# 2. Siapkan DB uji kosong
& "$bin\mysql.exe" --defaults-extra-file="$cnf" -e "DROP DATABASE IF EXISTS polibatam_verify_restore; CREATE DATABASE polibatam_verify_restore CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;"
if ($LASTEXITCODE -ne 0) { throw "Gagal menyiapkan DB uji" }

# 3. Restore (path memakai '/' agar diterima perintah source)
$src = $File.Replace("\", "/")
& "$bin\mysql.exe" --defaults-extra-file="$cnf" polibatam_verify_restore -e "source $src"
if ($LASTEXITCODE -ne 0) { throw "Restore gagal (exit $LASTEXITCODE)" }

Write-Host "Restore selesai. Jalankan uji\06_verifikasi_restore.sql sebagai pv_dba."
