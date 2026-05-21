<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/config/koneksi.php';

/** @var mysqli $koneksi */

$company_id = $_SESSION['company_id'];

$query = mysqli_query($koneksi, "SELECT fungsi, warna FROM theme WHERE company_id='$company_id'");
$theme = [];
while($row = mysqli_fetch_assoc($query)){
    $theme[$row['fungsi']] = $row['warna'];
}

$search = "";
if(isset($_GET['search'])){
    $search = $_GET['search'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Service Master</title>
    <link rel="stylesheet" href="/atri/css/global.css">
    <link rel="stylesheet" href="/atri/css/navbar.css">
    <link rel="stylesheet" href="/atri/css/module.css">
    <style>
    :root {
        --navbar: <?= $theme['navbar']; ?>;
        --primary: <?= $theme['logo_service']; ?>;
        --button: <?= $theme['logo_service']; ?>;
    }
    </style>
</head>

<body>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/include/navbar.php'; ?>

<div class="container">
    <div class="module-card">
        <div class="icon" style="background: var(--primary);">
            <img src="/atri/Gambar/service.png">
        </div>
        <div>
            <b>XXXXXXXX</b>
            <br>Service Mas ter<br>
            XXXXXXXX
        </div>
    </div>

    <div class="search-box">
        <h4>Search</h4>
        <form method="GET">
            <div class="search-container">
                <input type="text" name="search" value="<?= $search ?>" placeholder="Search Service">
                <button type="submit" class="btn-search">SEARCH</button>
            </div>
        </form>
    </div>

</div>

</body>
</html>