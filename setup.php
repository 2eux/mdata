<?php
$host = "kxs31cnzmktkqfav6tshrqqb";
$user = "mdata";
$pass = "Sv160505";
$db   = "mdm_portal";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Fail: " . $conn->connect_error);

echo "Users in DB:<br>\n";
$r = $conn->query("SELECT id, nama, email, password, LENGTH(password) as pwlen, HEX(password) as pwhex FROM users");
while ($row = $r->fetch_assoc()) {
    printf("%d | %s | %s | pw='%s' (len=%d, hex=%s)<br>\n",
        $row['id'], $row['nama'], $row['email'],
        $row['password'], $row['pwlen'], $row['pwhex']);
}

echo "<br>Test direct query:<br>\n";
$t = $conn->query("SELECT id, nama FROM users WHERE email='sydel.hv@gmail.com' AND password='123456'");
if ($t && $t->num_rows > 0) {
    echo "✅ Login query works! User: " . $t->fetch_assoc()['nama'];
} else {
    echo "❌ Login query FAILED";
    echo "<br>Error: " . $conn->error;
}
