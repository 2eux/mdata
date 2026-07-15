#!/bin/bash
# Auto-import MDM Portal database on first boot

DB_HOST="kxs31cnzmktkqfav6tshrqqb"
DB_USER="mdata"
DB_PASS="Sv160505"
DB_NAME="mdm_portal"
SQL_FILE="/var/www/html/atri/mdm_portal.sql"

# Check if data exists (table must have rows)
php -r "
\$c = @new mysqli('$DB_HOST', '$DB_USER', '$DB_PASS', '$DB_NAME');
if (\$c->connect_error) { echo 'DB_UNREACHABLE'; exit(0); }
\$r = \$c->query('SELECT COUNT(*) as cnt FROM users');
echo (\$r && \$r->fetch_assoc()['cnt'] > 0) ? 'HAS_DATA' : 'EMPTY';
" 2>/dev/null | grep -q HAS_DATA

if [ $? -ne 0 ]; then
    echo "==> Importing database schema..."
    
    # Drop existing tables
    php -r "
        \$c = new mysqli('$DB_HOST', '$DB_USER', '$DB_PASS', '$DB_NAME');
        \$tables = \$c->query('SHOW TABLES');
        if (\$tables) {
            while (\$row = \$tables->fetch_array()) {
                \$c->query('DROP TABLE IF EXISTS ' . \$row[0]);
            }
            echo 'Dropped existing tables\n';
        }
    "
    
    # Import SQL
    sed '/^CREATE DATABASE/d; /^USE /d' "$SQL_FILE" > /tmp/cleaned.sql
    
    php -r "
        \$c = new mysqli('$DB_HOST', '$DB_USER', '$DB_PASS', '$DB_NAME');
        \$sql = file_get_contents('/tmp/cleaned.sql');
        if (\$c->multi_query(\$sql)) {
            do {} while (\$c->next_result());
            echo 'Import completed successfully\n';
        } else {
            echo 'Import failed: ' . \$c->error . '\n';
        }
    "
fi

exec apache2-foreground
