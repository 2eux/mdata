<?php
$host     = "kxs31cnzmktkqfav6tshrqqb";
$username = "mdata";
$password = "Sv160505";
$database = "mdm_portal";

$koneksi = new mysqli($host, $username, $password, $database);
if ($koneksi->connect_error) {
    file_put_contents('/tmp/db_debug.txt', 'CONNECT_ERR: '.$koneksi->connect_error);
    die("Maaf, terjadi masalah pada sistem.");
}

$koneksi->set_charset("utf8mb4");
file_put_contents('/tmp/db_debug.txt', 'CONNECTED_OK');

// Auto-init
$init_flag = '/tmp/db_initialized.flag';
if (!file_exists($init_flag)) {
    $check = $koneksi->query("SELECT COUNT(*) as cnt FROM users");
    $has_data = false;
    if ($check) { $row = $check->fetch_assoc(); $has_data = ($row['cnt'] > 0); }
    file_put_contents('/tmp/db_debug.txt', 'DATA_CHECK: '.($has_data?'HAS_DATA':'EMPTY'), FILE_APPEND);
    
    if (!$has_data) {
        $init = new mysqli($host, $username, $password, $database);
        if ($init->connect_error) {
            file_put_contents('/tmp/db_debug.txt', ' INIT_CONN_ERR: '.$init->connect_error, FILE_APPEND);
        } else {
            // Minimal: just create tables and insert one user
            $init->query("SET FOREIGN_KEY_CHECKS = 0");
            $tables = $init->query("SHOW TABLES");
            if ($tables) { while ($row = $tables->fetch_array()) { $init->query("DROP TABLE IF EXISTS `{$row[0]}`"); } }
            $init->query("SET FOREIGN_KEY_CHECKS = 1");
            
            $r1 = $init->query("CREATE TABLE company (id INT AUTO_INCREMENT PRIMARY KEY, nama_company VARCHAR(100), company_code VARCHAR(10)) ENGINE=InnoDB");
            $r2 = $init->query("INSERT INTO company VALUES (1,'Alamtri','ATRI')");
            $r3 = $init->query("CREATE TABLE role (id INT AUTO_INCREMENT PRIMARY KEY, nama_role VARCHAR(50)) ENGINE=InnoDB");
            $r4 = $init->query("INSERT INTO role VALUES (1,'Requestor')");
            $r5 = $init->query("CREATE TABLE users (id INT AUTO_INCREMENT PRIMARY KEY, nama VARCHAR(100), email VARCHAR(100), departemen VARCHAR(50), password VARCHAR(50), company_id INT, role_id INT) ENGINE=InnoDB");
            $r6 = $init->query("INSERT INTO users VALUES (1,'andi','sydel.hv@gmail.com','BP','123456',1,1)");
            
            file_put_contents('/tmp/db_debug.txt', ' r1='.($r1?'OK':'FAIL:'.$init->error).' r2='.($r2?'OK':'FAIL').' r3='.($r3?'OK':'FAIL').' r4='.($r4?'OK':'FAIL').' r5='.($r5?'OK':'FAIL').' r6='.($r6?'OK':'FAIL'), FILE_APPEND);
            $init->close();
        }
    }
    file_put_contents('/tmp/db_debug.txt', ' DONE', FILE_APPEND);
    file_put_contents($init_flag, 'done');
}
