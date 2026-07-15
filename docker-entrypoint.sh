#!/bin/bash
# Auto-import MDM Portal database on first boot

DB_HOST="kxs31cnzmktkqfav6tshrqqb"
DB_USER="mdata"
DB_PASS="Sv160505"
DB_NAME="mdm_portal"
SQL_FILE="/var/www/html/atri/mdm_portal.sql"

# Check if tables exist, import if not
php -r "
\$c = @new mysqli('$DB_HOST', '$DB_USER', '$DB_PASS', '$DB_NAME');
if (\$c->connect_error) { echo 'DB_UNREACHABLE'; exit(0); }
\$r = \$c->query('SELECT 1 FROM users LIMIT 1');
echo \$r ? 'EXISTS' : 'MISSING';
" 2>/dev/null | grep -q EXISTS

if [ $? -ne 0 ]; then
    echo "==> Importing database schema..."
    php -r "
        \$c = new mysqli('$DB_HOST', '$DB_USER', '$DB_PASS', '$DB_NAME');
        \$sql = file_get_contents('$SQL_FILE');
        if (\$c->multi_query(\$sql)) {
            do {} while (\$c->next_result());
            echo 'Import completed successfully';
        } else {
            echo 'Import failed: ' . \$c->error;
        }
    "
fi

exec apache2-foreground
