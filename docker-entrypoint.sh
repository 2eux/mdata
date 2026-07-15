#!/bin/bash
DB_HOST="kxs31cnzmktkqfav6tshrqqb"
DB_USER="mdata"
DB_PASS="Sv160505"
DB_NAME="mdm_portal"
SQL_FILE="/var/www/html/atri/mdm_portal.sql"

# Check if data exists
DATA_EXISTS=$(php -r '
$c = @new mysqli(getenv("DB_HOST") ?: "'$DB_HOST'", "'$DB_USER'", "'$DB_PASS'", "'$DB_NAME'");
if ($c->connect_error) { echo "DB_UNREACHABLE"; exit(0); }
$r = $c->query("SELECT COUNT(*) as cnt FROM users");
echo ($r && ($row = $r->fetch_assoc()) && $row["cnt"] > 0) ? "HAS_DATA" : "EMPTY";
' 2>/dev/null)

if [ "$DATA_EXISTS" != "HAS_DATA" ]; then
    echo "==> Importing database using mysql CLI..."
    
    # Clean SQL: remove CREATE DATABASE and USE statements
    sed '/^CREATE DATABASE/d; /^USE /d' "$SQL_FILE" > /tmp/cleaned.sql
    
    # Import via mysql CLI (much more reliable than PHP multi_query)
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < /tmp/cleaned.sql 2>&1
    
    if [ $? -eq 0 ]; then
        echo "==> Import completed successfully"
    else
        echo "==> Import had errors (may be non-fatal)"
    fi
fi

exec apache2-foreground
