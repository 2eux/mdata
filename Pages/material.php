<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/config/koneksi.php';

/** @var mysqli $koneksi */

if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}

$company_id = $_SESSION['company_id'];

// Theme
$queryTheme = mysqli_query($koneksi, "SELECT fungsi, warna FROM theme WHERE company_id='$company_id'");
$theme = [];
while($row = mysqli_fetch_assoc($queryTheme)){
    $theme[$row['fungsi']] = $row['warna'];
}

// Search
$search    = $_GET['search'] ?? '';
$queryData = false;

if(!empty($search)){
    $search_safe = mysqli_real_escape_string($koneksi, $search);
    $queryData = mysqli_query($koneksi, "
        SELECT * FROM material
        WHERE material_number LIKE '%$search_safe%'
           OR description     LIKE '%$search_safe%'
    ");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Material Master</title>
    <link rel="stylesheet" href="/atri/css/global.css">
    <link rel="stylesheet" href="/atri/css/navbar.css">
    <link rel="stylesheet" href="/atri/css/module.css">
    <style>
    :root {
        --navbar: <?= $theme['navbar']; ?>;
        --primary: <?= $theme['logo_material']; ?>;
        --button: <?= $theme['logo_material']; ?>;
    }
    </style>
</head>
<body>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/include/navbar.php'; ?>

<div class="container">

    <!-- Header -->
    <div class="module-card">
        <div class="icon" style="background: var(--primary);">
            <img src="/atri/Gambar/material.png">
        </div>
        <div>
            <b>Material Module</b><br>
            Material Master<br>
            Manage your materials
        </div>
    </div>

    <!-- Search -->
    <div class="search-box">
        <h4><?= !empty($search) ? "Search Result: '" . htmlspecialchars($search) . "'" : "Search Material" ?></h4>
        <form method="GET">
            <div class="search-container">
                <input type="text" name="search"
                       value="<?= htmlspecialchars($search) ?>"
                       placeholder="Search by material number or description...">
                <button type="submit" class="btn-search">SEARCH</button>
            </div>
        </form>
    </div>

    <!-- Grid -->
    <div class="product-grid">
        <?php if($queryData && mysqli_num_rows($queryData) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($queryData)):
                $mnum     = $row['material_number'];
                $img      = !empty($row['image_url']) ? $row['image_url'] : 'https://via.placeholder.com/200';
                $has_img  = !empty($row['image_url']);
            ?>
            <div class="product-card"
                 onclick="window.location.href='detail_material.php?material_number=<?= urlencode($mnum) ?>'">

                <?php if(!$has_img): ?>
                    <div class="no-image-badge">No Image</div>
                <?php endif; ?>

                <div class="product-image">
                    <img src="<?= $img ?>" onerror="this.src='https://via.placeholder.com/200'">
                </div>

                <div class="product-info">
                    <h4 class="product-title"><?= htmlspecialchars($mnum) ?></h4>
                    <p class="product-desc"><?= htmlspecialchars($row['description']) ?></p>
                    <div class="product-meta">
                        <span class="badge"><?= htmlspecialchars($row['material_group']) ?></span>
                        <span class="badge type"><?= htmlspecialchars($row['material_type']) ?></span>
                    </div>
                </div>

            </div>
            <?php endwhile; ?>

        <?php elseif(!empty($search)): ?>
            <div class="empty-state">No material found for "<?= htmlspecialchars($search) ?>"</div>

        <?php else: ?>
            <div class="empty-state">Please enter a keyword to search</div>
        <?php endif; ?>
    </div>

</div>
</body>
</html>