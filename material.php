<?php
session_start();
include 'koneksi.php';

$company_id = $_SESSION['company_id'];

// Ambil theme
$query = mysqli_query($koneksi, "SELECT fungsi, warna FROM theme WHERE company_id='$company_id'");
$theme = [];
while($row = mysqli_fetch_assoc($query)){
    $theme[$row['fungsi']] = $row['warna'];
}

// Search
$search = "";
if(isset($_GET['search'])){
    $search = $_GET['search'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Material Master</title>
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/module.css">

    <style>
    :root {
        --navbar: <?= $theme['navbar']; ?>;
        --primary: <?= $theme['logo_material']; ?>;
        --button: <?= $theme['logo_material']; ?>;
    }
    </style>
</head>

<body>

<?php include 'navbar.php'; ?>

<div class="container">

    <!-- CARD -->
    <div class="module-card">
        <div class="icon" style="background: var(--primary);">
            <img src="Gambar/material.png">
        </div>
        <div>
            <b>XXXXXXXX</b><br>
            Material Master<br>
            XXXXXXXX
        </div>
    </div>

    <!-- SEARCH -->
    <div class="search-box">
        <h4>Search</h4>
        <form method="GET">
            <div class="search-container">
                <input type="text" name="search" value="<?= $search ?>" placeholder="Search Material">
                <button type="submit" class="btn-search">SEARCH</button>
            </div>
        </form>
    </div>

</div>

</body>
</html>