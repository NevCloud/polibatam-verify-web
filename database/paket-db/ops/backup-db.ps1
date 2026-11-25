# =====================================================================
# Polibatam Verify — Backup basis data (Laragon / Windows)
# Akun  : pv_backup (hanya-baca)      Ref: Dok. Keamanan §13.2, UTS-17
# Pakai : powershell -ExecutionPolicy Bypass -File ops\backup-db.ps1
# =====================================================================
$ErrorActionPreference = "Stop"

$bin   = "C:\laragon\bin\mysql\mysql-9.7.0-winx64\bin"   # sesuaikan dengan folder versi MySQL di Laragon
$cnf   = "C:\laragon\etc\pv\backup.cnf"
$dir   = "C:\backup\polibatam-verify"
$log   = Join-Path $dir "backup.log"

New-Item -ItemType Directory -Force -Path $dir | Out-Null
$ts  = (Get-Date).ToUniversalTime().ToString("yyyyMMddTHHmmssZ")
$out = Join-Path $dir "polibatam_verify_$ts.sql"

# --result-file, BUKAN redirect '>': PowerShell 5.1 menulis UTF-16 sehingga dump rusak.
& "$bin\mysqldump.exe" --defaults-extra-file="$cnf" `
  --single-transaction --triggers --no-tablespaces --set-gtid-purged=OFF `
  --result-file="$out" polibatam_verify
if ($LASTEXITCODE -ne 0) { throw "mysqldump gagal (exit $LASTEXITCODE)" }

# Checksum + jumlah baris tabel kunci saat backup (pembanding TST-37)
$hash = (Get-FileHash $out -Algorithm SHA256).Hash.ToLower()
"$hash  $(Split-Path $out -Leaf)" | Set-Content -Encoding ascii "$out.sha256"

$size = (Get-Item $out).Length
"$((Get-Date).ToUniversalTime().ToString('s'))Z backup selesai: $out ($size byte) sha256=$hash" |
  Add-Content -Encoding utf8 $log

# Retensi lokal 7 harian [ASUMSI A-57]
Get-ChildItem $dir -Filter "polibatam_verify_*.sql*" |
  Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-7) } | Remove-Item

Get-Item $out | Select-Object Name, Length, LastWriteTime   # evidence UTS-17
Get-Content "$out.sha256"
