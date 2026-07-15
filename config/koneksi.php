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
    $check = $koneksi->query("SHOW TABLES LIKE 'users'");
    if ($check && $check->num_rows == 0) {
        $sql = file_get_contents(__DIR__ . '/mdm_portal.sql');
        $sql = preg_replace('/^CREATE DATABASE.*$/m', '', $sql);
        $sql = preg_replace('/^USE .*$/m', '', $sql);
        if ($koneksi->multi_query($sql)) {
            do {} while ($koneksi->next_result());
        }
    }
    file_put_contents($init_flag, 'done');
}
