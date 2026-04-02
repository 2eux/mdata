<?php
session_start();
include 'koneksi.php';

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
    $banner = "banner_alamtri.png";
} else {
    $banner = "banner_adaro.png";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home - MDM Portal</title>
    <link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/navbar.css">
<link rel="stylesheet" href="css/home.css">


    <!-- Dynamic Theme -->
    <style>
    :root {
        --navbar: <?php echo $theme['navbar']; ?>;
        --logo-material: <?php echo $theme['logo_material']; ?>;
        --logo-service: <?php echo $theme['logo_service']; ?>;
        --logo-vendor: <?php echo $theme['logo_vendor']; ?>;
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

<?php include 'navbar.php'; ?>

<div class="banner">
    <img src="Gambar/<?php echo $banner; ?>" alt="Banner">
</div>

<div class="container">
    <h3> Quick Acces</h3>
    <div class="quick-access">

    <div class="card" onclick="location.href='material.php'">
        <div class="icon logo-material">
            <img src="Gambar/material.png" alt="Material">
        </div>
        <div>
            <b>Material Master</b><br>
            Create & Request Material
        </div>
    </div>

    <div class="card" onclick="location.href='service.php'">
        <div class="icon logo-service">
            <img src="Gambar/Service.png" alt="Service">
        </div>
        <div>
            <b>Service Master</b><br>
            Create & Request Service
        </div>
    </div>

    <div class="card" onclick="location.href='vendor.php'">
        <div class="icon logo-vendor">
            <img src="Gambar/vendor.png" alt="Vendor">
        </div>
        <div>
            <b>Vendor Master</b><br>
            Create & Request Vendor
        </div>
    </div>

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