<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/config/koneksi.php';

/** @var mysqli $koneksi */


if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}

$company_id = $_SESSION['company_id'];
$user_id    = (int)$_SESSION['user_id'];

// Theme
$queryTheme = mysqli_query($koneksi, "SELECT fungsi, warna FROM theme WHERE company_id='$company_id'");
$theme = [];
while($row = mysqli_fetch_assoc($queryTheme)){
    $theme[$row['fungsi']] = $row['warna'];
}

// User data — pakai nama tabel yang benar sesuai database kamu
$queryUser = mysqli_query($koneksi, "
    SELECT u.*, r.nama_role, c.nama_company
    FROM users u
    JOIN role r ON u.role_id = r.id
    JOIN company c ON u.company_id = c.id
    WHERE u.id = $user_id
");

if(!$queryUser){
    die("Query error: " . mysqli_error($koneksi));
}

$user = mysqli_fetch_assoc($queryUser);

if(!$user){
    die("User tidak ditemukan. user_id=" . $user_id);
}

// Handle POST update
$successMsg = '';
$errorMsg   = '';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $nama       = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $email      = mysqli_real_escape_string($koneksi, $_POST['email']);
    $departemen = mysqli_real_escape_string($koneksi, $_POST['departemen']);
    $newPass    = $_POST['new_password'];
    $konfirm    = $_POST['confirm_password'];

    if(!empty($newPass)){
        if($newPass !== $konfirm){
            $errorMsg = 'Password baru dan konfirmasi tidak cocok.';
        } else {
            $newPass = mysqli_real_escape_string($koneksi, $newPass);
            mysqli_query($koneksi, "UPDATE users SET nama='$nama', email='$email', departemen='$departemen', password='$newPass' WHERE id=$user_id");
            $successMsg = 'Profile berhasil diupdate.';
        }
    } else {
        mysqli_query($koneksi, "UPDATE users SET nama='$nama', email='$email', departemen='$departemen' WHERE id=$user_id");
        $successMsg = 'Profile berhasil diupdate.';
    }

    // Refresh data setelah update
    $queryUser = mysqli_query($koneksi, "
        SELECT u.*, r.nama_role, c.nama_company
        FROM users u
        JOIN role r ON u.role_id = r.id
        JOIN company c ON u.company_id = c.id
        WHERE u.id = $user_id
    ");
    $user = mysqli_fetch_assoc($queryUser);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
    
    <link rel="stylesheet" href="/atri/css/global.css">
    <link rel="stylesheet" href="/atri/css/navbar.css">
    <link rel="stylesheet" href="/atri/css/profile.css">
    <style>
    :root {
        --navbar: <?= $theme['navbar']; ?>;
        --btn-primary: <?= $theme['btn_primary']; ?>;
        --table-header: <?= $theme['table_header']; ?>;
        --status-pending: <?= $theme['status_pending']; ?>;
        --status-active: <?= $theme['status_active']; ?>;
    }
    </style>

<script>
    tailwind.config = {
        corePlugins: {
            preflight: false
        }
    }
</script>
<script src="https://cdn.tailwindcss.com"></script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/include/navbar.php'; ?>

<div class="container">
    <div class="profile-card">

        <div class="profile-header">
            <div class="profile-avatar">
                <?= strtoupper(substr($user['nama'], 0, 1)) ?>
            </div>
            <div class="profile-meta">
                <h3><?= htmlspecialchars($user['nama']) ?></h3>
                <span><?= htmlspecialchars($user['email']) ?></span><br>
                <span class="profile-badge"><?= htmlspecialchars($user['nama_role']) ?></span>
                <span class="profile-badge"><?= htmlspecialchars($user['nama_company']) ?></span>
            </div>
        </div>

        <?php if($successMsg): ?>
            <div class="alert-success"><?= $successMsg ?></div>
        <?php endif; ?>
        <?php if($errorMsg): ?>
            <div class="alert-error"><?= $errorMsg ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
            </div>
            <div class="form-group">
                <label>Departemen</label>
                <input type="text" name="departemen" value="<?= htmlspecialchars($user['departemen'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Role</label>
                <input type="text" value="<?= htmlspecialchars($user['nama_role']) ?>" readonly>
            </div>
            <div class="form-group">
                <label>Company</label>
                <input type="text" value="<?= htmlspecialchars($user['nama_company']) ?>" readonly>
            </div>

            <div class="form-divider">Ganti Password (kosongkan jika tidak ingin mengubah)</div>

            <div class="form-group">
                <label>Password Baru</label>
                <input type="password" name="new_password" placeholder="Masukkan password baru">
            </div>
            <div class="form-group">
                <label>Konfirmasi Password</label>
                <input type="password" name="confirm_password" placeholder="Ulangi password baru">
            </div>

            <button type="submit" class="btn-save">Simpan Perubahan</button>
        </form>

    </div>
</div>

</body>
</html>