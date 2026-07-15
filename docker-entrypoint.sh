#!/bin/bash
DB_HOST="kxs31cnzmktkqfav6tshrqqb"
DB_USER="mdata"
DB_PASS="Sv160505"
DB_NAME="mdm_portal"
SQL_FILE="/var/www/html/atri/mdm_portal.sql"

php -r "
\$c = @new mysqli('$DB_HOST', '$DB_USER', '$DB_PASS', '$DB_NAME');
if (\$c->connect_error) { echo 'DB_UNREACHABLE'; exit(0); }
\$r = \$c->query('SELECT COUNT(*) as cnt FROM users');
echo (\$r && \$r->fetch_assoc()['cnt'] > 0) ? 'HAS_DATA' : 'EMPTY_OR_MISSING';
" 2>/dev/null | grep -q HAS_DATA

if [ $? -ne 0 ]; then
    echo "==> Importing database..."
    
    php -r "
        \$c = new mysqli('$DB_HOST', '$DB_USER', '$DB_PASS', '$DB_NAME');
        \$c->query('SET FOREIGN_KEY_CHECKS = 0');
        \$tables = \$c->query('SHOW TABLES');
        if (\$tables) {
            while (\$row = \$tables->fetch_array()) {
                \$c->query('DROP TABLE IF EXISTS ' . \$row[0]);
            }
        }
        \$c->query('SET FOREIGN_KEY_CHECKS = 1');
        echo 'Tables reset.\n';
    "
    
    # Clean and import
    sed '/^CREATE DATABASE/d; /^USE /d' "$SQL_FILE" > /tmp/cleaned.sql
    
    php -r "
        \$c = new mysqli('$DB_HOST', '$DB_USER', '$DB_PASS', '$DB_NAME');
        \$sql = file_get_contents('/tmp/cleaned.sql');
        \$c->query('SET FOREIGN_KEY_CHECKS = 0');
        if (\$c->multi_query(\$sql)) {
            do {} while (\$c->next_result());
            echo 'Import OK\n';
        } else {
            echo 'Import FAIL: ' . \$c->error . '\n';
        }
        \$c->query('SET FOREIGN_KEY_CHECKS = 1');
    "
fi

exec apache2-foreground
