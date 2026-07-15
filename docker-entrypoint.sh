#!/bin/bash
# Auto-import MDM Portal database on first boot

DB_HOST="kxs31cnzmktkqfav6tshrqqb"
DB_USER="mdata"
DB_PASS="Sv160505"
DB_NAME="mdm_portal"
SQL_FILE="/var/www/html/atri/mdm_portal.sql"

# Check if tables exist
php -r "
\$c = @new mysqli('$DB_HOST', '$DB_USER', '$DB_PASS', '$DB_NAME');
if (\$c->connect_error) { echo 'DB_UNREACHABLE'; exit(0); }
\$r = \$c->query('SELECT 1 FROM users LIMIT 1');
echo \$r ? 'EXISTS' : 'MISSING';
" 2>/dev/null | grep -q EXISTS

if [ $? -ne 0 ]; then
    echo "==> Importing database schema..."
    # Strip CREATE DATABASE and USE statements - they fail when DB already exists
    # Also strip comments and set statements
    grep -v '^CREATE DATABASE\|^USE \|^/\*!\|^-- ' "$SQL_FILE" | grep -v '^$' | sed 's/DEFAULT CHARSET=latin1//' | sed 's/\/\*!40100 DEFAULT CHARACTER SET latin1 \*\/;//' > /tmp/cleaned.sql
    
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
