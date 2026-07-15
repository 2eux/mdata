#!/bin/bash
# Auto-import MDM Portal database on first boot (errors won't crash Apache)
DB_HOST="kxs31cnzmktkqfav6tshrqqb"
DB_USER="mdata"
DB_PASS="Sv160505"
DB_NAME="mdm_portal"

# Check if data exists (ignore errors)
DATA_CHECK=$(mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "SELECT COUNT(*) FROM users" 2>/dev/null || echo "NO")

if [ "$DATA_CHECK" = "NO" ] || [ "$DATA_CHECK" = "0" ]; then
    echo "==> Importing database..."
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < /var/www/html/atri/mdm_portal.sql 2>&1 || echo "Import had errors (continuing)"
fi

exec apache2-foreground
