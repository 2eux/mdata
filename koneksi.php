<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "mdm_portal";
$port = 3308;   // <-- WAJIB DITAMBAHKAN

$koneksi = mysqli_connect($host, $user, $pass, $db, $port);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
