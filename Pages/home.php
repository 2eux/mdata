<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/config/koneksi.php';

/** @var mysqli $koneksi */

if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}

$company_id = $_SESSION['company_id'];
$company = $_SESSION['company'];



// Ambil theme warna dari database
$query = mysqli_query($koneksi, "SELECT fungsi, warna FROM theme WHERE company_id='$company_id'");

$theme = [];
while($row = mysqli_fetch_assoc($query)){
    $theme[$row['fungsi']] = $row['warna'];
}

// Banner berdasarkan company
if($company == "Alamtri"){
    $banners = [
        "alamtri1.png",
        "alamtri2.png",
        "alamtri3.png"
    ];
} else {
    $banners = [
        "adaro1.png",
        "adaro2.png",
        "adaro3.png"
    ];
}
$role = $_SESSION['role'] ?? '';

if($role == 'MDM Business Unit') $role_id = 2;
elseif($role == 'Direct Manager') $role_id = 3;
elseif($role == 'BPO Local') $role_id = 4;
elseif($role == 'MDM Global') $role_id = 5;
else $role_id = 0;

$step = '';
if($role_id == 2) $step = 'MDM Business Unit';
elseif($role_id == 3) $step = 'Direct Manager';
elseif($role_id == 4) $step = 'BPO Local';
elseif($role_id == 5) $step = 'MDM Global';

$pending_count = 0;

if($step != ''){
   $qPending = mysqli_query($koneksi, "
    SELECT COUNT(*) as total
    FROM request_header
    WHERE current_step = '$step'
    AND status = 'PENDING'
");


    if($qPending && mysqli_num_rows($qPending) > 0){
        $pending_count = mysqli_fetch_assoc($qPending)['total'];
    }
}


?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <title>Home - MDM Portal</title>
    
    <link rel="stylesheet" href="/atri/css/global.css">
    <link rel="stylesheet" href="/atri/css/navbar.css">
    <link rel="stylesheet" href="/atri/css/home.css">


    <!-- Dynamic Theme -->
    <style>
    :root {
        --navbar: <?php echo $theme['navbar']; ?>;
        --logo-material: <?php echo $theme['logo_material']; ?>;
        --logo-service: <?php echo $theme['logo_service']; ?>;
        --logo-vendor: <?php echo $theme['logo_vendor']; ?>;
        --alert: <?php echo $theme['alert']; ?>;
        --btn-primary: <?php echo $theme['btn_primary']; ?>;
        --btn-approve: <?php echo $theme['btn_approve']; ?>;
        --btn-reject: <?php echo $theme['btn_reject']; ?>;
        --table-header: <?php echo $theme['table_header']; ?>;
        --status-pending: <?php echo $theme['status_pending']; ?>;
        --status-active: <?php echo $theme['status_active']; ?>;
    }
    </style>
</head>

<script>
function toggleDropdown() {
    var menu = document.getElementById("dropdownMenu");
    if(menu.style.display === "block"){
        menu.style.display = "none";
    } else {
        menu.style.display = "block";
    }
}

function setType(type) {
    document.getElementById("type").value = type;

    // ubah judul
    if(type === "material"){
        document.getElementById("searchTitle").innerText = "Search Material";
        window.location.href = "material.php";
    }
    else if(type === "service"){
        document.getElementById("searchTitle").innerText = "Search Service";
        window.location.href = "service.php";
    }
    else if(type === "vendor"){
        document.getElementById("searchTitle").innerText = "Search Vendor";
        window.location.href = "vendor.php";
    }
}
</script>

<body>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/include/navbar.php'; ?>



    <div class="banner-slider">
        <div class="slides">
            <?php foreach($banners as $banner): ?>
                <img src="/atri/Gambar/<?php echo $banner; ?>" alt="Banner">
            <?php endforeach; ?>
        </div>
    </div>

<div class="container">
    <h3> Quick Acces</h3>
    <div class="quick-access">

    <div class="card" onclick="location.href='material.php'">
        <div class="icon logo-material">
            <img src="/atri/Gambar/material.png" alt="Material">
        </div>
        <div>
            <b>Material Master</b><br>
            Create & Request Material
        </div>
    </div>

    <div class="card" onclick="location.href='service.php'">
        <div class="icon logo-service">
            <img src="/atri/Gambar/Service.png" alt="Service">
        </div>
        <div>
            <b>Service Master</b><br>
            Create & Request Service
        </div>
    </div>

    <div class="card" onclick="location.href='vendor.php'">
        <div class="icon logo-vendor">
            <img src="/atri/Gambar/vendor.png" alt="Vendor">
        </div>
        <div>
            <b>Vendor Master</b><br>
            Create & Request Vendor
        </div>
    </div>
<?php
$link = '';

if($role_id == 2){
    $link = 'approval_list.php';
} elseif($role_id >= 3 && $role_id <= 5){
    $link = 'APBPO.php';
}
?>

<?php if($role_id >= 2 && $role_id <= 5): ?>

<div class="card" onclick="location.href='<?= $link ?>'">
    <div class="alert"></div>
    
    <div>
        <div style="font-size:14px;color:#666;">Quick Pending Request</div>
        <div style="font-size:28px;font-weight:bold;">
            <?= $pending_count ?>
        </div>
    </div>
</div>

<?php endif; ?>
</div>
   <div class="search-box">
    <h4 id="searchTitle">Search Material</h4>

    <form action="search_redirect.php" method="GET">
        <div class="search-container">
            <input type="text" name="keyword" placeholder="Search Material, Service Or Vendor">

            <button type="submit" class="btn-search">SEARCH</button>

            <div class="dropdown">
                <button type="button" class="btn-arrow" onclick="toggleDropdown()">▲</button>

                <div class="dropdown-menu" id="dropdownMenu">
                    <div onclick="setType('material')">Material</div>
                    <div onclick="setType('service')">Service</div>
                    <div onclick="setType('vendor')">Vendor</div>
                </div>
            </div>

            <input type="hidden" name="type" id="type" value="material">

        </div>
    </form>
</div>
</div>

</body>
</html> 