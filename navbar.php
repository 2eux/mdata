<?php
if(session_status() == PHP_SESSION_NONE){
    session_start();
}
include 'koneksi.php';

$user_id = $_SESSION['user_id'];

$queryNav = mysqli_query($koneksi, "
    SELECT users.nama, role.nama_role, company.nama_company
    FROM users
    JOIN role ON users.role_id = role.id
    JOIN company ON users.company_id = company.id
    WHERE users.id = '$user_id'
");

$userNav = mysqli_fetch_assoc($queryNav);
?>

<link rel="stylesheet" href="css/navbar.css">

<div class="navbar">
    <div class="nav-left">
        <span class="logo-text">MDATA</span>
    </div>

    <div class="nav-right">
        <a href="#">File Manager</a>
        <a href="#">EN</a>

        <div class="user-menu">
            <div class="user-panel" onclick="toggleUserMenu()">
                <div class="user-text">
                    <div class="user-role"><?php echo $userNav['nama_role'] ?? 'No Role'; ?></div>
                    <div class="user-company"><?php echo $userNav['nama_company'] ?? 'No Company'; ?></div>
                </div>
                <div class="user-color"></div>
                <div class="arrow">▲</div>
            </div>

            <div class="user-dropdown" id="userDropdown" style="display: none;">
                <a href="home.php">Dashboard</a>
                <?php
                $role = $userNav['nama_role'];
                if($role == "Requestor"){
                    echo '<a href="request.php">Requests</a>';
                }
                if($role == "Requestor"){
                    echo '<a href="request_list.php">History</a>';
                }
                if(in_array($role, ["MDM BU", "BPO Local", "MDM Business Unit", "Direct Manager", "MDM GLOBAL"])){
                    echo '<a href="approval_List.php">Review</a>';
                }
                ?>
                <a href="profile.php">Profile</a>
                <a href="logout.php">Log Out</a>
            </div> 
        </div>
    </div>
</div>

<script>
function toggleUserMenu() {
    var menu = document.getElementById("userDropdown");
    var arrow = document.querySelector(".arrow");

    if(menu.style.display === "block"){
        menu.style.display = "none";
        arrow.innerHTML = "▲";
    } else {
        menu.style.display = "block";
        arrow.innerHTML = "▼";
    }
}

window.onclick = function(event) {
    if (!event.target.closest('.user-menu')) {
        var menu = document.getElementById("userDropdown");
        if(menu) {
            menu.style.display = "none";
            document.querySelector(".arrow").innerHTML = "▲";
        }
    }
}
</script>