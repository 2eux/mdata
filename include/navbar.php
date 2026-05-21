<?php
// ─── Session & Koneksi ───────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($koneksi)) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/atri/config/koneksi.php';
    /** @var mysqli $koneksi */
}

// ─── Data User ───────────────────────────────────────────────────────────────
$user_id = $_SESSION['user_id'];

$queryNav = mysqli_query($koneksi, "
    SELECT users.nama, role.nama_role, company.nama_company
    FROM users
    JOIN role    ON users.role_id    = role.id
    JOIN company ON users.company_id = company.id
    WHERE users.id = '$user_id'
");

$userNav = mysqli_fetch_assoc($queryNav);
$role    = $userNav['nama_role'] ?? '';

// ─── Helper: link menu berdasarkan role ──────────────────────────────────────
function renderMenuLinks(string $role): void
{
    echo '<a href="home.php">Dashboard</a>';

    if ($role === 'Requestor') {
        echo '<a href="request.php">Requests</a>';
        echo '<a href="request_list.php">History</a>';
    }

    if (in_array($role, ['BPO Local', 'Direct Manager', 'MDM Global'])) {
        echo '<a href="APBPO.php">Review</a>';
    }

    if ($role === 'MDM Business Unit') {
        echo '<a href="approval_List.php">Review</a>';
    }
}
?>

    <link rel="stylesheet" href="/atri/css/global.css">
    <link rel="stylesheet" href="/atri/css/navbar.css">

<!-- ═══════════════════════════════════════════════════════════════════════════
     NAVBAR
═══════════════════════════════════════════════════════════════════════════ -->
<div class="navbar">

    <!-- Logo -->
    <div class="nav-left">
        <span class="logo-text">MDATA</span>
    </div>

    <!-- Hamburger (mobile) -->
    <div class="menu-toggle" onclick="toggleSidebar()">☰</div>

    <!-- Nav kanan -->
    <div class="nav-right" id="mobileMenu">

        <a href="#">File Manager</a>
        <a href="#">EN</a>

        <!-- User menu (desktop dropdown) -->
        <div class="user-menu">

            <div class="user-panel" onclick="toggleUserMenu()">
                <div class="user-text">
                    <div class="user-role">
                        <?= htmlspecialchars($userNav['nama_role']    ?? 'No Role') ?>
                    </div>
                    <div class="user-company">
                        <?= htmlspecialchars($userNav['nama_company'] ?? 'No Company') ?>
                    </div>
                </div>
                <div class="user-color"></div>
                <div class="arrow">▲</div>
            </div>

            <!-- Dropdown desktop -->
            <div class="user-dropdown" id="userDropdown" style="display:none;">
                <?php renderMenuLinks($role); ?>
                <a href="profile.php">Profile</a>
                <a href="/atri/action/logout.php">Log Out</a>
            </div>

        </div><!-- /.user-menu -->

        <!-- Sidebar (mobile) -->
        <div class="mobile-links">
            <?php renderMenuLinks($role); ?>
            <a href="profile.php" class="mobile-user-info">
                <div class="mobile-role">
                    <?= htmlspecialchars($userNav['nama_role']    ?? 'No Role') ?>
                </div>
                <div class="mobile-company">
                    <?= htmlspecialchars($userNav['nama_company'] ?? 'No Company') ?>
                </div>
            </a>
            <div class="mobile-logout">
                <a href="/atri/action/logout.php">Logout</a>
            </div>
        </div>

    </div><!-- /.nav-right -->

</div><!-- /.navbar -->

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- ═══════════════════════════════════════════════════════════════════════════
     SCRIPTS
═══════════════════════════════════════════════════════════════════════════ -->
<script>
function toggleSidebar() {
    document.getElementById('mobileMenu').classList.toggle('show');
    document.getElementById('sidebarOverlay').classList.toggle('show');
}

function toggleUserMenu() {
    const menu  = document.getElementById('userDropdown');
    const arrow = document.querySelector('.arrow');
    const isOpen = menu.style.display === 'block';

    menu.style.display = isOpen ? 'none' : 'block';
    arrow.textContent  = isOpen ? '▲' : '▼';
}

window.addEventListener('click', function (e) {
    if (!e.target.closest('.user-menu')) {
        const menu  = document.getElementById('userDropdown');
        const arrow = document.querySelector('.arrow');
        if (menu) {
            menu.style.display = 'none';
            arrow.textContent  = '▲';
        }
    }
});
</script>