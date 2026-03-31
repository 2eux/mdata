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
$user_id = (int)$_SESSION['user_id'];

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
    $res = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM request_header WHERE requestor_id = $user_id AND status = '$st'");
    $data = mysqli_fetch_assoc($res);
    $summary[$st] = $data['total'];
}

// 3. Query List
$sql = "SELECT
            rh.id,
            rh.request_no,
            rh.request_date,
            rh.status,
            rh.current_step,
            (SELECT COUNT(*) FROM request_detail rd WHERE rd.request_id = rh.id) as total_material
        FROM request_header rh
        WHERE rh.requestor_id = $user_id
        ORDER BY rh.id DESC";

$query = mysqli_query($koneksi, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Home - MDM Portal</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/request_list.css">
    <style>
    :root {
        --navbar: <?php echo $theme['navbar'] ?? '#005f73'; ?>;
        --logo-material: <?php echo $theme['logo_material'] ?? '#0a9396'; ?>;
        --logo-service: <?php echo $theme['logo_service'] ?? '#94d2bd'; ?>;
        --logo-vendor: <?php echo $theme['logo_vendor'] ?? '#e9d8a6'; ?>;
        --btn-primary: <?php echo $theme['btn_primary'] ?? '#0a9396'; ?>;
        --btn-approve: <?php echo $theme['btn_approve'] ?? '#2ecc71'; ?>;
        --btn-reject: <?php echo $theme['btn_reject'] ?? '#e74c3c'; ?>;
        --table-header: <?php echo $theme['table_header'] ?? '#e0f4f4'; ?>;
        --status-pending: <?php echo $theme['status_pending'] ?? '#f39c12'; ?>;
        --status-active: <?php echo $theme['status_active'] ?? '#2ecc71'; ?>;
    }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>My Request List</h2>
        <a href="create_request.php" style="background: var(--btn-primary); color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none;">+ New Request</a>
    </div>

    <!-- Summary Cards -->
    <div class="summary-box">
        <?php foreach ($summary as $label => $count): ?>
            <div class="summary-card">
                <div style="color: #666; font-size: 14px;"><?= htmlspecialchars($label) ?></div>
                <h1 style="margin: 10px 0 0 0; color: #333;"><?= $count ?></h1>
            </div>
        <?php endforeach; ?>
    </div>
<?php 
mysqli_data_seek($query, 0);
echo "TOTAL ROWS: " . mysqli_num_rows($query); 
?>
    <!-- Table -->
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
                            <td><strong><?= htmlspecialchars($row['request_no']) ?></strong></td>
                            <td><?= date('d M Y', strtotime($row['request_date'])) ?></td>
                            <td style="text-align: center;"><?= $row['total_material'] ?></td>
                            <td>
                                <?php $s = $row['status']; ?>
                                <span class="badge status-<?= strtolower($s) ?>"><?= htmlspecialchars($s) ?></span>
                            </td>
                            <td><?= htmlspecialchars($row['current_step'] ?? '-') ?></td>
                            <td>
                                <?php if($row['status'] == "Draft"): ?>
                                    <a href="edit_request.php?id=<?= $row['id'] ?>" class="btn-edit">Edit</a>
                                <?php else: ?>
                                    <a href="view_request.php?id=<?= $row['id'] ?>" class="btn-view">View</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 30px; color: #999;">
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