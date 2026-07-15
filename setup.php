<?php
// One-time setup: initialize DB tables with seed data
$host = "kxs31cnzmktkqfav6tshrqqb";
$user = "mdata";
$pass = "Sv160505";
$db   = "mdm_portal";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("DB connect failed: " . $conn->connect_error);
}

// Check if already done
$check = $conn->query("SELECT COUNT(*) as cnt FROM users");
if ($check) {
    $row = $check->fetch_assoc();
    if ($row['cnt'] > 0) { die("Already initialized - users found: " . $row['cnt']); }
}

// Import the full SQL dump
echo "Importing mdm_portal.sql...<br>\n";
ob_flush(); flush();

$sql = file_get_contents(__DIR__ . '/mdm_portal.sql');
$sql = preg_replace('/^CREATE DATABASE.*$/m', '', $sql);
$sql = preg_replace('/^USE .*$/m', '', $sql);

$conn->query("SET FOREIGN_KEY_CHECKS = 0");
if ($conn->multi_query($sql)) {
    do {} while ($conn->next_result());
    echo "Import done!<br>\n";
} else {
    echo "Import failed: " . $conn->error . "<br>\n";
}

// Verify
$v = $conn->query("SELECT COUNT(*) as cnt FROM users");
if ($v) { $r = $v->fetch_assoc(); echo "Users in DB: " . $r['cnt'] . "<br>\n"; }

echo "<br>Try logging in with: sydel.hv@gmail.com / 123456";
