<?php

$host     = "kxs31cnzmktkqfav6tshrqqb";
$username = "mdata";
$password = "Sv160505";
$database = "mdm_portal";

$koneksi = new mysqli($host, $username, $password, $database);

if ($koneksi->connect_error) {
     error_log("Koneksi gagal: " . $koneksi->connect_error);
    die("Maaf, terjadi masalah pada sistem.");
}

$koneksi->set_charset("utf8mb4");

// Auto-init: run once per container
$init_flag = '/tmp/db_initialized.flag';
if (!file_exists($init_flag)) {
    $check = $koneksi->query("SELECT COUNT(*) as cnt FROM users");
    $has_data = false;
    if ($check) { $row = $check->fetch_assoc(); $has_data = ($row['cnt'] > 0); }
    
    if (!$has_data) {
        // Use SEPARATE connection for init to avoid interfering with main connection
        $init = new mysqli($host, $username, $password, $database);
        
        // Drop tables
        $init->query("SET FOREIGN_KEY_CHECKS = 0");
        $tables = $init->query("SHOW TABLES");
        if ($tables) {
            while ($row = $tables->fetch_array()) {
                $init->query("DROP TABLE IF EXISTS `{$row[0]}`");
            }
        }
        $init->query("SET FOREIGN_KEY_CHECKS = 1");
        
        // Create tables and insert data - one query at a time (no multi_query)
        $init->query("CREATE TABLE IF NOT EXISTS `company` (
          `id` int NOT NULL AUTO_INCREMENT,
          `nama_company` varchar(100) NOT NULL,
          `company_code` varchar(10) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1");
        
        $init->query("INSERT INTO `company` VALUES (1,'Alamtri','ATRI'),(2,'Adaro','ADRO')");
        
        $init->query("CREATE TABLE IF NOT EXISTS `role` (
          `id` int NOT NULL AUTO_INCREMENT,
          `nama_role` varchar(50) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1");
        
        $init->query("INSERT INTO `role` VALUES (1,'Requestor'),(2,'MDM Business Unit'),(3,'Direct Manager'),(4,'BPO Local'),(5,'MDM Global')");
        
        $init->query("CREATE TABLE IF NOT EXISTS `users` (
          `id` int NOT NULL AUTO_INCREMENT,
          `nama` varchar(100) NOT NULL,
          `email` varchar(100) NOT NULL,
          `departemen` varchar(50) DEFAULT NULL,
          `password` varchar(50) NOT NULL,
          `company_id` int NOT NULL,
          `role_id` int NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1");
        
        $init->query("INSERT INTO `users` VALUES 
          (1,'andi','sydel.hv@gmail.com','BP','123456',1,1),
          (2,'auvia','sydel016@gmail.com','a','123',1,2)");
        
        $init->close();
    }
    file_put_contents($init_flag, 'done');
}
