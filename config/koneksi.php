<?php

$host     = "localhost";
$username = "root";
$password = "Sv160505";
$database = "mdm_portal";

$koneksi = new mysqli($host, $username, $password, $database);


if ($koneksi->connect_error) {
     error_log("Koneksi gagal: " . $koneksi->connect_error);
    die("Maaf, terjadi masalah pada sistem.");
}

$koneksi->set_charset("utf8mb4");
?>