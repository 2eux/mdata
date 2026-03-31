<?php
if(session_status() == PHP_SESSION_NONE){
    session_start();
}
include 'koneksi.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$company_id = $_SESSION['company_id'];
$user_id = $_SESSION['user_id'];

// 1. Ambil Theme
$queryTheme = mysqli_query($koneksi, "SELECT fungsi, warna FROM theme WHERE company_id='$company_id'");
$theme = [];
while($row = mysqli_fetch_assoc($queryTheme)){
    $theme[$row['fungsi']] = $row['warna'];
}

// 2. Summary Counts
$statuses = ['Draft', 'Pending', 'Approved', 'Rejected'];
$summary = [];
foreach ($statuses as $st) {
    $sql_sum = "SELECT COUNT(*) as total FROM request_header WHERE requestor_id = '$user_id' AND status = '$st'";
    $res = mysqli_query($koneksi, $sql_sum);
    $data = mysqli_fetch_assoc($res);
    $summary[$st] = $data['total'];
}

// 3. Query List Table (PERBAIKAN: Menambahkan JOIN untuk total_material dan eksekusi query)
$sql = "SELECT 
            rh.*, 
            (SELECT COUNT(*) FROM request_detail rd WHERE rd.request_id = rh.id) as total_material 
        FROM request_header rh 
        WHERE rh.requestor_id = '$user_id' 
        ORDER BY rh.id DESC";

$query = mysqli_query($koneksi, $sql); // Baris ini wajib ada agar data bisa diproses di tabel
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Home - MDM Portal</title>
    
    <style>
   
    /* Tambahan agar badge terlihat */
    .badge {
        padding: 5px 10px;
        border-radius: 4px;
        color: white;
        font-size: 12px;
        font-weight: bold;
    }
    .status-pending { background-color: var(--status-pending); color: black; }
    .status-approved { background-color: var(--status-active); }
    .status-rejected { background-color: var(--btn-reject); }
    .status-draft { background-color: #95a5a6; }
    
    table { width: 100%; border-collapse: collapse; background: white; }
    th { background: var(--table-header); padding: 10px; border: 1px solid #ddd; }
    td { padding: 10px; border: 1px solid #ddd; color: black; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>My Request List</h2>
        <a href="create_request.php" style="background: var(--btn-primary); color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none;">+ New Request</a>
    </div>

    <div class="summary-box" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 30px;">
        <?php foreach ($summary as $label => $count): ?>
            <div class="summary-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                <div style="color: #666; font-size: 14px;"><?= $label ?></div>
                <h1 style="margin: 10px 0 0 0; color: #333;"><?= $count ?></h1>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="table-box">
        <table>
            <thead>
                <tr>
                    <th>Request No</th>
                    <th>Date</th>
                    <th>Total Item</th>
                    <th>Status</th>
                    <th>Current Step</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if($query && mysqli_num_rows($query) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <td><strong><?= $row['request_no']; ?></strong></td>
                            <td><?= date('d M Y', strtotime($row['request_date'])); ?></td>
                            <td style="text-align: center;"><?= $row['total_material'] ?? 0; ?></td>
                            <td>
                                <?php 
                                    $s = $row['status'];
                                    $class = "status-" . strtolower($s);
                                ?>
                                <span class="badge <?= $class ?>"><?= $s ?></span>
                            </td>
                            <td><?= $row['current_step'] ?? '-'; ?></td>
                            <td>
                                <?php if($row['status'] == "Draft"): ?>
                                    <a href="edit_request.php?id=<?= $row['id']; ?>" class="btn-edit" style="color: orange;">Edit</a>
                                <?php else: ?>
                                    <a href="view_request.php?id=<?= $row['id']; ?>" class="btn-view" style="color: var(--btn-primary);">View</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 30px; color: #999;">
                            ID Session: <?= $user_id ?> <br>
                            Belum ada data request untuk akun Anda.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>