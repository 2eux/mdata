#!/bin/bash
set -e
DB_HOST="kxs31cnzmktkqfav6tshrqqb"
DB_USER="mdata"
DB_PASS="Sv160505"
DB_NAME="mdm_portal"

echo "==> Checking if users table has data..."
DATA=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "SELECT COUNT(*) FROM users" 2>/dev/null || echo "NO_TABLE")

if [ "$DATA" = "NO_TABLE" ] || [ "$DATA" = "0" ] || [ -z "$DATA" ]; then
    echo "==> Importing database schema from mdm_portal.sql..."
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < /var/www/html/atri/mdm_portal.sql 2>&1
    echo "==> Import exit code: $?"
fi

exec apache2-foreground
