#!/usr/bin/env bash
# =====================================================================
# Polibatam Verify — Backup basis data (server Linux, cron 01.00 WIB)
# Akun : pv_backup@localhost          Ref: Dok. Keamanan §13.2
# Cron : 0 18 * * *  /opt/polibatam-verify/backup-db.sh   (18.00 UTC = 01.00 WIB)
# =====================================================================
set -euo pipefail
umask 077

CNF=/etc/polibatam-verify/backup.cnf
DIR=/var/backups/polibatam-verify
LOG=/var/log/polibatam-verify/backup.log
RECIPIENT=backup@polibatam-verify          # kunci publik GPG tim [ASUMSI]

mkdir -p "$DIR" "$(dirname "$LOG")"
TS=$(date -u +%Y%m%dT%H%M%SZ)
OUT="$DIR/polibatam_verify_${TS}.sql.gz"

mysqldump --defaults-extra-file="$CNF" \
  --single-transaction --triggers --no-tablespaces --set-gtid-purged=OFF \
  polibatam_verify | gzip > "$OUT"

sha256sum "$OUT" > "$OUT.sha256"
gpg --batch --yes --encrypt --recipient "$RECIPIENT" "$OUT" && shred -u "$OUT"   # → .sql.gz.gpg

echo "$(date -u +%FT%TZ) backup selesai: $OUT.gpg" >> "$LOG"

# Retensi: 7 harian + 4 mingguan (salinan hari Minggu disimpan 28 hari) [A-57]
now=$(date +%s)
for f in "$DIR"/polibatam_verify_*; do
  [ -e "$f" ] || continue
  mtime=$(stat -c %Y "$f"); age=$(( (now - mtime) / 86400 )); dow=$(date -d "@$mtime" +%u)
  if [ "$age" -gt 28 ] || { [ "$age" -gt 7 ] && [ "$dow" != 7 ]; }; then rm -f "$f"; fi
done
