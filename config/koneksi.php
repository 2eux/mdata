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

// Auto-init database tables on first load
$init_flag = '/tmp/db_initialized.flag';
if (!file_exists($init_flag)) {
    // Check if we have actual data
    $has_data = false;
    $check = $koneksi->query("SELECT COUNT(*) as cnt FROM users");
    if ($check) {
        $row = $check->fetch_assoc();
        $has_data = ($row['cnt'] > 0);
    }
    
    if (!$has_data) {
        // Drop all existing tables
        $tables = $koneksi->query("SHOW TABLES");
        if ($tables) {
            $koneksi->query("SET FOREIGN_KEY_CHECKS = 0");
            while ($row = $tables->fetch_array()) {
                $koneksi->query("DROP TABLE IF EXISTS `{$row[0]}`");
            }
            $koneksi->query("SET FOREIGN_KEY_CHECKS = 1");
        }
        
        // Import a minimal clean SQL (just core tables + users)
        $mini_sql = "
CREATE TABLE IF NOT EXISTS `company` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_company` varchar(100) NOT NULL,
  `company_code` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `company` (`id`, `nama_company`, `company_code`) VALUES
(1, 'Alamtri', 'ATRI'),
(2, 'Adaro', 'ADRO');

CREATE TABLE IF NOT EXISTS `role` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_role` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `role` (`id`, `nama_role`) VALUES
(1, 'Requestor'),
(2, 'MDM Business Unit'),
(3, 'Direct Manager'),
(4, 'BPO Local'),
(5, 'MDM Global');

CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `departemen` varchar(50) DEFAULT NULL,
  `password` varchar(50) NOT NULL,
  `company_id` int NOT NULL,
  `role_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `users` (`id`, `nama`, `email`, `departemen`, `password`, `company_id`, `role_id`) VALUES
(1, 'andi', 'sydel.hv@gmail.com', 'BP', '123456', 1, 1),
(2, 'auvia', 'sydel016@gmail.com', 'a', '123', 1, 2),
(3, 'sydel', 'sydelvaniafx2@gmail.com', 'it', '123', 1, 3),
(4, 'pak zulham', 'academicinsight29@gmail.com', 'it & bp', '123', 1, 4),
(5, 'vania', 'global@gmail.com', 'MDM', '123', 1, 5),
(6, 'dini', 'req@adaro.com', 'manajement', '123', 2, 1),
(7, 'sasa', 'mdmbu@adaro.com', NULL, '123', 2, 2),
(8, 'Manager Adaro', 'manager@adaro.com', NULL, '123', 2, 3),
(9, 'BPO Adaro', 'bpo@adaro.com', NULL, '123', 2, 4),
(10, 'MDM Global Adaro', 'global@adaro.com', NULL, '123', 2, 5);
";
        if ($koneksi->multi_query($mini_sql)) {
            do {} while ($koneksi->next_result());
        }
    }
    file_put_contents($init_flag, 'done');
}
